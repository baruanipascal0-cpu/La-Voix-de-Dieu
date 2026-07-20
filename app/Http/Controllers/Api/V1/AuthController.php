<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DeleteDeviceTokenRequest;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Requests\Api\V1\StoreDeviceTokenRequest;
use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    private const ACCESS_TOKEN_DAYS = 7;

    private const ACCESS_TOKEN_ABILITY = 'access-api';

    private const REFRESH_TOKEN_DAYS = 60;

    private const REFRESH_TOKEN_ABILITY = 'refresh';

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'role' => 'member',
            'is_active' => true,
            'last_seen_at' => now(),
        ]);

        if (Role::where('name', 'member')->where('guard_name', 'web')->exists()) {
            $user->assignRole('member');
        }

        return $this->issueTokenResponse($user, 'Compte La Voix de Dieu Tabernacle de Kindu cree avec succes.', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $identifier = $validated['login'] ?? $validated['email'] ?? $validated['phone'] ?? null;

        if (! $identifier) {
            throw ValidationException::withMessages([
                'email' => 'Renseigne ton email ou ton numero de telephone.',
            ]);
        }

        $user = $this->findUserForLogin($identifier);

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Identifiants invalides.',
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Ce compte est desactive.',
            ]);
        }

        if ($user->suspended_at || $user->blocked_at) {
            throw ValidationException::withMessages([
                'email' => 'Ce compte est suspendu ou bloque.',
            ]);
        }

        $user->forceFill(['last_seen_at' => now()])->save();

        return $this->issueTokenResponse($user, 'Connexion reussie.');
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $plainToken = $request->bearerToken()
            ?: $request->input('refresh_token')
            ?: $request->input('refreshToken');

        if (! $plainToken) {
            throw ValidationException::withMessages([
                'refresh_token' => 'Le refresh token est requis.',
            ]);
        }

        $token = PersonalAccessToken::findToken($plainToken);

        if (! $token || ! $token->can(self::REFRESH_TOKEN_ABILITY) || ! $token->tokenable instanceof User) {
            throw ValidationException::withMessages([
                'refresh_token' => 'Refresh token invalide.',
            ]);
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            $token->delete();

            throw ValidationException::withMessages([
                'refresh_token' => 'Refresh token expire.',
            ]);
        }

        $user = $token->tokenable;

        if (! $user->is_active || $user->suspended_at || $user->blocked_at) {
            $token->delete();

            throw ValidationException::withMessages([
                'refresh_token' => 'Ce compte est suspendu, bloque ou desactive.',
            ]);
        }

        $token->delete();

        $user->forceFill(['last_seen_at' => now()])->save();

        return $this->issueTokenResponse($user, 'Token rafraichi.');
    }

    public function deviceToken(StoreDeviceTokenRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $deviceToken = DeviceToken::updateOrCreate(
            ['token_hash' => hash('sha256', $validated['token'])],
            [
                'user_id' => $request->user()->id,
                'token' => $validated['token'],
                'platform' => $validated['platform'] ?? 'unknown',
                'device_id' => $validated['device_id'] ?? null,
                'app_version' => $validated['app_version'] ?? null,
                'last_used_at' => now(),
            ],
        );

        return response()->json([
            'message' => 'Device token enregistre.',
            'device_token' => [
                'id' => $deviceToken->id,
                'platform' => $deviceToken->platform,
                'device_id' => $deviceToken->device_id,
                'app_version' => $deviceToken->app_version,
                'last_used_at' => $deviceToken->last_used_at?->toISOString(),
            ],
        ]);
    }

    public function deleteDeviceToken(DeleteDeviceTokenRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $query = DeviceToken::query()
            ->where('user_id', $request->user()->id);

        if (filled($validated['token'] ?? null)) {
            $query->where('token_hash', hash('sha256', $validated['token']));
        } elseif (filled($validated['device_id'] ?? null)) {
            $query->where('device_id', $validated['device_id']);
        } else {
            abort(422, 'token ou device_id est requis.');
        }

        $deleted = $query->delete();

        return response()->json([
            'message' => 'Device token supprime.',
            'deleted' => $deleted,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $refreshToken = $request->input('refresh_token') ?? $request->input('refreshToken');

        if (filled($refreshToken)) {
            $token = PersonalAccessToken::findToken((string) $refreshToken);

            if ($token && $token->tokenable instanceof User && $token->tokenable->is($request->user()) && $token->can(self::REFRESH_TOKEN_ABILITY)) {
                $token->delete();
            }
        }

        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Deconnexion reussie.',
        ]);
    }

    private function issueTokenResponse(User $user, string $message, int $status = 200): JsonResponse
    {
        $accessToken = $user
            ->createToken('mobile-access', [self::ACCESS_TOKEN_ABILITY], now()->addDays(self::ACCESS_TOKEN_DAYS))
            ->plainTextToken;

        $refreshToken = $user
            ->createToken('mobile-refresh', [self::REFRESH_TOKEN_ABILITY], now()->addDays(self::REFRESH_TOKEN_DAYS))
            ->plainTextToken;

        return response()->json([
            'message' => $message,
            'token_type' => 'Bearer',
            'access_token' => $accessToken,
            'accessToken' => $accessToken,
            'refresh_token' => $refreshToken,
            'refreshToken' => $refreshToken,
            'expires_in' => self::ACCESS_TOKEN_DAYS * 24 * 60 * 60,
            'user' => $this->userPayload($user),
        ], $status);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar_url,
            'avatar_url' => $user->avatar_url,
            'avatarUrl' => $user->avatar_url,
            'photo_url' => $user->avatar_url,
            'photoUrl' => $user->avatar_url,
            'role' => $user->role,
            'roles' => $user->getRoleNames()->values(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
            'is_active' => $user->is_active,
            'isActive' => $user->is_active,
            'email_verified_at' => $user->email_verified_at?->toISOString(),
            'emailVerifiedAt' => $user->email_verified_at?->toISOString(),
            'last_seen_at' => $user->last_seen_at?->toISOString(),
            'lastSeenAt' => $user->last_seen_at?->toISOString(),
            'suspended_at' => $user->suspended_at?->toISOString(),
            'suspendedAt' => $user->suspended_at?->toISOString(),
            'blocked_at' => $user->blocked_at?->toISOString(),
            'blockedAt' => $user->blocked_at?->toISOString(),
            'created_at' => $user->created_at?->toISOString(),
            'createdAt' => $user->created_at?->toISOString(),
        ];
    }

    private function findUserForLogin(string $identifier): ?User
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return User::where('email', strtolower($identifier))->first();
        }

        return User::where('phone', $this->normalizePhone($identifier))->first();
    }

    private function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $phone = preg_replace('/[\s\-().]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return '+243'.substr($phone, 1);
        }

        return $phone;
    }
}
