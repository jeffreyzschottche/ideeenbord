<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Quiz\QuizController;

Route::middleware('auth:brand_owner')->group(function () {
    Route::post('/quizzes', [QuizController::class, 'store']);
    Route::patch('/quizzes/{quiz}', [QuizController::class, 'update']);
    Route::post('/quizzes/{quiz}/close', [QuizController::class, 'close']);
    Route::post('/quizzes/{quiz}/select-winner', [QuizController::class, 'selectWinner']);
    Route::get('/quizzes/{quiz}/participants', [QuizController::class, 'participantsByQuiz']);
});

Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->middleware('auth:sanctum');
Route::post('/quizzes/{quiz}/submit', [QuizController::class, 'submit'])->middleware('auth:sanctum');
Route::get('/users/{username}/quiz-submissions', [QuizController::class, 'quizzesForUser'])->middleware('auth:sanctum');
Route::get('/quizzes', [QuizController::class, 'index']);
