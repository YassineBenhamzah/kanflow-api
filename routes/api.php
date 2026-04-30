<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\BoardMemberController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CommentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:sanctum']]);
// Public
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);
Route::get('/health',         fn() => response()->json(['status' => 'ok']));
Route::get('/login', fn() => response()->json(['message' => 'Unauthenticated'], 401))->name('login');

// Invitations (Public to view)
Route::get('/invitations/{token}', [\App\Http\Controllers\BoardInvitationController::class, 'show']);
// Protected (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',[AuthController::class, 'me']);

    // Boards
    Route::apiResource('boards', BoardController::class);
    Route::post('/boards/{board}/members',         [BoardMemberController::class, 'store']);
    Route::delete('/boards/{board}/members/{user}', [BoardMemberController::class, 'destroy']);

    // Accept Invitation
    Route::post('/invitations/{token}/accept', [\App\Http\Controllers\BoardInvitationController::class, 'accept']);

    // Columns
    Route::apiResource('boards.columns', ColumnController::class)->shallow();
    Route::patch('/columns/reorder', [ColumnController::class, 'reorder']);

    // Tasks
    Route::apiResource('columns.tasks', TaskController::class)->shallow();
    Route::patch('/tasks/{task}/move',    [TaskController::class, 'move']);
    Route::patch('/tasks/{task}/assign',  [TaskController::class, 'assign']);
    Route::patch('/tasks/{task}/archive', [TaskController::class, 'archive']);
    Route::patch('/tasks/reorder',        [TaskController::class, 'reorder']);

    // Comments
    Route::apiResource('tasks.comments', CommentController::class)
         ->only(['index', 'store', 'destroy'])->shallow();

    // Checklists
    Route::apiResource('tasks.checklist', \App\Http\Controllers\ChecklistItemController::class)
         ->only(['store', 'update', 'destroy'])->shallow();
});