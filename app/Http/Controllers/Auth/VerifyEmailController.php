<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EmailVerificationRequest $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified',
                'status' => 'success'
            ]);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        // Create a token for immediate use after verification
        $token = $request->user()->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Email verified successfully',
            'status' => 'success',
            'user' => [
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'email_verified' => true
            ],
            'token' => $token
        ]);
    }

    /**
     * Handle verification from email link
     */
    public function verifyEmail(Request $request, string $id, string $hash)
    {
        try {
            $user = User::findOrFail($id);

            if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
                return redirect()->route('verification.error');
            }

            if ($user->hasVerifiedEmail()) {
                return redirect()->route('verification.already-verified');
            }

            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            // Create a token for immediate use
            $token = $user->createToken('auth_token')->plainTextToken;

            // Store token in session for frontend to retrieve
            session(['auth_token' => $token]);

            return redirect()->route('verification.success');
        } catch (\Exception $e) {
            return redirect()->route('verification.error');
        }
    }
}
