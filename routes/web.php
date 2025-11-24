<?php

use App\Http\Controllers\ExpiryController;
use App\Http\Controllers\FridgeController;
use App\Http\Controllers\FridgeItemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductNutritionController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\ShoppingListController;
use App\Http\Controllers\ShoppingListItemController;
use App\Http\Controllers\UserPreferenceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CameraController; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Facades\Response;

// Auth pages (SPA shell rendered at /login and /register)
Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',   [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register',[AuthController::class, 'register']);
Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');

Route::get('/storage/uploads/{filename}', function ($filename) {
    
    $path = 'uploads/' . $filename;

    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }

    $file = Storage::disk('public')->get($path);
    $type = Storage::disk('public')->mimeType($path);

    return Response::make($file, 200)
        ->header("Content-Type", $type);
});


Route::middleware('auth')->group(function () {
    Route::get('/api/user', fn () => auth()->user());

    Route::post('/camera/trigger', [CameraController::class, 'triggerCommand'])->name('camera.trigger'); 
    
    // products
    Route::get   ('/api/products',            [ProductController::class, 'index']);
    Route::get   ('/api/products/{id}',       [ProductController::class, 'show']);
    Route::post  ('/api/products',            [ProductController::class, 'store']);
    Route::put   ('/api/products/{id}',       [ProductController::class, 'update']);
    Route::delete('/api/products/{id}',       [ProductController::class, 'destroy']);

    // nutrition
    Route::get ('/api/products/{product}/nutrition', [ProductNutritionController::class, 'show']);
    Route::post('/api/products/{product}/nutrition', [ProductNutritionController::class, 'store']);
    Route::put ('/api/products/{product}/nutrition', [ProductNutritionController::class, 'update']);

    // products expiry
    Route::get('/api/expiry', [ExpiryController::class, 'index']);

    // must-have
    Route::get   ('/api/user/must-have',              [UserPreferenceController::class, 'mustHaveList']);
    Route::post  ('/api/user/must-have',              [UserPreferenceController::class, 'addMustHave']);
    Route::put   ('/api/user/must-have/{entry}',      [UserPreferenceController::class, 'updateMustHave']);
    Route::delete('/api/user/must-have/{entry}',      [UserPreferenceController::class, 'removeMustHave']);

    // shopping list
    Route::get   ('/api/shopping-list',               [ShoppingListController::class, 'showCurrent']);
    Route::post  ('/api/shopping-list/items',         [ShoppingListItemController::class, 'store']);
    Route::put   ('/api/shopping-list/items/{item}',  [ShoppingListItemController::class, 'update']);
    Route::delete('/api/shopping-list/items/{item}',  [ShoppingListItemController::class, 'destroy']);

    // allergies
    Route::get   ('/api/user/allergies',           [UserPreferenceController::class, 'allergyList']);
    Route::post  ('/api/user/allergies',             [UserPreferenceController::class, 'saveAllergies']);
    Route::delete('/api/user/allergies/{product}',   [UserPreferenceController::class, 'removeAllergy']);

    // fridges
    Route::get   ('/api/fridges',                [FridgeController::class, 'index']);
    Route::post  ('/api/fridges',                [FridgeController::class, 'store']);
    Route::get   ('/api/fridges/{fridge}',       [FridgeController::class, 'show']);
    Route::put   ('/api/fridges/{fridge}',       [FridgeController::class, 'update']);
    Route::delete('/api/fridges/{fridge}',       [FridgeController::class, 'destroy']);

    // fridge items
    Route::post  ('/api/fridges/{fridge}/items', [FridgeItemController::class, 'store']);
    Route::patch ('/api/fridge-items/{item}',    [FridgeItemController::class, 'update']);
    Route::delete('/api/fridge-items/{item}',    [FridgeItemController::class, 'destroy']);

    // recipes
    Route::post('/api/recipes/suggest', [RecipeController::class, 'suggest']);

    // dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
});


Route::get('/debug-db', function () {
    try {
        $tables = DB::select("
            SELECT table_name
            FROM information_schema.tables
            WHERE table_schema = 'public'
        ");
        return [
            'host' => DB::connection()->getConfig('host'),
            'database' => DB::connection()->getDatabaseName(),
            'tables' => $tables,
        ];
    } catch (\Exception $e) {
        Log::error('DB error: ' . $e->getMessage());
        return ['error' => $e->getMessage()];
    }
});

Route::get('{any}', fn () => view('app'))->where('any', '.*');
