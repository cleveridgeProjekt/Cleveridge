<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

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

use Illuminate\Support\Facades\DB;

Route::get('/debug-db', function () {
    return [
        'connection'  => DB::connection()->getConfig('driver'),
        'host'        => DB::connection()->getConfig('host'),
        'database'    => DB::connection()->getDatabaseName(),
        'tables'      => DB::select("
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = 'public'
            ORDER BY table_name
        "),
        'user_count'  => DB::table('users')->count(),
        'sample_user' => DB::table('users')->first(),
    ];
});
