<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TaskAssignmentController;
use App\Http\Controllers\Api\CommentController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class,'logout']);

    Route::apiResource('tasks', TaskController::class);

    Route::post('tasks/{task}/assign',   [TaskAssignmentController::class, 'assign'])
        ->middleware('can:assign,task');
    Route::post('tasks/{task}/unassign', [TaskAssignmentController::class, 'unassign'])
        ->middleware('can:unassign,task');

    Route::get('tasks/{task}/comments',  [CommentController::class, 'index'])
        ->middleware('can:view,task');
    Route::post('tasks/{task}/comments', [CommentController::class, 'store'])
        ->middleware('can:create,App\Models\Comment,task');

    Route::put('comments/{comment}',     [CommentController::class, 'update'])
        ->middleware('can:update,comment');
    Route::delete('comments/{comment}',  [CommentController::class, 'destroy'])
        ->middleware('can:delete,comment');

    Route::get('me/assigned-tasks', [TaskController::class,'assignedToMe']);
});
