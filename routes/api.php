<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\FieldImageController;
use App\Http\Controllers\ListBookingController;
use App\Http\Controllers\RequestCancelController;
use App\Http\Controllers\RequestRescheduleController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SparingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\WalletController;
use App\Models\RequestCancel;
use App\Models\RequestReschedule;
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
Route::get('/fields', [FieldController::class, 'index']); // get data list field
Route::get('field-facilities', [FacilityController::class, 'index']); // get data list field facilities
Route::get('/fields/{field:id}', [FieldController::class, 'show']); // get data field by id
Route::get('/fields/{field:id}/schedules/', [FieldController::class, 'getSchedules']); // get data field schedule by id
Route::get('/sparings', [SparingController::class, 'index']); // get data list sparing in sparing page
Route::get('/articles', [ArticleController::class, 'index']); // get data list article
Route::get('/articles/{article:id}', [ArticleController::class, 'show']); // get data article by id
Route::get('/reviews', [ReviewController::class, 'index']); // get data review


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
        Route::get('/vouchers', [VoucherController::class, 'index']);
        Route::post('/vouchers', [VoucherController::class, 'store']);
        Route::get('/vouchers/{voucher:id}', [VoucherController::class, 'show']);
        Route::put('/vouchers/{vouchers:id}', [VoucherController::class, 'update']);
        Route::delete('/vouchers/{voucher:id}', [VoucherController::class, 'destroy']);


        // Route Article Admin
        Route::post('/articles', [ArticleController::class, 'store']);
        Route::post('/articles/photos', [ArticleController::class, 'addArticlePhoto']);

        Route::put('/articles/{article:id}', [ArticleController::class, 'update']);
        Route::delete('/articles/{article:id}', [ArticleController::class, 'destroy']);
        // Route::post('articles/{article}')

        // Cancel & Reschedule Request
        Route::get('/booking/request-reschedule', [RequestRescheduleController::class, 'index']);
        Route::get('/booking/request-cancel', [RequestCancelController::class, 'index']);
        Route::post('/booking/{requestReschedule:id}/accept-reschedule', [RequestRescheduleController::class, 'acceptRequest']);
        Route::post('/booking/{requestReschedule:id}/reject-reschedule', [RequestRescheduleController::class, 'rejectRequest']);
        Route::post('/booking/{requestCancel:id}/accept-cancel', [RequestCancelController::class, 'acceptRequest']);
        Route::post('/booking/{requestCancel:id}/reject-cancel', [RequestCancelController::class, 'rejectRequest']);

        // Admin Booking User Offline
        Route::post('/booking/user-offline', [BookingController::class, 'storeOffline']);
        Route::post('/booking/user-offline/select-user', [BookingController::class, 'selectUser']);

        // Setting Company Profiles
        Route::get('/settings', [CompanyProfileController::class, 'getSetting']);
        Route::put('/settings', [CompanyProfileController::class, 'updateSetting']);
        Route::post('/settings/logo', [CompanyProfileController::class, 'updateLogo']);
        Route::post('/settings/banner', [CompanyProfileController::class, 'updateBanner']);
        Route::post('/settings/slider', [CompanyProfileController::class, 'updateSlider']);

    });

    // Route Authenticated User
    Route::get('/users/current', [UserController::class, 'get']);
//    Route::patch('/users/current', [UserController::class, 'update']);
    Route::post('/users/current', [UserController::class, 'update']);
    Route::get('/users/current/notifications', [UserController::class, 'getAllNotifications']);
    Route::post('/users/current/notifications/{id}/read', [UserController::class, 'readNotification']);
    Route::post('/users/logout', [AuthController::class, 'logout'])->name('logout');

    // Route Cart
    Route::get('/cart/{booking:id}', [CartController::class, 'index']);
    Route::post('/cart/{booking:id}', [CartController::class, 'saveAllCart']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);

    // Route Booking
    Route::get('/booking', [BookingController::class, 'index']);
    Route::post('/booking', [BookingController::class, 'store']);
    Route::post('/booking/payment', [BookingController::class, 'payment']);

    // Route Voucher
    Route::post('/voucher/{code}/booking/{booking}', [VoucherController::class, 'checkVoucher']);

    // Route Sparing
    Route::post('/sparings', [SparingController::class, 'store']);
    Route::post('/sparings/{sparing:id}/request', [SparingController::class, 'addSparingRequest']);
    Route::post('/sparings/{sparingRequest:id}/accept', [SparingController::class, 'acceptSparingRequest']);
    Route::post('/sparings/{sparingRequest:id}/reject', [SparingController::class, 'rejectSparingRequest']);

    // Route MyBooking
    Route::get('/my-booking', [ListBookingController::class, 'index']);
    Route::post('/my-booking/{listBooking:id}/request-reschedule', [RequestRescheduleController::class, 'addRequest']);
    Route::post('/my-booking/{listBooking:id}/request-cancel', [RequestCancelController::class, 'addRequest']);

    // Route Review
    Route::post('/reviews', [ReviewController::class, 'store']);

    // Route Notifications
    Route::get('/notifications', [UserController::class, 'getNotifications']);
    Route::post('/notifications/{notification:id}/read', [UserController::class, 'readNotification']);

    // Route Payments
    Route::get('/wallet', [WalletController::class, 'getRecentTransaction']);
    Route::post('/wallet/topup', [WalletController::class, 'topup']);
    Route::post('/wallet/transfer', [WalletController::class, 'transfer']);
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw']);

});

// Route only guest
Route::middleware('can:isGuest')->group(function (){
    Route::post('/users', [AuthController::class, 'register']);
    Route::post('/users/login', [AuthController::class, 'login'])->name('login');
    Route::post('/users/forgot-password', [ResetPasswordController::class, 'forgotPassword']);
    Route::post('/users/reset-password', [ResetPasswordController::class, 'resetPassword']);
});





