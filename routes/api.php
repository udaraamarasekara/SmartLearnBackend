<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/notificationCount', [UserController::class, 'notificationCount']);
    Route::middleware(AdminMiddleware::class)->group(function () {
        Route::get('/students',[UserController::class,'getStudents']);

    });
});
Route::post('/login',[UserController::class,'login']);
Route::post('/register',[UserController::class,'register']);

