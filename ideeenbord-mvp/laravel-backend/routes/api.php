<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BrandOwnerController;
use App\Http\Controllers\BrandOwnerAuthController;
use App\Http\Controllers\IdeaController;
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
   // BrandOwner Auth
   Route::post('/brand-owner/login', [BrandOwnerAuthController::class, 'login']);
    
   Route::middleware('auth:brand_owner')->group(function () { // 👈 fix hier
       Route::post('/brand-owner/logout', [BrandOwnerAuthController::class, 'logout']);
       Route::middleware('auth:brand_owner')->get('/brand-owner/me', [BrandOwnerAuthController::class, 'me']);
       Route::middleware('auth:brand_owner')->patch('/ideas/{idea}', [IdeaController::class, 'update']);
       Route::patch('/ideas/{idea}/pin', [IdeaController::class, 'pin']);
Route::patch('/ideas/{idea}/unpin', [IdeaController::class, 'unpin']);

    });

    // 🌐 Publieke routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/hi', fn () => response()->json(['message' => 'Hello World']));
    Route::prefix('brands')->group(function () {
        Route::get('/', [BrandController::class, 'index']);
        Route::get('/{brand}/ideas', [IdeaController::class, 'index']);
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
        Route::post('/ideas', [IdeaController::class, 'store']);
        Route::post('/ideas/{idea}/like', [IdeaController::class, 'like']);
        Route::post('/ideas/{idea}/dislike', [IdeaController::class, 'dislike']);

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

