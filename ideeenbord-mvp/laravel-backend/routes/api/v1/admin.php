<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Events\Registered;
use App\Http\Middleware\IsAdmin;
use App\Http\Controllers\BrandOwner\BrandOwnerController;
use App\Models\BrandOwner;
use App\Http\Controllers\Brand\BrandController;

Route::middleware(['auth:sanctum', IsAdmin::class])->prefix('admin')->group(function () {
    Route::post('/brands/owners/{id}/verify', function ($id) {
        $owner = BrandOwner::findOrFail($id);

        $owner->verified_owner = true;
        $owner->save();

        event(new Registered($owner));

        return response()->json(['message' => 'BrandOwner succesvol goedgekeurd door admin.']);
    });

    Route::get('/brand-owners', [BrandOwnerController::class, 'index']);
    Route::get('/brands/pending', [BrandController::class, 'pending']);
    Route::post('/brands/{brand}/accept', [BrandController::class, 'accept']);
});