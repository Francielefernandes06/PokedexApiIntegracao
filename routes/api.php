<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\PokeApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });


    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/pokemons', [PokeApiController::class, 'index']);
    Route::get('/pokemons/details/{id}', [PokeApiController::class, 'show']);

    Route::post('/favorites', [FavoriteController::class, 'store']);

    Route::get('/my-favorites', [FavoriteController::class, 'index']);

    Route::delete('/favorites/{id}', [FavoriteController::class, 'destroy']);
});


Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);
