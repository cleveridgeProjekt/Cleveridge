<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/status', function () {
    return view('app');
});


// Dashboard (protected)
Route::middleware('auth')->group(function() {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/user', function () {
        return auth()->user();
    });
});
Route::get('{any}', function () {
    return view('app');
})->where('any', '.*');


Route::get('/debug-db', function () {
    try {
        $tables = DB::select("
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = 'public'
        ");
        return [
            'host'     => DB::connection()->getConfig('host'),
            'database' => DB::connection()->getDatabaseName(),
            'tables'   => $tables,
        ];
    } catch (\Exception $e) {
        Log::error('DB error: ' . $e->getMessage());
        return ['error' => $e->getMessage()];
    }
});
