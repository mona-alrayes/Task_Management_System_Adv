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

Route::middleware(['security'])->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('login', 'login')->middleware('throttle:login');   // allowed 5 attempets to protect from bruce-force attack
        Route::post('register', 'register');
        Route::post('logout', 'logout');
        Route::post('refresh', 'refresh');
    });
});

//admin routes 
Route::middleware(['throttle:60,1', 'security', 'auth:api', 'role:admin'])->group(function () {
    //user routes
    Route::get('users/deleted', [UserController::class, 'showDeleted'])->name('users.deleted');
    Route::put('users/{id}/restore', [UserController::class, 'restoreDeleted'])->name('users.restore');
    Route::delete('users/{id}force-delete', [UserController::class, 'forceDeleted'])->name('users.force-delete');
    Route::apiResource('users', UserController::class);
    
    //task routes
    Route::get('tasks/deleted', [UserController::class, 'showDeleted']);
    Route::put('tasks/{id}/restore', [TaskController::class, 'restoreDeleted']);
    Route::delete('tasks/{id}/delete', [TaskController::class, 'forceDeleted']);
    Route::apiResource('tasks', TaskController::class);
    Route::get('/error-logs', [ErrorLogController::class, 'index']);
    Route::get('/reports/daily-tasks', [ReportController::class, 'dailyTaskReport']);
});

// manager routes
Route::middleware(['throttle:60,1', 'security', 'auth:api', 'role:manager'])->group(function () {
   //assign and reassign tasks 
   Route::post('tasks/{task}/assign', [TaskController::class, 'assignTask']);
   Route::put('tasks/{task}/reassign', [TaskController::class, 'reassignTask']);
   //manager add attachements to developers
   Route::post('tasks/{task}/attachments', [TaskController::class, 'uploadAttachment']);
});

// developer routes
Route::middleware(['throttle:60,1', 'security', 'auth:api', 'role:developer'])->group(function () {
    Route::put('tasks/{task}/status', [TaskController::class, 'statusChange']);
});

Route::middleware(['throttle:60,1', 'security', 'auth:api'])->group(function () {
Route::apiResource('tasks', TaskController::class)->only('index','show');
Route::get('tasks/blockedTasks', [TaskController::class, 'blockedTasks']);   // all roles can reach this
Route::post('tasks/{task}/comments', [CommentController::class, 'store']);   // all roles can reach this
});