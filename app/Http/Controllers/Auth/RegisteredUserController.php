<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Carbon\Carbon;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        // Check if request has files
        $hasFiles = $request->hasFile('avatar');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'gender' => ['required', 'string', 'in:male,female'],
            'birthday' => ['required', 'date', 'before:today', 'after:' . now()->subYears(120)->format('Y-m-d')],
            'avatar' => $hasFiles ? ['sometimes', 'nullable', 'image', 'max:1024'] : ['sometimes', 'nullable', 'string'],
        ]);

        // Ensure avatars directory exists
        if (!Storage::disk('public')->exists('avatars')) {
            Storage::disk('public')->makeDirectory('avatars', 0755, true);
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
            'birthday' => $request->birthday,
        ];

        // Handle avatar upload if provided as file
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $file = $request->file('avatar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('avatars', $filename, 'public');
            
            if ($path) {
                $userData['avatar'] = $path;
            }
        }
        // Handle avatar if provided as string or use default
        elseif ($request->has('avatar') && is_string($request->avatar)) {
            $userData['avatar'] = $request->avatar;
        } else {
            // Set default avatar based on gender
            $userData['avatar'] = 'default-' . $request->gender . '.svg';
        }

        $user = User::create($userData);

        event(new Registered($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful! Please check your email for verification link.',
            'user' => array_merge(
                $user->toArray(),
                [
                    'age' => $user->age,
                    'avatar_url' => $user->avatar_url
                ]
            ),
            'token' => $token
        ], 201);
    }
}
