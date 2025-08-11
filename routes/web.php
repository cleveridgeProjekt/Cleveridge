<?php

use App\Http\Controllers\ShoppingListController;
use App\Http\Controllers\ShoppingListItemController;
use App\Http\Controllers\UserPreferenceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Auth pages (SPA shell rendered at /login and /register)
Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',   [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register',[AuthController::class, 'register']);
Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/api/user', fn () => auth()->user());

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

    // dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
});

// SPA catch-all
Route::get('{any}', fn () => view('app'))->where('any', '.*');


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
