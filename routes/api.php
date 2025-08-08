<?php

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


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('fridges', FridgeController::class);
    Route::post('fridges/{fridge}/items', [FridgeItemController::class, 'store']);
    Route::patch('fridge-items/{id}', [FridgeItemController::class, 'update']);
    Route::delete('fridge-items/{id}', [FridgeItemController::class, 'destroy']);

    Route::get('/user/must-have', [UserPreferenceController::class, 'mustHaveList']);
    Route::post('/user/must-have', [UserPreferenceController::class, 'addMustHave']);
    Route::delete('/user/must-have/{product}', [UserPreferenceController::class, 'removeMustHave']);
    Route::get('/user/allergies', [UserPreferenceController::class, 'allergyList']);
    Route::post('/user/allergies', [UserPreferenceController::class, 'addAllergy']);
    Route::delete('/user/allergies/{product}', [UserPreferenceController::class, 'removeAllergy']);
});
