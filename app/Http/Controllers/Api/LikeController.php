<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    /**
     * Toggle like status for a post
     */
    public function toggle($id): JsonResponse
    {
        $post = Post::findOrFail($id);
        $currentUser = Auth::user();

        // Check if post belongs to a private profile
        if ($post->user->is_private && $currentUser->id !== $post->user_id && !$currentUser->isFollowing($post->user)) {
            return response()->json([
                'message' => 'This account is private'
            ], 403);
        }

        // Check if post itself is private
        if ($post->is_private && $currentUser->id !== $post->user_id) {
            return response()->json([
                'message' => 'Post not found'
            ], 404);
        }
        
        if ($post->isLikedBy($currentUser)) {
            $post->likes()->where('user_id', $currentUser->id)->delete();
            $action = 'unliked';
        } else {
            $post->likes()->create(['user_id' => $currentUser->id]);
            $action = 'liked';
        }

        return response()->json([
            'message' => "Post {$action} successfully",
            'likes_count' => $post->likes()->count()
        ]);
    }

    /**
     * Get users who liked a post
     */
    public function users($id): JsonResponse
    {
        $post = Post::findOrFail($id);
        $currentUser = Auth::user();

        // Check if post belongs to a private profile
        if ($post->user->is_private && $currentUser->id !== $post->user_id && !$currentUser->isFollowing($post->user)) {
            return response()->json([
                'message' => 'This account is private'
            ], 403);
        }

        // Check if post itself is private
        if ($post->is_private && $currentUser->id !== $post->user_id) {
            return response()->json([
                'message' => 'Post not found'
            ], 404);
        }

        $users = $post->likes()
            ->with('user')
            ->latest()
            ->paginate(20)
            ->through(fn ($like) => $like->user);

        return response()->json($users);
    }
}
