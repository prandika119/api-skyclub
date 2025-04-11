<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\FieldImageController;
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

// Route All can access
Route::get('fields/{field:id}', [FieldController::class, 'show']);
Route::get('fields/{field:id}/schedules/', [FieldController::class, 'getSchedules']);


Route::middleware('auth:sanctum')->group(function (){

    // Route Admin User
    Route::middleware('can:isAdmin')->group(function (){
        Route::post('/fields', [FieldController::class, 'store']);
        Route::put('/fields/{field:id}', [FieldController::class, 'update']);

        // field photos
        Route::post('/fields/{field:id}/photos', [FieldImageController::class, 'store']);
        Route::delete('/fields/photos/{fieldImage:id}', [FieldImageController::class, 'destroy']);

        // field facilities
        Route::post('/fields/{field}/facilities/{facility}', [FacilityController::class, 'addFacilityToField']);
        Route::delete('/fields/{field}/facilities/{facility}', [FacilityController::class, 'removeFacilityFromField']);
    });

    // Route Authenticated User
    Route::get('/users/current', [UserController::class, 'get']);
    Route::patch('/users/current', [UserController::class, 'update']);
    Route::post('/users/logout', [AuthController::class, 'logout'])->name('logout');

    // Route Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);

    // Route Booking
    Route::post('/booking', [BookingController::class, 'store']);
    Route::post('/booking/payment', [BookingController::class, 'payment']);

});

// Route only guest
Route::middleware('can:isGuest')->group(function (){
    Route::post('/users', [AuthController::class, 'register']);
    Route::post('/users/login', [AuthController::class, 'login'])->name('login');
    Route::post('/users/forgot-password', [ResetPasswordController::class, 'forgotPassword']);
    Route::post('/users/reset-password', [ResetPasswordController::class, 'resetPassword']);
});





