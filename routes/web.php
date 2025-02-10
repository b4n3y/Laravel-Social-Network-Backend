<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\VerificationViewController;

// Verification Status Pages
Route::get('/email-verification/success', [VerificationViewController::class, 'success'])
    ->name('verification.success');

Route::get('/email-verification/error', [VerificationViewController::class, 'error'])
    ->name('verification.error');

Route::get('/email-verification/already-verified', [VerificationViewController::class, 'alreadyVerified'])
    ->name('verification.already-verified');

Route::get('/email-verification/expired', [VerificationViewController::class, 'expired'])
    ->name('verification.expired');

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__.'/auth.php';
