<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\UserController;
use App\Models\Movie;
use App\Models\User;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/sign-in', [LoginController::class, 'store']);

Route::post('/sign-up', [RegisterController::class, 'store']);
//->can('viewAny',[Movie::class])
Route::get('/movies/{option}', [MovieController::class, 'index']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/countries', [CountryController::class, 'index']);

Route::post("/users", [UserController::class, 'store']);
Route::put("/users", [UserController::class, 'update']);

