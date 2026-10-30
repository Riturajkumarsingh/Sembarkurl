<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationAcceptController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\ShortUrlController;
use App\Http\Controllers\ShortUrlExportController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

// Root redirect — guests go to login, authenticated users go to dashboard.
Route::redirect('/', '/dashboard');

// Public short-link redirect — no authentication required.
Route::get('/s/{code}', RedirectController::class)->name('redirect');

// Invitation acceptance is public; the token is the only gate.
Route::get('/invitations/{token}', [InvitationAcceptController::class, 'show'])->name('invitations.accept');
Route::post('/invitations/{token}', [InvitationAcceptController::class, 'store'])->name('invitations.accept.store');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/short-urls', [ShortUrlController::class, 'index'])->name('short-urls.index');
    Route::get('/short-urls/create', [ShortUrlController::class, 'create'])->name('short-urls.create');
    Route::post('/short-urls', [ShortUrlController::class, 'store'])->name('short-urls.store');
    Route::get('/short-urls/export', ShortUrlExportController::class)->name('short-urls.export');

    Route::get('/invitations/create', [InvitationController::class, 'create'])->name('invitations.create');
    Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');

    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
});

require __DIR__.'/auth.php';
