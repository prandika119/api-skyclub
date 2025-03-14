<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ResetPasswordController;
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
    Route::get('/users/current', [AuthController::class, 'get']);
});

Route::post('/users', [AuthController::class, 'register']);
Route::post('/users/login', [AuthController::class, 'login'])->name('login');
Route::post('/users/forgot-password', [ResetPasswordController::class, 'forgotPassword']);
Route::post('/users/reset-password', [ResetPasswordController::class, 'resetPassword']);



Route::post('/users', [AuthController::class, 'register']);
Route::post('/users/login', [AuthController::class, 'login'])->name('login');


