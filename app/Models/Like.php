<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;

class Like extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'user_id',
        'post_id',
    ];

    /**
     * Get the user that owns the like
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post that owns the like
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
} 