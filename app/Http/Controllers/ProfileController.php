<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Update the user's profile.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        // Debug logging
        \Log::info('Profile update request', [
            'has_file' => $request->hasFile('avatar'),
            'files' => $request->allFiles(),
            'all_data' => $request->all(),
            'headers' => $request->headers->all(),
            'content_type' => $request->header('Content-Type'),
            'method' => $request->method(),
        ]);

        // Handle form data
        $userData = [];

        // Handle text fields
        if ($request->has('name')) {
            $userData['name'] = $request->input('name');
        }
        if ($request->has('bio')) {
            $userData['bio'] = $request->input('bio');
        }
        if ($request->has('is_private')) {
            $userData['is_private'] = filter_var($request->input('is_private'), FILTER_VALIDATE_BOOLEAN);
        }

        // Validate the data
        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'avatar' => ['sometimes', 'nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif', 'max:1024'], // 1MB max
            'is_private' => ['sometimes', 'boolean'],
        ]);

        // Handle avatar upload if provided
        if ($request->hasFile('avatar')) {
            \Log::info('Avatar file found', [
                'is_valid' => $request->file('avatar')->isValid(),
                'original_name' => $request->file('avatar')->getClientOriginalName(),
                'mime_type' => $request->file('avatar')->getMimeType(),
                'size' => $request->file('avatar')->getSize(),
            ]);

            try {
                $file = $request->file('avatar');
                if ($file->isValid()) {
                    // Ensure avatars directory exists
                    if (!Storage::disk('public')->exists('avatars')) {
                        Storage::disk('public')->makeDirectory('avatars', 0755, true);
                    }

                    // Delete old avatar if exists and not a default avatar
                    if ($user->avatar && !str_contains($user->avatar, 'default-') && Storage::disk('public')->exists($user->avatar)) {
                        Storage::disk('public')->delete($user->avatar);
                    }

                    $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                    $path = $file->storeAs('avatars', $filename, 'public');
                    
                    \Log::info('File storage attempt', [
                        'filename' => $filename,
                        'path' => $path,
                    ]);

                    if ($path) {
                        $userData['avatar'] = $path;
                    } else {
                        return response()->json([
                            'message' => 'Failed to store avatar file'
                        ], 500);
                    }
                } else {
                    return response()->json([
                        'message' => 'Invalid avatar file'
                    ], 422);
                }
            } catch (\Exception $e) {
                \Log::error('Avatar upload error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'message' => 'Error uploading avatar: ' . $e->getMessage()
                ], 500);
            }
        }

        if (!empty($userData)) {
            $user->update($userData);
            $user->refresh();
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => array_merge(
                $user->toArray(),
                [
                    'age' => $user->age,
                    'avatar_url' => $user->avatar_url,
                    'followers_count' => $user->followers_count,
                    'following_count' => $user->following_count
                ]
            )
        ]);
    }
} 