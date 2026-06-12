<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Brand\BrandController;
use App\Http\Controllers\Brand\BrandReportController;
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
Route::get('/search/brands', [BrandController::class, 'search']);

Route::prefix('brands')->group(function () {
    Route::get('/', [BrandController::class, 'index']);
    Route::get('/{brand}/ideas', [IdeaController::class, 'index']);
    Route::get('/{slug}', [BrandController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/request', [BrandController::class, 'store'])->middleware('throttle:10,1');
        Route::post('/claim', [BrandOwnerController::class, 'store'])->middleware('throttle:5,1');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/brands/{brand}/rate', [BrandController::class, 'rate']);
    Route::post('/brands/{brand}/main-question-response', [MainQuestionResponseController::class, 'store']);
    Route::get('/brands/{brand}/participants', [BrandController::class, 'participants']);
});

Route::middleware('auth:brand_owner')->group(function () {
    Route::patch('/brands/{brand}/main-questions', [BrandController::class, 'setMainQuestion']);
    Route::patch('/brands/{brand}', [BrandController::class, 'update']);
    Route::get('/brands/{brand}/raw-export', [BrandController::class, 'rawExport']);
    Route::post('/brands/{brand}/logo', [BrandController::class, 'uploadLogo']);

    // Live statistics (no AI) — current analytics for the dashboard
    Route::get('/brands/{brand}/stats', [BrandReportController::class, 'stats']);
    // Available months/date-bounds with data (drives the period pickers)
    Route::get('/brands/{brand}/report-range', [BrandReportController::class, 'range']);

    // AI brand reports
    Route::get('/brands/{brand}/reports', [BrandReportController::class, 'index']);
    Route::post('/brands/{brand}/reports', [BrandReportController::class, 'store']);
    Route::get('/reports/{report}', [BrandReportController::class, 'show']);
});
