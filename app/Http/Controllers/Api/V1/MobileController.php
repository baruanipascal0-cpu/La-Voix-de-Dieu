<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class MobileController extends Controller
{
    public function bootstrap(): JsonResponse
    {
        return response()->json([
            'data' => [
                'app' => [
                    'name' => 'La Voix de Dieu Tabernacle de Kindu',
                    'api_version' => 'v1',
                    'timezone' => config('app.timezone'),
                ],
                'features' => [
                    'auth' => true,
                    'sermons' => true,
                    'radio' => true,
                    'live' => true,
                    'church' => true,
                    'chat' => true,
                    'groups' => true,
                    'dm' => true,
                    'calls' => true,
                    'media_uploads' => true,
                    'mediaUploads' => true,
                    'notifications' => true,
                    'realtime' => true,
                    'prayer_rooms' => true,
                    'prayerRooms' => true,
                ],
                'endpoints' => [
                    'login' => '/api/v1/auth/login',
                    'register' => '/api/v1/auth/register',
                    'me' => '/api/v1/auth/me',
                    'device_token' => '/api/v1/auth/device-token',
                    'deviceToken' => '/api/v1/auth/device-token',
                    'bootstrap' => '/api/v1/mobile/bootstrap',
                    'realtime' => '/api/v1/realtime/config',
                    'media_uploads' => '/api/v1/media/uploads',
                    'mediaUploads' => '/api/v1/media/uploads',
                    'notifications' => '/api/v1/notifications',
                    'public_chat' => '/api/v1/public/chat',
                    'publicChat' => '/api/v1/public/chat',
                    'groups' => '/api/v1/public/groups',
                    'dm' => '/api/v1/public/dm',
                    'calls' => '/api/v1/public/dm/calls',
                ],
            ],
        ]);
    }
}
