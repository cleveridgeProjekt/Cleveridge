<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\FridgeController;
use App\Http\Controllers\FridgeItemController;
use App\Http\Controllers\ProductController;

Route::post('/sensor', [SensorController::class, 'store']);
Route::get('/status', [SensorController::class, 'latest']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('fridges', FridgeController::class);
    Route::post('fridges/{fridge}/items', [FridgeItemController::class, 'store']);
    Route::patch('fridge-items/{id}', [FridgeItemController::class, 'update']);
    Route::delete('fridge-items/{id}', [FridgeItemController::class, 'destroy']);
    Route::apiResource('products', ProductController::class);
});
