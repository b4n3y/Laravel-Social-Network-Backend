<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VerificationViewController extends Controller
{
    /**
     * Show the email verification success page.
     */
    public function success()
    {
        return view('auth.verification.success');
    }

    /**
     * Show the email verification error page.
     */
    public function error()
    {
        return view('auth.verification.error');
    }

    /**
     * Show the email verification expired page.
     */
    public function expired()
    {
        return view('auth.verification.expired');
    }

    /**
     * Show the email verification already verified page.
     */
    public function alreadyVerified()
    {
        return view('auth.verification.already-verified');
    }
} 