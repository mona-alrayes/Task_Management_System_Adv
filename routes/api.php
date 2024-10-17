<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ErrorLogController;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::middleware(['throttle:30,1', 'security'])->group(function () {
    Route::put('tasks/{id}/restore', [TaskController::class, 'restoreDeleted']);
    Route::delete('tasks/{id}/delete', [TaskController::class, 'forceDeleted']);
    Route::put('tasks/{task}/status', [TaskController::class, 'statusChange']);
    Route::post('tasks/{task}/assign', [TaskController::class, 'assignTask']);
    Route::put('tasks/{task}/reassign', [TaskController::class, 'reassignTask']);
    Route::post('tasks/{task}/attachments', [TaskController::class, 'uploadAttachment']);
    Route::post('tasks/{task}/comments', [CommentController::class, 'store']);
});

Route::apiResource('users', UserController::class);
Route::get('users/deleted', [UserController::class, 'showDeleted'])->name('users.deleted');
Route::put('users/{id}/restore', [UserController::class, 'restoreDeleted'])->name('users.restore');
Route::delete('users/{id}force-delete', [UserController::class, 'forceDeleted'])->name('users.force-delete');


Route::middleware(['security'])->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('login', 'login')->middleware('throttle:login');;
        Route::post('register', 'register');
        Route::post('logout', 'logout');
        Route::post('refresh', 'refresh');
    });
    // You can use a different rate limit for other routes if needed
    Route::get('tasks/deleted', [TaskController::class, 'showDeleted']);
    Route::get('tasks/blockedTasks', [TaskController::class, 'blockedTasks']);
    Route::apiResource('tasks', TaskController::class);
    Route::get('/reports/daily-tasks', [ReportController::class, 'dailyTaskReport']);
    Route::get('/error-logs', [ErrorLogController::class, 'index']);
});


//admin routes 
