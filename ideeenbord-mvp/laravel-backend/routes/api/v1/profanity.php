<?php

use Illuminate\Support\Facades\Route;

Route::get('profanity', function () {
    return response()->json(config('profanity.bad_words'));
});

