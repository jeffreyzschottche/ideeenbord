<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Brand\BrandController;
use App\Http\Controllers\BrandOwner\BrandOwnerController;
use App\Http\Controllers\MainQuestion\MainQuestionController;
use App\Http\Controllers\MainQuestion\MainQuestionResponseController;
use App\Http\Controllers\Ideas\IdeaController;
use App\Http\Controllers\Quiz\QuizController;

Route::get('/main-questions', [MainQuestionController::class, 'index']);
Route::get('/main-questions/{mainQuestion}', [MainQuestionController::class, 'show']);

Route::get('/brands/{brand}/quiz', [QuizController::class, 'activeForBrand']);
Route::get('/brands/{brand}/quiz/participants', [QuizController::class, 'getParticipants']);
Route::get('/brands/{brand}/quizzes', [QuizController::class, 'listForBrand']);

Route::prefix('brands')->group(function () {
    Route::get('/', [BrandController::class, 'index']);
    Route::get('/{brand}/ideas', [IdeaController::class, 'index']);
    Route::get('/{slug}', [BrandController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/request', [BrandController::class, 'store']);
        Route::post('/claim', [BrandOwnerController::class, 'store']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/brands/{brand}/rate', [BrandController::class, 'rate']);
    Route::post('/brands/{brand}/main-question-response', [MainQuestionResponseController::class, 'store']);
});

Route::middleware('auth:brand_owner')->group(function () {
    Route::patch('/brands/{brand}/main-questions', [BrandController::class, 'setMainQuestion']);
    Route::patch('/brands/{brand}', [BrandController::class, 'update']);
});