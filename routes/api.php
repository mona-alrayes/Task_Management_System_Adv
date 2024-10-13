<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\TaskController;

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

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');

});
Route::get('tasks/deleted', [TaskController::class , 'showDeleted']);
Route::put('tasks/{id}/restore', [TaskController::class , 'restoreDeleted']);
Route::delete('tasks/{id}/delete', [TaskController::class , 'forceDeleted']);
Route::put('tasks/{task}/status', [TaskController::class , 'statusChange']);
Route::get('tasks/blockedTasks', [TaskController::class , 'blockedTasks']);
Route::post('tasks/{task}/assign' , [TaskController::class , 'assignTask']);
Route::put('tasks/{task}/reassign', [TaskController::class , 'reassignTask']);
Route::apiResource('tasks', TaskController::class);

