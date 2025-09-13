<?php

use App\Http\Controllers\Api\AdminApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Web Routes for AJAX calls
|--------------------------------------------------------------------------
|
| These routes are for AJAX calls from the admin dashboard.
| They use session-based authentication.
|
*/

// Admin AJAX Routes - Protected by auth and super_admin middleware
Route::prefix('api/admin')->name('admin.api.')->middleware(['auth', 'super_admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard-stats', [AdminApiController::class, 'getDashboardStats'])->name('dashboard.stats');
    Route::get('/user-growth-chart', [AdminApiController::class, 'getUserGrowthChart'])->name('user.growth.chart');
    Route::get('/course-growth-chart', [AdminApiController::class, 'getCourseGrowthChart'])->name('course.growth.chart');
    Route::get('/revenue-chart', [AdminApiController::class, 'getRevenueChart'])->name('revenue.chart');
    
    // User Management
    Route::get('/users', [AdminApiController::class, 'getUsers'])->name('users.index');
    Route::get('/users/{id}', [AdminApiController::class, 'getUser'])->name('users.show');
    Route::post('/users', [AdminApiController::class, 'createUser'])->name('users.store');
    Route::put('/users/{id}', [AdminApiController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [AdminApiController::class, 'deleteUser'])->name('users.destroy');
    
    // Settings
    Route::get('/settings', [AdminApiController::class, 'getSettings'])->name('settings.index');
    Route::post('/settings', [AdminApiController::class, 'updateSettings'])->name('settings.update');
    Route::get('/settings/{group}', [AdminApiController::class, 'getSettingsByGroup'])->name('settings.group');
    Route::post('/settings/{group}', [AdminApiController::class, 'updateSettingsByGroup'])->name('settings.update.group');
});
