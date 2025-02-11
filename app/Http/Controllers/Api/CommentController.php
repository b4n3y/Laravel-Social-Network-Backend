<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a newly created comment
     */
    public function store(Request $request, Post $post): JsonResponse
    {
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

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:1', 'max:1000'],
        ]);

        // Sanitize the input
        $validated['content'] = strip_tags($validated['content']);

        $comment = $post->comments()->create([
            'content' => $validated['content'],
            'user_id' => $currentUser->id,
        ]);

        $comment->load('user');

        return response()->json($comment, 201);
    }

    /**
     * Update the specified comment
     */
    public function update(Request $request, Comment $comment): JsonResponse
    {
        try {
            $this->authorize('update', $comment);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'You are not authorized to update this comment'
            ], 403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:1', 'max:1000'],
        ]);

        // Sanitize the input
        $validated['content'] = strip_tags($validated['content']);

        $comment->update($validated);

        return response()->json($comment);
    }

    /**
     * Remove the specified comment
     */
    public function destroy(Comment $comment): JsonResponse
    {
        try {
            $this->authorize('delete', $comment);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'You are not authorized to delete this comment'
            ], 403);
        }

        $comment->delete();

        return response()->json(null, 204);
    }

    /**
     * Get comments for a specific post
     */
    public function index(Post $post): JsonResponse
    {
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

        $comments = $post->comments()
            ->with('user')
            ->latest()
            ->paginate(30);

        return response()->json($comments);
    }
}
