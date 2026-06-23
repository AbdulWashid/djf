<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmployerVerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest:employer')->group(function () {
    Volt::route('employer/register', 'pages.auth.register')
        ->name('employer.register');

    Volt::route('employer/login', 'pages.auth.login')
        ->name('employer.login');

    Volt::route('employer/forgot-password', 'pages.auth.forgot-password')
        ->name('employer.password.request');

    Volt::route('employer/reset-password/{token}', 'pages.auth.reset-password')
        ->name('employer.password.reset');

    Volt::route('employer/verify-email', 'pages.auth.verify-email')
        ->name('employer.verification.notice');

    Route::get('employer/verify-email/{id}/{hash}', EmployerVerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('employer.verification.verify');
});

Route::middleware('guest:candidate')->group(function () {

    Volt::route('candidate/register', 'pages.auth.register')
        ->name('candidate.register');

    Volt::route('candidate/login', 'pages.auth.login')
        ->name('candidate.login');

    Volt::route('candidate/forgot-password', 'pages.auth.forgot-password')
        ->name('candidate.password.request');

    Volt::route('candidate/reset-password/{token}', 'pages.auth.reset-password')
        ->name('candidate.password.reset');

    Volt::route('candidate/verify-email', 'pages.auth.verify-email')
        ->name('candidate.verification.notice');

    Route::get('candidate/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('candidate.verification.verify');
});


Route::middleware('auth:employer')->group(function () {
    Volt::route('employer/confirm-password', 'pages.auth.confirm-password')
        ->name('employer.password.confirm');
});

Route::middleware('auth:candidate')->group(function () {
    Volt::route('candidate/confirm-password', 'pages.auth.confirm-password')
        ->name('candidate.password.confirm');
});