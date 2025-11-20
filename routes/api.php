<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\CameraController;

Route::post('/sensor', [SensorController::class, 'store']);
Route::get('/status', [SensorController::class, 'latest']);

Route::get('/camera/check', [CameraController::class, 'checkCommand']);
Route::post('/camera/upload', [CameraController::class, 'uploadPhotoAndResults']);
Route::get('/camera/latest', [App\Http\Controllers\CameraController::class, 'getLatestData']);

