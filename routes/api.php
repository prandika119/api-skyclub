<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\FieldImageController;
use App\Http\Controllers\ListBookingController;
use App\Http\Controllers\SparingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VoucherController;
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
Route::get('fields/{field:id}', [FieldController::class, 'show']); // get data field by id
Route::get('fields/{field:id}/schedules/', [FieldController::class, 'getSchedules']); // get data field schedule by id
Route::get('/sparings', [SparingController::class, 'index']); // get data list sparing in sparing page


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

        // Route Voucher Admin
        Route::post('/vouchers', [VoucherController::class, 'store']);
        Route::get('/vouchers/{voucher:id}', [VoucherController::class, 'show']);
        Route::put('/vouchers/{vouchers:id}', [VoucherController::class, 'update']);
        Route::delete('/vouchers/{voucher:id}', [VoucherController::class, 'destroy']);

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
    Route::get('/booking', [BookingController::class, 'index']);
    Route::post('/booking', [BookingController::class, 'store']);
    Route::post('/booking/payment', [BookingController::class, 'payment']);

    // Route Voucher
    Route::post('/voucher/{code}', [VoucherController::class, 'checkVoucher']);

    // Route History Booking
    Route::get('/list-booking', [ListBookingController::class, 'index']);
    Route::get('/list-booking/{listBooking:id}', [ListBookingController::class, 'show']);
    Route::post('/list-booking/{listBooking:id}/request-reschedule', [ListBookingController::class, 'requestReschedule']);
    Route::post('/list-booking/{listBooking:id}/request-cancel', [ListBookingController::class, 'requestCancel']);

    // Route Sparing
    Route::post('/sparings', [SparingController::class, 'store']);
    Route::post('/sparings/{sparing:id}/request', [SparingController::class, 'addSparingRequest']);
    Route::post('/sparings/{sparingRequest:id}/accept', [SparingController::class, 'acceptSparingRequest']);
    Route::post('/sparings/{sparingRequest:id}/reject', [SparingController::class, 'rejectSparingRequest']);

});

// Route only guest
Route::middleware('can:isGuest')->group(function (){
    Route::post('/users', [AuthController::class, 'register']);
    Route::post('/users/login', [AuthController::class, 'login'])->name('login');
    Route::post('/users/forgot-password', [ResetPasswordController::class, 'forgotPassword']);
    Route::post('/users/reset-password', [ResetPasswordController::class, 'resetPassword']);
});





