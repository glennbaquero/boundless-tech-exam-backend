<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CustomerLookupController;
use Illuminate\Support\Facades\Route;

Route::post('/customers/lookup', CustomerLookupController::class);
Route::post('/bookings', [BookingController::class, 'store']);
Route::get('/bookings/{booking}', [BookingController::class, 'show']);
