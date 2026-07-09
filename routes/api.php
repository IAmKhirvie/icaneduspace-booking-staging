<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ClassroomController;
use App\Http\Controllers\Api\ServicePackageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::get('/classrooms',                       [ClassroomController::class, 'index']);
Route::get('/classrooms/{classroom}',           [ClassroomController::class, 'show']);

Route::get('/service-packages',                 [ServicePackageController::class, 'index']);
Route::get('/service-packages/{servicePackage}', [ServicePackageController::class, 'show']);

// Public booking submission (rate-limited).
Route::post('/bookings', [BookingController::class, 'store'])->middleware('throttle:6,1');

/*
|--------------------------------------------------------------------------
| Authenticated (Sanctum bearer tokens)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',      [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/bookings',                   [BookingController::class, 'index']);
    Route::get('/bookings/{booking}',         [BookingController::class, 'show']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);

    // Staff / admin only (enforced by BookingPolicy).
    Route::post('/bookings/{booking}/approve', [BookingController::class, 'approve']);
    Route::post('/bookings/{booking}/reject',  [BookingController::class, 'reject']);
});
