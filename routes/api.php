<?php

use App\Http\Controllers\ProductNutritionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorController;

Route::post('/sensor', [SensorController::class, 'store']);
Route::get('/status', [SensorController::class, 'latest']);
Route::get('/products/{product}/nutrition', [ProductNutritionController::class, 'show']);
