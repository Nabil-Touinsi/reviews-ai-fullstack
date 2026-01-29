<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnalyzeController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/analyze', [AnalyzeController::class, 'analyze']);

/*
|--------------------------------------------------------------------------
| Authenticated user routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Reviews (user & admin)
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/{review}', [ReviewController::class, 'show']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);

    // Dashboard user
    Route::get('/dashboard', [DashboardController::class, 'summary']);
});

/*
|--------------------------------------------------------------------------
| Admin-only routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Suppression globale des avis
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

    // Statistiques admin
    Route::get('/admin/stats', function () {
        return response()->json([
            'message' => 'Admin access granted',
            'users_count' => \App\Models\User::count(),
            'reviews_count' => \App\Models\Review::count(),
        ]);
    });
});
