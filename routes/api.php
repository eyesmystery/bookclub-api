<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DivisionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\BookLikeController;
use App\Http\Controllers\Api\BookReviewController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\NewsController;
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

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});

// Division routes
Route::get('divisions', [DivisionController::class, 'index']);
Route::get('divisions/{division}', [DivisionController::class, 'show']);

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('divisions', [DivisionController::class, 'store']);
    Route::put('divisions/{division}', [DivisionController::class, 'update']);
    Route::delete('divisions/{division}', [DivisionController::class, 'destroy']);
});

// User routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('users/{user}', [UserController::class, 'show']);
    Route::put('users/{user}', [UserController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('users', [UserController::class, 'index']);
    Route::delete('users/{user}', [UserController::class, 'destroy']);
});

// Book routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('books', [BookController::class, 'index']);
    
    // Book reviews routes - specific routes before parameterized ones
    Route::get('books/reviewed', [BookReviewController::class, 'reviewedBooks']);
    
    Route::get('books/{book}', [BookController::class, 'show']);
    Route::get('books/{book}/reviews', [BookReviewController::class, 'index']);
    
    // Book likes routes
    Route::post('books/{book}/like', [BookLikeController::class, 'toggle']);
    
    // Book reviews routes
    Route::post('books/{book}/review', [BookReviewController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Admin book management
    Route::post('books', [BookController::class, 'store']);
    Route::put('books/{book}', [BookController::class, 'update']);
    Route::delete('books/{book}', [BookController::class, 'destroy']);
    
    // Admin review management
    Route::delete('reviews/{review}', [BookReviewController::class, 'destroy']);
});

// Event routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('events', [EventController::class, 'index']);
    Route::get('events/{event}', [EventController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('events', [EventController::class, 'store']);
    Route::put('events/{event}', [EventController::class, 'update']);
    Route::delete('events/{event}', [EventController::class, 'destroy']);
});

// Article routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('articles', [ArticleController::class, 'index']);
    Route::get('articles/{article}', [ArticleController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('articles', [ArticleController::class, 'store']);
    Route::put('articles/{article}', [ArticleController::class, 'update']);
    Route::delete('articles/{article}', [ArticleController::class, 'destroy']);
});

// News routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('news', [NewsController::class, 'index']);
    Route::get('news/{news}', [NewsController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('news', [NewsController::class, 'store']);
    Route::put('news/{news}', [NewsController::class, 'update']);
    Route::delete('news/{news}', [NewsController::class, 'destroy']);
}); 