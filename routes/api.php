<?php

use App\Http\Controllers\ActorController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DirectorController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoomController;
use App\Http\Middleware\Customer;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\UserController;

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

Route::get('/movies/{option}', [MovieController::class, 'index']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show'])->whereNumber('id');

Route::get('/countries', [CountryController::class, 'index']);
Route::middleware('auth:sanctum')->get("/roles", function (){
    return response()->json([
        'roles' => Role::all()
    ], 200);
});

Route::get("/directors/{id}", [DirectorController::class, 'show'])->whereNumber('id');
Route::get("/actors/{id}", [ActorController::class, 'show'])->whereNumber('id');
Route::middleware(Customer::class)->get("/movie/{id}", [MovieController::class, 'show'])->whereNumber('id');
Route::middleware(Customer::class)->get("/movie/{id}/comments", [CommentController::class, 'index_movie'])->whereNumber('id');
Route::middleware(Customer::class)->get("/review/{id}/comments", [CommentController::class, 'index_review'])->whereNumber('id');
Route::get("/movie/{id}/similar", [MovieController::class, 'similar'])->whereNumber('id');
Route::get("/movie/{id}/reviews", [MovieController::class, 'reviews'])->whereNumber('id');
Route::middleware(Customer::class)->get("/reviews", [ReviewController::class, 'index']);

Route::middleware('auth:sanctum')->get("/user/reviews", [ReviewController::class, 'index_user']);
Route::middleware('auth:sanctum')->post("/user/reviews", [ReviewController::class, 'store']);
Route::middleware('auth:sanctum')->put("/user/reviews", [ReviewController::class, 'update']);
Route::middleware('auth:sanctum')->put("/admin/reviews", [ReviewController::class, 'set_status']);
Route::middleware('auth:sanctum')->get("/reviews/{id}/manage", [ReviewController::class, 'manage'])->whereNumber('id');
Route::middleware('auth:sanctum')->get("/reviews/{id}", [ReviewController::class, 'show'])->whereNumber('id');
Route::middleware('auth:sanctum')->get("/admin/reviews", [ReviewController::class, 'index_admin']);
Route::middleware('auth:sanctum')->post("/review/rate", [ReviewController::class, 'rate']);
Route::middleware('auth:sanctum')->post("/review/comment", [ReviewController::class, 'comment']);


Route::middleware('auth:sanctum')->get("/users", [UserController::class, 'index']);

Route::middleware('auth:sanctum')->get("/users/{id}", [UserController::class, 'show'])->whereNumber('id');
Route::middleware('auth:sanctum')->post("/users", [UserController::class, 'store']);
Route::middleware('auth:sanctum')->put("/users", [UserController::class, 'update']);
Route::middleware('auth:sanctum')->delete("/users", [UserController::class, 'destroy']);

Route::middleware('auth:sanctum')->get("/admin/directors", [DirectorController::class, 'index']);
Route::middleware('auth:sanctum')->post("/admin/directors", [DirectorController::class, 'store']);
Route::middleware('auth:sanctum')->put("/admin/directors", [DirectorController::class, 'update']);
Route::middleware('auth:sanctum')->delete("/admin/directors", [DirectorController::class, 'destroy']);

Route::middleware('auth:sanctum')->post("/admin/categories", [CategoryController::class, 'store']);
Route::middleware('auth:sanctum')->put("/admin/categories", [CategoryController::class, 'update']);
Route::middleware('auth:sanctum')->delete("/admin/categories", [CategoryController::class, 'destroy']);

Route::middleware('auth:sanctum')->get("/admin/actors", [ActorController::class, 'index']);
Route::middleware('auth:sanctum')->post("/admin/actors", [ActorController::class, 'store']);
Route::middleware('auth:sanctum')->put("/admin/actors", [ActorController::class, 'update']);
Route::middleware('auth:sanctum')->delete("/admin/actors", [ActorController::class, 'destroy']);

Route::middleware('auth:sanctum')->get("/admin/movies", [MovieController::class, 'index_admin']);
Route::middleware('auth:sanctum')->get("/admin/movies/select", [MovieController::class, 'index_select']);
Route::middleware('auth:sanctum')->get("/admin/movies/{id}", [MovieController::class, 'show_admin'])->whereNumber('id');
Route::middleware('auth:sanctum')->post("/admin/movies", [MovieController::class, 'store']);
Route::middleware('auth:sanctum')->put("/admin/movies", [MovieController::class, 'update']);
Route::middleware('auth:sanctum')->put("/admin/movies/status", [MovieController::class, 'set_status']);
Route::middleware('auth:sanctum')->delete("/admin/movies", [MovieController::class, 'destroy']);
Route::middleware('auth:sanctum')->post("/movie/rate", [MovieController::class, 'rate']);
Route::middleware('auth:sanctum')->post("/movie/comment", [MovieController::class, 'comment']);

Route::middleware('auth:sanctum')->put("/comment", [CommentController::class, 'update']);
Route::middleware('auth:sanctum')->delete("/comment", [CommentController::class, 'destroy']);
//Route::middleware('auth:sanctum')->post("/comment/reply", [MovieController::class, 'reply']);

Route::fallback(fn() =>
    response()->json([
        'message' => 'Page Not Found'
    ], 404));

Route::middleware('auth:sanctum')->post("/message", [MessageController::class, 'store']);

Route::middleware('auth:sanctum')->get("/admin/rooms", [RoomController::class, 'index']);
Route::middleware('auth:sanctum')->get("/room/{id}/message", [RoomController::class, 'messages'])->whereNumber('id');
Route::middleware('auth:sanctum')->get("/room/message", [RoomController::class, 'messages']);
Route::middleware('auth:sanctum')->put("/room/seen/{id?}", [RoomController::class, 'seen'])->whereNumber('id');


