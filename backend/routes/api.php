<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - KasirKu POS System
|--------------------------------------------------------------------------
*/

// Public Authentication & Store info
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/settings/public', [SettingController::class, 'index']);

// Protected POS Routes (Requires Bearer Token)
Route::middleware('auth:sanctum')->group(function () {
    // Auth & User Management
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/users', [AuthController::class, 'indexUsers']);
    Route::post('/users', [AuthController::class, 'storeUser']);
    Route::put('/users/{id}', [AuthController::class, 'updateUser']);
    Route::delete('/users/{id}', [AuthController::class, 'destroyUser']);

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Products & Inventory
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/low-stock', [ProductController::class, 'lowStockAlert']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::post('/products/{id}/adjust-stock', [ProductController::class, 'adjustStock']);

    // Transactions & Checkout
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions/checkout', [TransactionController::class, 'checkout']);
    Route::get('/transactions/{identifier}', [TransactionController::class, 'show']);

    // Dashboard & Analytics
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

    // Store Settings
    Route::get('/settings', [SettingController::class, 'index']);
    Route::post('/settings', [SettingController::class, 'update']);
});
