<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes — no authentication required
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Protected routes — require a valid Sanctum token in the Authorization header
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    /*
     * Projects — standard resource routes (no create/edit — those are HTML form pages)
     * GET    /api/projects              → ProjectController@index
     * POST   /api/projects              → ProjectController@store
     * GET    /api/projects/{project}    → ProjectController@show
     * PUT    /api/projects/{project}    → ProjectController@update
     * DELETE /api/projects/{project}    → ProjectController@destroy
     */
    Route::apiResource('projects', \App\Http\Controllers\Api\ProjectController::class);

    /*
     * Sprints — nested under projects (a sprint always belongs to a project)
     * GET    /api/projects/{project}/sprints              → SprintController@index
     * POST   /api/projects/{project}/sprints              → SprintController@store
     * GET    /api/projects/{project}/sprints/{sprint}     → SprintController@show
     * PUT    /api/projects/{project}/sprints/{sprint}     → SprintController@update
     * DELETE /api/projects/{project}/sprints/{sprint}     → SprintController@destroy
     */
    Route::apiResource('projects.sprints', \App\Http\Controllers\Api\SprintController::class);

    /*
     * Tasks — nested under sprints (a task always belongs to a sprint)
     * GET    /api/sprints/{sprint}/tasks           → TaskController@index
     * POST   /api/sprints/{sprint}/tasks           → TaskController@store
     * GET    /api/sprints/{sprint}/tasks/{task}    → TaskController@show
     * PUT    /api/sprints/{sprint}/tasks/{task}    → TaskController@update
     * DELETE /api/sprints/{sprint}/tasks/{task}    → TaskController@destroy
     */
    Route::apiResource('sprints.tasks', \App\Http\Controllers\Api\TaskController::class);
});
