<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Content\ContentController;

Route::get('/content/{slug}', [ContentController::class, 'show']);
