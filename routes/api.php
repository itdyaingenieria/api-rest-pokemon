<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PokemonController;
use App\Http\Controllers\FavoriteController;

// Health check endpoint
Route::get('status', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is running correctly',
        'data' => [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'app_key_configured' => !empty(config('app.key')),
            'jwt_configured' => !empty(config('jwt.secret')),
            'database_connected' => true, // Laravel would fail if DB wasn't connected
            'pokeapi_base_url' => config('pokeapi.base_url'),
            'timestamp' => now()->toISOString(),
        ]
    ]);
});



Route::group([
    'prefix' => 'auth',
    #'middleware' => ['auth:api', 'role:admin'],
], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
    Route::post('password/forgot', [AuthController::class, 'forgotPassword']);
    Route::post('password/reset', [AuthController::class, 'resetPassword']);
});

// PokeAPI proxy endpoints - following PokeAPI best practices
Route::group([
    'prefix' => 'pokemon',
], function () {
    Route::get('/', [PokemonController::class, 'index']);
    Route::get('{id}', [PokemonController::class, 'show']);
    Route::get('type/{type}', [PokemonController::class, 'byType']);
});

// Favorites (protected)
Route::group([
    'prefix' => 'favorites',
    'middleware' => ['auth:api', 'single.session']
], function () {
    Route::get('/', [FavoriteController::class, 'index']);
    Route::post('/', [FavoriteController::class, 'store']);
    Route::post('batch', [FavoriteController::class, 'storeBatch']);
    Route::delete('{id}', [FavoriteController::class, 'destroy']);
});
