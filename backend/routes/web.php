<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

// ─── Auth Routes ──────────────────────────────────────────────────────────

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── Protected Routes ─────────────────────────────────────────────────────

Route::middleware('auth')->group(function () {

    Route::get('/', fn () => redirect()->route('dashboard'));

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Leads
    Route::prefix('leads')->name('leads.')->group(function () {
        Route::get('/',              [LeadController::class, 'index'])->name('index');
        Route::get('/export',        [LeadController::class, 'export'])->name('export');
        Route::get('/{lead}',        [LeadController::class, 'show'])->name('show');
        Route::post('/{lead}/notes', [LeadController::class, 'updateNotes'])->name('notes');
        Route::post('/{lead}/tag',   [LeadController::class, 'updateTag'])->name('tag');
        Route::post('/{lead}/contacted', [LeadController::class, 'toggleContacted'])->name('contacted');
        Route::delete('/{lead}',     [LeadController::class, 'destroy'])->name('destroy');
    });
});
