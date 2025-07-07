<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ideas\IdeaController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/ideas', [IdeaController::class, 'store']);
    Route::post('/ideas/{idea}/like', [IdeaController::class, 'like']);
    Route::post('/ideas/{idea}/dislike', [IdeaController::class, 'dislike']);
    Route::get('/ideas', [IdeaController::class, 'getMultipleByIds']);
    Route::get('/users/{username}/ideas', [IdeaController::class, 'getIdeasByUser']);
});

Route::middleware('auth:brand_owner')->group(function () {
    Route::patch('/ideas/{idea}', [IdeaController::class, 'update']);
    Route::patch('/ideas/{idea}/pin', [IdeaController::class, 'pin']);
    Route::patch('/ideas/{idea}/unpin', [IdeaController::class, 'unpin']);
});

Route::get('/ideas-feed', [IdeaController::class, 'feed']);