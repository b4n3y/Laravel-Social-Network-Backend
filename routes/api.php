<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\FollowerController;

// Public routes
Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest')
    ->name('register');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('login');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.store');

// Email verification routes (no auth required)
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('api.verification.verify');

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // User profile routes
    Route::get('/me', function (Request $request) {
        $user = $request->user();
        return array_merge(
            $user->toArray(),
            [
                'age' => $user->age,
                'avatar_url' => $user->avatar_url
            ]
        );
    })->name('profile.show');

    Route::patch('/me', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // Resend verification email
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');

    // Posts - Read operations (higher rate limit)
    Route::middleware(['throttle:60,1'])->group(function () {
        Route::get('posts', [PostController::class, 'index']);
        Route::get('posts/{post}', [PostController::class, 'show']);
        Route::get('posts/{post}/comments', [CommentController::class, 'index']);
        Route::get('users/{userId}/posts', [PostController::class, 'userPosts']);
    });

    // Posts - Write operations (lower rate limit)
    Route::middleware(['throttle:100,1'])->group(function () {
        Route::post('posts', [PostController::class, 'store']);
        Route::put('posts/{id}', [PostController::class, 'update']);
        Route::delete('posts/{id}', [PostController::class, 'destroy']);
        Route::get('my-posts', [PostController::class, 'myPosts']);
        Route::patch('posts/{id}/toggle-privacy', [PostController::class, 'togglePrivacy']);

        // Comments
        Route::post('posts/{post}/comments', [CommentController::class, 'store']);
        Route::put('comments/{comment}', [CommentController::class, 'update']);
        Route::delete('comments/{comment}', [CommentController::class, 'destroy']);

        // Likes
        Route::post('posts/{post}/toggle-like', [LikeController::class, 'toggle']);
        Route::get('posts/{post}/likes', [LikeController::class, 'users']);

        // Followers
        Route::post('users/{id}/follow', [FollowerController::class, 'follow']);
        Route::delete('users/{id}/unfollow', [FollowerController::class, 'unfollow']);
        Route::post('followers/{id}/accept', [FollowerController::class, 'acceptRequest']);
        Route::delete('followers/{id}/reject', [FollowerController::class, 'rejectRequest']);
    });

    // Followers - Read operations (higher rate limit)
    Route::middleware(['throttle:60,1'])->group(function () {
        Route::get('users/{id}/followers', [FollowerController::class, 'followers']);
        Route::get('users/{id}/following', [FollowerController::class, 'following']);
        Route::get('followers/pending', [FollowerController::class, 'pendingRequests']);
    });
});

// Protected routes that require email verification
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Add your protected routes here that require email verification
    Route::get('/protected-route', function () {
        return response()->json(['message' => 'This is a protected route that requires email verification']);
    });
});
