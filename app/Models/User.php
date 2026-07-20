<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'password', 'avatar_url', 'role', 'is_active', 'last_seen_at', 'suspended_at', 'blocked_at', 'moderation_reason'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected string $guard_name = 'web';

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active || $this->suspended_at || $this->blocked_at) {
            return false;
        }

        try {
            if ($this->hasPermissionTo('access admin', 'web')) {
                return true;
            }
        } catch (PermissionDoesNotExist) {
            // Permissions may not be seeded yet during the first deployment.
        }

        return $this->hasAnyRole(['super_admin', 'admin', 'editor', 'moderator', 'media_manager', 'prayer_leader'])
            || in_array($this->role, ['super_admin', 'admin', 'editor', 'moderator'], true);
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function mediaUploads(): HasMany
    {
        return $this->hasMany(MediaUpload::class);
    }

    public function pushNotifications(): HasMany
    {
        return $this->hasMany(PushNotification::class);
    }

    public function socialGroups(): BelongsToMany
    {
        return $this->belongsToMany(SocialGroup::class, 'social_group_members')
            ->withPivot(['role', 'status', 'joined_at', 'last_read_at'])
            ->withTimestamps();
    }

    public function directConversations(): BelongsToMany
    {
        return $this->belongsToMany(DirectConversation::class, 'direct_conversation_user')
            ->withPivot(['last_read_at', 'muted_at'])
            ->withTimestamps();
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'last_seen_at' => 'datetime',
            'suspended_at' => 'datetime',
            'blocked_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}