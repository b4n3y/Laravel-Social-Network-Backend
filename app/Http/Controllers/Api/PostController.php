<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of posts
     */
    public function index(): JsonResponse
    {
        $currentUser = Auth::user();
        
        $posts = Post::with(['user'])
            ->where(function ($query) use ($currentUser) {
                // Posts that are public and from public profiles
                $query->where('is_private', false)
                    ->whereHas('user', function ($q) {
                        $q->where('is_private', false);
                    });

                // OR posts from private profiles that the user follows
                $query->orWhere(function ($q) use ($currentUser) {
                    $q->whereHas('user', function ($u) use ($currentUser) {
                        $u->where('is_private', true)
                            ->whereHas('followers', function ($f) use ($currentUser) {
                                $f->where('follower_id', $currentUser->id)
                                    ->where('status', 'accepted');
                            });
                    });
                });

                // OR user's own posts
                $query->orWhere('user_id', $currentUser->id);
            })
            ->withCount(['comments', 'likes'])
            ->latest()
            ->paginate(10);

        return response()->json($posts);
    }

    /**
     * Store a newly created post
     */
    public function store(Request $request): JsonResponse
    {
        // Debug logging
        \Log::info('Post creation request', [
            'has_file' => $request->hasFile('media'),
            'files' => $request->allFiles(),
            'all_data' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255', 'min:3'],
            'content' => ['nullable', 'string', 'min:1', 'max:10000'],
            'media' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif', 'max:5120'], // 5MB max size
            'is_private' => ['sometimes', 'boolean'],
        ]);

        // Debug logging
        \Log::info('Validated data', [
            'validated' => $validated,
            'has_media' => isset($validated['media']),
        ]);

        // Sanitize the text input
        $validated['title'] = strip_tags($validated['title']);
        if (isset($validated['content'])) {
            $validated['content'] = strip_tags($validated['content']);
        }

        // Handle media upload if present
        if ($request->hasFile('media')) {
            \Log::info('Media file found', [
                'is_valid' => $request->file('media')->isValid(),
                'original_name' => $request->file('media')->getClientOriginalName(),
                'mime_type' => $request->file('media')->getMimeType(),
                'size' => $request->file('media')->getSize(),
            ]);

            // Ensure posts directory exists
            if (!Storage::disk('public')->exists('posts')) {
                Storage::disk('public')->makeDirectory('posts', 0755, true);
            }

            try {
                $file = $request->file('media');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('posts', $filename, 'public');
                
                \Log::info('File storage attempt', [
                    'filename' => $filename,
                    'path' => $path,
                ]);

                if ($path) {
                    $validated['media_type'] = 'image';
                    $validated['media_url'] = $path;
                } else {
                    return response()->json([
                        'message' => 'Failed to upload media file'
                    ], 500);
                }
            } catch (\Exception $e) {
                \Log::error('File upload error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json([
                    'message' => 'Error uploading media file: ' . $e->getMessage()
                ], 500);
            }
        }

        $post = Auth::user()->posts()->create($validated);
        $post->load('user');
        $post->loadCount(['comments', 'likes']);

        // Add the full media URL to the response
        $response = $post->toArray();
        if ($post->media_url) {
            $response['media_full_url'] = url('storage/' . $post->media_url);
        }

        return response()->json($response, 201);
    }

    /**
     * Display the specified post
     */
    public function show($id): JsonResponse
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

        $post->load(['user'])
            ->loadCount(['comments', 'likes']);

        // Load paginated comments separately
        $comments = $post->comments()
            ->with('user')
            ->latest()
            ->paginate(30);

        // Merge the paginated comments with the post data
        $response = $post->toArray();
        $response['comments'] = $comments;

        return response()->json($response);
    }

    /**
     * Update the specified post
     */
    public function update(Request $request, $id): JsonResponse
    {
        $post = Post::findOrFail($id);

        // Check if post is private and user is not the owner
        if ($post->is_private && (!Auth::check() || Auth::id() !== $post->user_id)) {
            return response()->json([
                'message' => 'Post not found'
            ], 404);
        }

        $this->authorize('update', $post);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255', 'min:3'],
            'content' => ['nullable', 'string', 'min:1', 'max:10000'],
            'media' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif', 'max:5120'], // 5MB max size
            'is_private' => ['sometimes', 'boolean'],
        ]);

        // Sanitize the text input
        $validated['title'] = strip_tags($validated['title']);
        if (isset($validated['content'])) {
            $validated['content'] = strip_tags($validated['content']);
        }

        // Handle media upload if present
        if ($request->hasFile('media') && $request->file('media')->isValid()) {
            // Ensure posts directory exists
            if (!Storage::disk('public')->exists('posts')) {
                Storage::disk('public')->makeDirectory('posts', 0755, true);
            }

            try {
                // Delete old media if exists
                if ($post->media_url) {
                    Storage::disk('public')->delete($post->media_url);
                }

                $file = $request->file('media');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('posts', $filename, 'public');
                
                if ($path) {
                    $validated['media_type'] = 'image';
                    $validated['media_url'] = $path;
                } else {
                    return response()->json([
                        'message' => 'Failed to upload media file'
                    ], 500);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Error uploading media file'
                ], 500);
            }
        }

        $post->update($validated);
        $post->load('user');
        $post->loadCount(['comments', 'likes']);

        // Add the full media URL to the response
        $response = $post->toArray();
        if ($post->media_url) {
            $response['media_full_url'] = url('storage/' . $post->media_url);
        }

        return response()->json($response);
    }

    /**
     * Remove the specified post
     */
    public function destroy($id): JsonResponse
    {
        $post = Post::findOrFail($id);

        // Check if post is private and user is not the owner
        if ($post->is_private && (!Auth::check() || Auth::id() !== $post->user_id)) {
            return response()->json([
                'message' => 'Post not found'
            ], 404);
        }

        $this->authorize('delete', $post);

        // Delete media if exists
        if ($post->media_url) {
            Storage::disk('public')->delete($post->media_url);
        }

        $post->delete();

        return response()->json(null, 204);
    }

    /**
     * Get posts for the authenticated user
     */
    public function myPosts(): JsonResponse
    {
        $posts = Auth::user()->posts()
            ->with(['comments.user'])
            ->withCount(['comments', 'likes'])
            ->latest()
            ->paginate(10);

        return response()->json($posts);
    }

    /**
     * Get posts by user ID
     */
    public function userPosts($userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $currentUser = Auth::user();

        // If profile is private and current user is not following
        if ($user->is_private && $currentUser->id !== $user->id && !$currentUser->isFollowing($user)) {
            return response()->json([
                'message' => 'This account is private'
            ], 403);
        }

        $posts = Post::where('user_id', $userId)
            ->when($currentUser->id !== (int)$userId, function ($query) {
                return $query->where('is_private', false);
            })
            ->with(['user'])
            ->withCount(['comments', 'likes'])
            ->latest()
            ->paginate(10);

        return response()->json($posts);
    }

    /**
     * Toggle post privacy
     */
    public function togglePrivacy($id): JsonResponse
    {
        $post = Post::findOrFail($id);

        // Check if post is private and user is not the owner
        if ($post->is_private && (!Auth::check() || Auth::id() !== $post->user_id)) {
            return response()->json([
                'message' => 'Post not found'
            ], 404);
        }

        $this->authorize('update', $post);

        $post->is_private = !$post->is_private;
        $post->save();

        return response()->json([
            'message' => 'Post privacy updated successfully',
            'is_private' => $post->is_private
        ]);
    }
}
