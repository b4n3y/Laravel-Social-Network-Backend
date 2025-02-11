<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\CustomVerifyEmail;
use App\Traits\HasUuid;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'avatar',
        'birthday',
        'bio',
        'is_private',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthday' => 'date',
            'is_private' => 'boolean',
        ];
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }

    /**
     * Get the user's avatar URL.
     *
     * @return string
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return url('storage/' . $this->avatar);
        }

        return url('avatars/default-' . $this->gender . '.svg');
    }

    /**
     * Get the user's age.
     *
     * @return int
     */
    public function getAgeAttribute()
    {
        return $this->birthday->age;
    }

    /**
     * Get all posts for the user
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get all comments for the user
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get all likes for the user
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Get users that this user is following
     */
    public function following(): HasMany
    {
        return $this->hasMany(Follower::class, 'follower_id');
    }

    /**
     * Get users that are following this user
     */
    public function followers(): HasMany
    {
        return $this->hasMany(Follower::class, 'following_id');
    }

    /**
     * Check if user is following another user
     */
    public function isFollowing(User $user): bool
    {
        return $this->following()
            ->where('following_id', $user->id)
            ->where('status', 'accepted')
            ->exists();
    }

    /**
     * Check if user has a pending follow request to another user
     */
    public function hasPendingFollowRequest(User $user): bool
    {
        return $this->following()
            ->where('following_id', $user->id)
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Get number of followers
     */
    public function getFollowersCountAttribute(): int
    {
        return $this->followers()->where('status', 'accepted')->count();
    }

    /**
     * Get number of users being followed
     */
    public function getFollowingCountAttribute(): int
    {
        return $this->following()->where('status', 'accepted')->count();
    }

    /**
     * Get pending follow requests for this user
     */
    public function getPendingFollowRequestsAttribute()
    {
        return $this->followers()
            ->where('status', 'pending')
            ->with('follower')
            ->get();
    }
}
