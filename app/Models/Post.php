<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Traits\HasUuid;

class Post extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'title',
        'content',
        'media_type',
        'media_url',
        'user_id',
        'is_private',
    ];

    protected $with = ['user'];

    protected $withCount = ['comments', 'likes'];

    protected $appends = ['media_full_url'];

    /**
     * Get the user that owns the post
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the comments for the post
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the likes for the post
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Check if post is liked by specific user
     */
    public function isLikedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the full URL for media
     */
    public function getMediaFullUrlAttribute(): ?string
    {
        if ($this->media_url) {
            return url('storage/' . $this->media_url);
        }
        return null;
    }
} 