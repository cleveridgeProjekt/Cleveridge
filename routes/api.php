<?php

use App\Http\Controllers\ProductNutritionController;
use App\Http\Controllers\UserPreferenceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\FridgeController;
use App\Http\Controllers\FridgeItemController;
use App\Http\Controllers\ProductController;

Route::post('/sensor', [SensorController::class, 'store']);
Route::get('/status', [SensorController::class, 'latest']);
Route::apiResource('products', ProductController::class);
Route::get('/user/allergies-test', [UserPreferenceController::class, 'allergyList']);
Route::get('/products/{product}/nutrition', [ProductNutritionController::class, 'show']);


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('fridges', FridgeController::class);
    Route::post('fridges/{fridge}/items', [FridgeItemController::class, 'store']);
    Route::patch('fridge-items/{id}', [FridgeItemController::class, 'update']);
    Route::delete('fridge-items/{id}', [FridgeItemController::class, 'destroy']);

    // Allergies
    Route::get('/user/allergies', [UserPreferenceController::class, 'allergyList']);
    Route::post('/user/allergies', [UserPreferenceController::class, 'addAllergy']);
    Route::delete('/user/allergies/{product}', [UserPreferenceController::class, 'removeAllergy']);
});
