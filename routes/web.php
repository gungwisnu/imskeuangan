<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AiController;

Route::get('/', fn() => redirect()->route('login'));

// ── Authentication ─────────────────────────────────────────────────────────
Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');

// ── Protected Routes ───────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard (analytics)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Transactions (CRUD + list page)
    Route::get('/transactions',              [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions',             [TransactionController::class, 'store'])->name('transactions.store');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

    // Categories (dynamic creation)
    Route::get('/categories',  [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');

    // AI Chat
    Route::post('/ai/chat', [AiController::class, 'chat'])->name('ai.chat');
});
