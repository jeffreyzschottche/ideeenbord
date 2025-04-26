<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BrandOwnerController;
use App\Http\Middleware\IsAdmin;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Hier registreer je API routes voor je applicatie. Deze routes worden 
| automatisch de "api" middlewaregroep toegewezen.
|
*/
Route::prefix('v1')->group(function () {
    
    // 🌐 Publieke routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/hi', fn () => response()->json(['message' => 'Hello World']));
    Route::prefix('brands')->group(function () {
        Route::get('/', [BrandController::class, 'index']);
        Route::get('/{slug}', [BrandController::class, 'show']); // NIEUW
          // Beveiligde POST-routes
          Route::middleware('auth:sanctum')->group(function () {
            Route::post('/request', [BrandController::class, 'store']);
            Route::post('/claim', [BrandOwnerController::class, 'store']);
        });
    });
      

    // 🔒 Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', fn (Request $request) => $request->user());
        Route::get('/me', fn (Request $request) => $request->user());
        Route::get('/bye', fn () => response()->json(['message' => 'Protected']));
        Route::post('/brands/{brand}/rate', [BrandController::class, 'rate']);

    });

    // 🔒👑 Admin routes
    Route::middleware(['auth:sanctum', IsAdmin::class])->prefix('admin')->group(function () {
        Route::post('/brands/owners/{id}/verify', function ($id) {
            $owner = \App\Models\BrandOwner::findOrFail($id);
            $owner->verified_owner = true;
            $owner->save();
            return response()->json(['message' => 'BrandOwner verified']);
        });

        Route::get('/brand-owners', [BrandOwnerController::class, 'index']);
    });

});

