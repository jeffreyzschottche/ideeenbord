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
    Route::post('/brands/request', [BrandController::class, 'store']);
    Route::post('/brands/claim', [BrandOwnerController::class, 'store']);
    Route::get('/brands', [BrandController::class, 'index']);

    // 🔒 Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', fn (Request $request) => $request->user());
        Route::get('/me', fn (Request $request) => $request->user());
        Route::get('/bye', fn () => response()->json(['message' => 'Protected']));
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


// // Voorbeeld van een beveiligde route
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::middleware('auth:sanctum')->get('/bye', function (Request $request) {
//     return response()->json(['message' => 'Sanctum Protected Message From laravel-backend/api.php']);
// });


// Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
//     return $request->user();
// });

// Route::get('/hi', function (Request $request) {
//     return response()->json(['message' => 'Viewable from laravel-backend/api.php']);
// });

// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);

// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/user', function (Request $request) {
//         return $request->user();
//     });
// });

// Route::post('/brands/request', [BrandController::class, 'store']);

// Route::post('/brands/claim', [BrandOwnerController::class, 'store']);

// Route::get('/brands', [BrandController::class, 'index']);

// Route::middleware(['auth:sanctum', IsAdmin::class])->group(function () {
//     Route::post('/brands/owners/{id}/verify', function ($id) {
//         $owner = \App\Models\BrandOwner::findOrFail($id);
//         $owner->verified_owner = true;
//         $owner->save(); // ➤ dit triggert de observer
//         return response()->json(['message' => 'BrandOwner verified!']);
//     });
    
//     Route::get('/brand-owners', [BrandOwnerController::class, 'index']);
    
// });