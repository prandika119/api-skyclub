<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\UserController;
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

Route::middleware('auth:sanctum')->group(function (){
    Route::get('/users/current', [UserController::class, 'get']);
    Route::patch('/users/current', [UserController::class, 'update']);
});

Route::post('/users', [AuthController::class, 'register']);
Route::post('/users/login', [AuthController::class, 'login'])->name('login');
Route::post('/users/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/users/forgot-password', [ResetPasswordController::class, 'forgotPassword']);
Route::post('/users/reset-password', [ResetPasswordController::class, 'resetPassword']);


