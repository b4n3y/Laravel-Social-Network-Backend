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

        // Log request information for debugging
        Log::info('Profile update request', [
            'has_file' => $request->hasFile('avatar'),
            'is_valid' => $request->hasFile('avatar') ? $request->file('avatar')->isValid() : false,
            'all_data' => $request->all()
        ]);

        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'avatar' => ['sometimes', 'nullable', 'file', 'image', 'max:1024'],
        ]);

        $userData = $request->only(['name', 'bio']);

        // Handle avatar upload if provided
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            
            if ($file->isValid()) {
                // Delete old avatar if exists and not a default avatar
                if ($user->avatar && !str_contains($user->avatar, 'default-') && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                // Ensure avatars directory exists
                if (!Storage::disk('public')->exists('avatars')) {
                    Storage::disk('public')->makeDirectory('avatars', 0755, true);
                }

                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('avatars', $filename, 'public');
                
                if ($path) {
                    $userData['avatar'] = $path;
                    Log::info('Avatar uploaded successfully', ['path' => $path]);
                } else {
                    Log::error('Failed to store avatar');
                }
            } else {
                Log::error('Invalid avatar file');
            }
        }

        $user->update($userData);

        // Refresh user data after update
        $user->refresh();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => array_merge(
                $user->toArray(),
                [
                    'age' => $user->age,
                    'avatar_url' => $user->avatar_url
                ]
            )
        ]);
    }
} 