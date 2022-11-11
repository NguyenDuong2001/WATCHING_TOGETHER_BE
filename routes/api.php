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
Route::get('/categories/{id}', [CategoryController::class, 'show'])->whereNumber('id');

Route::get('/countries', [CountryController::class, 'index']);
Route::middleware('auth:sanctum')->get("/roles", function (){
    return response()->json([
        'roles' => \App\Models\Role::all()
    ], 200);
});

Route::get("/directors/{id}", [\App\Http\Controllers\DirectorController::class, 'show'])->whereNumber('id');
Route::get("/actors/{id}", [\App\Http\Controllers\ActorController::class, 'show'])->whereNumber('id');
Route::middleware(\App\Http\Middleware\Customer::class)->get("/movie/{id}", [\App\Http\Controllers\MovieController::class, 'show'])->whereNumber('id');
Route::middleware(\App\Http\Middleware\Customer::class)->get("/movie/{id}/comments", [\App\Http\Controllers\CommentController::class, 'index'])->whereNumber('id');
Route::get("/movie/{id}/similar", [MovieController::class, 'similar'])->whereNumber('id');
Route::get("/movie/{id}/reviews", [MovieController::class, 'reviews'])->whereNumber('id');
Route::get("/reviews", [\App\Http\Controllers\ReviewController::class, 'index']);

Route::middleware('auth:sanctum')->get("/user/{id}/reviews", [\App\Http\Controllers\ReviewController::class, 'index_user'])->whereNumber('id');
Route::middleware('auth:sanctum')->post("/user/reviews", [\App\Http\Controllers\ReviewController::class, 'store']);
Route::middleware('auth:sanctum')->put("/user/reviews", [\App\Http\Controllers\ReviewController::class, 'update']);
Route::middleware('auth:sanctum')->put("/admin/reviews", [\App\Http\Controllers\ReviewController::class, 'set_status']);
Route::middleware('auth:sanctum')->get("/admin/reviews", [\App\Http\Controllers\ReviewController::class, 'index_admin']);


Route::middleware('auth:sanctum')->get("/users", [UserController::class, 'index']);

Route::middleware('auth:sanctum')->get("/users/{id}", [UserController::class, 'show'])->whereNumber('id');
Route::middleware('auth:sanctum')->post("/users", [UserController::class, 'store']);
Route::middleware('auth:sanctum')->put("/users", [UserController::class, 'update']);
Route::middleware('auth:sanctum')->delete("/users", [UserController::class, 'destroy']);

Route::middleware('auth:sanctum')->get("/admin/directors", [\App\Http\Controllers\DirectorController::class, 'index']);
Route::middleware('auth:sanctum')->post("/admin/directors", [\App\Http\Controllers\DirectorController::class, 'store']);
Route::middleware('auth:sanctum')->put("/admin/directors", [\App\Http\Controllers\DirectorController::class, 'update']);
Route::middleware('auth:sanctum')->delete("/admin/directors", [\App\Http\Controllers\DirectorController::class, 'destroy']);

Route::middleware('auth:sanctum')->post("/admin/categories", [\App\Http\Controllers\CategoryController::class, 'store']);
Route::middleware('auth:sanctum')->put("/admin/categories", [\App\Http\Controllers\CategoryController::class, 'update']);
Route::middleware('auth:sanctum')->delete("/admin/categories", [\App\Http\Controllers\CategoryController::class, 'destroy']);

Route::middleware('auth:sanctum')->get("/admin/actors", [\App\Http\Controllers\ActorController::class, 'index']);
Route::middleware('auth:sanctum')->post("/admin/actors", [\App\Http\Controllers\ActorController::class, 'store']);
Route::middleware('auth:sanctum')->put("/admin/actors", [\App\Http\Controllers\ActorController::class, 'update']);
Route::middleware('auth:sanctum')->delete("/admin/actors", [\App\Http\Controllers\ActorController::class, 'destroy']);

Route::middleware('auth:sanctum')->get("/admin/movies", [MovieController::class, 'index_admin']);
Route::middleware('auth:sanctum')->get("/admin/movies/{id}", [MovieController::class, 'show_admin'])->whereNumber('id');
Route::middleware('auth:sanctum')->post("/admin/movies", [MovieController::class, 'store']);
Route::middleware('auth:sanctum')->put("/admin/movies", [MovieController::class, 'update']);
Route::middleware('auth:sanctum')->put("/admin/movies/status", [MovieController::class, 'set_status']);
Route::middleware('auth:sanctum')->delete("/admin/movies", [MovieController::class, 'destroy']);
Route::middleware('auth:sanctum')->post("/movie/rate", [MovieController::class, 'rate']);
Route::middleware('auth:sanctum')->post("/movie/comment", [\App\Http\Controllers\CommentController::class, 'store']);
Route::middleware('auth:sanctum')->put("/movie/comment", [\App\Http\Controllers\CommentController::class, 'update']);
Route::middleware('auth:sanctum')->delete("/movie/comment", [\App\Http\Controllers\CommentController::class, 'destroy']);
Route::middleware('auth:sanctum')->post("/comment/reply", [MovieController::class, 'reply']);


Route::get('stream',function (){
    $videosDir = config('larastreamer.basepath');
    if (file_exists($filePath = $videosDir."/".'[S7] Tuyển Tập Doraemon - Phần 59 - Ngày Sinh Nhật Rỗng Túi Của Suneo, Triệu Phú Nobita.mp4')) {
//        $stream = new \Raju\Streamer\Helpers\VideoStream($filePath);
        \Iman\Streamer\VideoStreamer::streamFile($filePath);
//        return response()->stream(function() use ($stream) {
//            $stream->start();
//        });
    }
    return response("File doesn't exists", 404);
});

Route::fallback(function(){
    return response()->json([
        'message' => 'Page Not Found'
    ], 404);
});

