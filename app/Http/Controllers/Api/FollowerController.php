<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Follower;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FollowerController extends Controller
{
    /**
     * Follow a user
     */
    public function follow($userId): JsonResponse
    {
        $userToFollow = User::findOrFail($userId);
        $currentUser = Auth::user();

        // Can't follow yourself
        if ($currentUser->id === $userToFollow->id) {
            return response()->json([
                'message' => 'You cannot follow yourself'
            ], 400);
        }

        // Check if already following or has pending request
        if ($currentUser->isFollowing($userToFollow)) {
            return response()->json([
                'message' => 'You are already following this user'
            ], 400);
        }

        if ($currentUser->hasPendingFollowRequest($userToFollow)) {
            return response()->json([
                'message' => 'You already have a pending follow request for this user'
            ], 400);
        }

        // Create follow relationship
        $status = $userToFollow->is_private ? 'pending' : 'accepted';
        $currentUser->following()->create([
            'following_id' => $userToFollow->id,
            'status' => $status
        ]);

        return response()->json([
            'message' => $status === 'pending' 
                ? 'Follow request sent successfully' 
                : 'Following user successfully',
            'status' => $status
        ]);
    }

    /**
     * Unfollow a user
     */
    public function unfollow($userId): JsonResponse
    {
        $userToUnfollow = User::findOrFail($userId);
        $currentUser = Auth::user();

        $currentUser->following()
            ->where('following_id', $userToUnfollow->id)
            ->delete();

        return response()->json([
            'message' => 'Unfollowed user successfully'
        ]);
    }

    /**
     * Accept a follow request
     */
    public function acceptRequest($id): JsonResponse
    {
        $currentUser = Auth::user();

        $request = Follower::where('id', $id)
            ->where('following_id', $currentUser->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $request->update(['status' => 'accepted']);

        // Check if we want to follow back and aren't already following
        $followerUser = $request->follower;
        $isFollowingBack = false;
        $followBackStatus = null;

        if (request()->has('follow_back') && request()->boolean('follow_back')) {
            // Check if we're already following this user
            if (!$currentUser->isFollowing($followerUser) && !$currentUser->hasPendingFollowRequest($followerUser)) {
                // Create follow back relationship
                $followBackStatus = $followerUser->is_private ? 'pending' : 'accepted';
                $currentUser->following()->create([
                    'following_id' => $followerUser->id,
                    'status' => $followBackStatus
                ]);
                $isFollowingBack = true;
            }
        }

        return response()->json([
            'message' => 'Follow request accepted successfully',
            'followed_back' => $isFollowingBack,
            'follow_back_status' => $followBackStatus
        ]);
    }

    /**
     * Reject/cancel a follow request
     */
    public function rejectRequest($id): JsonResponse
    {
        $currentUser = Auth::user();

        $request = Follower::where('id', $id)
            ->where('following_id', $currentUser->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $request->delete();

        return response()->json([
            'message' => 'Follow request rejected successfully'
        ]);
    }

    /**
     * Get user's followers
     */
    public function followers($userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $currentUser = Auth::user();

        // If profile is private and current user is not following
        if ($user->is_private && $currentUser->id !== $user->id && !$currentUser->isFollowing($user)) {
            return response()->json([
                'message' => 'This account is private'
            ], 403);
        }

        $followers = $user->followers()
            ->where('status', 'accepted')
            ->with('follower')
            ->latest()
            ->paginate(20)
            ->through(fn ($follow) => $follow->follower);

        return response()->json($followers);
    }

    /**
     * Get users that a user is following
     */
    public function following($userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $currentUser = Auth::user();

        // If profile is private and current user is not following
        if ($user->is_private && $currentUser->id !== $user->id && !$currentUser->isFollowing($user)) {
            return response()->json([
                'message' => 'This account is private'
            ], 403);
        }

        $following = $user->following()
            ->where('status', 'accepted')
            ->with('following')
            ->latest()
            ->paginate(20)
            ->through(fn ($follow) => $follow->following);

        return response()->json($following);
    }

    /**
     * Get pending follow requests for the authenticated user
     */
    public function pendingRequests(): JsonResponse
    {
        $requests = Auth::user()->pending_follow_requests;

        return response()->json($requests);
    }

    /**
     * Check if we can follow back a user
     */
    public function canFollowBack($userId): JsonResponse
    {
        $userToCheck = User::findOrFail($userId);
        $currentUser = Auth::user();

        $canFollowBack = !$currentUser->isFollowing($userToCheck) 
            && !$currentUser->hasPendingFollowRequest($userToCheck)
            && $currentUser->id !== $userToCheck->id;

        return response()->json([
            'can_follow_back' => $canFollowBack,
            'is_private' => $userToCheck->is_private
        ]);
    }
}
