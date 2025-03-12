<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\TutorMiddleware;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/notificationCount', [UserController::class, 'notificationCount']);
    Route::middleware(AdminMiddleware::class)->group(function () {
        Route::get('/students',[UserController::class,'getStudents']);
        Route::delete('/member/{id}',[UserController::class,'deleteMember']);
        Route::put('/member/{id}',[UserController::class,'approveMember']);
        Route::get('/tutors',[UserController::class,'getTutors']);
        Route::post('/registerTutor',[UserController::class,'registerTutor']);


    });
    Route::get('/papers',[UserController::class,'getPapers']);
    Route::middleware(TutorMiddleware::class)->group(function () {
        Route::post('/paper',[UserController::class,'newPaper']);
        Route::post('/tutor',[UserController::class,'updateProfile']);
    });
    Route::get('/paper/{id}',[UserController::class,'downloadPaper']);
    Route::post('/logout',[UserController::class,'logout']);

});
Route::post('/login',[UserController::class,'login']);
Route::post('/register',[UserController::class,'register']);

