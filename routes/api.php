<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

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

Route::prefix('/auth/v1')->group(function (){
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::get('/current', [AuthController::class, 'current'])->middleware('auth:api');
});

Route::prefix('/v1')->group(function (){
        Route::apiResource('/users', UserController::class)->middleware('auth:api');
        Route::post('/users/{id}/restore', [UserController::class, 'restore'])->middleware('auth:api');
        Route::post('/users/{id}/permanently/delete', [UserController::class, 'forceDelete'])->middleware('auth:api');
        Route::get('/get/users/deleted', [UserController::class, 'showDeletedUsers'])->middleware('auth:api');
        Route::delete('/users/{id}/force/delete', [UserController::class, 'forceDelete'])->middleware('auth:api');
});

Route::prefix('/v1')->group(function (){
        Route::apiResource('/projects', ProjectController::class)->middleware('auth:api');
        Route::post('/projects/{id}/restore', [ProjectController::class, 'restore'])->middleware('auth:api');
        Route::get('projects/{projectId}/latest/task', [ProjectController::class, 'getLatestTask'])->middleware('auth:api');
        Route::get('projects/{projectId}/oldest/task', [ProjectController::class, 'getOldestTask'])->middleware('auth:api');
        Route::get('/projects/{projectId}/highest/priority/task', [ProjectController::class, 'showHighestPriorityTask']);
        Route::get('/get/projects/deleted', [ProjectController::class, 'showDeletedProjects'])->middleware('auth:api');
        Route::delete('/projects/{id}/force/delete', [ProjectController::class, 'forceDelete'])->middleware('auth:api');
});

Route::prefix('/v1')->group(function (){
        Route::apiResource('/tasks', TaskController::class)->middleware('auth:api');
        Route::post('/tasks/{id}/restore', [TaskController::class, 'restore'])->middleware('auth:api');
        Route::get('user/tasks/filter', [TaskController::class, 'filterUserTasks'])->middleware('auth:api');
        Route::get('/tasks/highest/priority/{projectId}/{status}', [TaskController::class, 'getHighestPriorityTask'])->middleware('auth:api');
        Route::get('/get/tasks/deleted', [TaskController::class, 'showDeletedTasks'])->middleware('auth:api');
        Route::delete('/tasks/{id}/force/delete', [TaskController::class, 'forceDelete'])->middleware('auth:api');
});















