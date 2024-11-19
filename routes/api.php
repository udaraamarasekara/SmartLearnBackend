<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/notificationCount', [UserController::class, 'notificationCount']);
});
Route::post('/login',[UserController::class,'login']);
Route::post('/register',[UserController::class,'register']);
Route::get('/getStudentPapers',[UserController::class,'getStudentPapers']);

