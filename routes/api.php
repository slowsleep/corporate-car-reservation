<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\BusinessTripController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/trips', [BusinessTripController::class, 'index']);

Route::get('/available-car', [CarController::class, 'available'])->middleware('auth:sanctum');

