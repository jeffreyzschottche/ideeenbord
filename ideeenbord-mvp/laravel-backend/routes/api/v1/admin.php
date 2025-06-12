<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Events\Registered;
use App\Http\Middleware\IsAdmin;
use App\Http\Controllers\BrandOwner\BrandOwnerController;
use App\Models\BrandOwner;
use App\Http\Controllers\Brand\BrandController;
use App\Http\Controllers\Admin\CmsPageController;
use App\Http\Controllers\Admin\CmsFieldController;
use App\Http\Controllers\Admin\CmsFileController;


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

    // Pages CMS
    Route::get('/cms/pages', [CmsPageController::class, 'index']);
    Route::post('/cms/pages', [CmsPageController::class, 'store']);
    Route::get('/cms/pages/{id}', [CmsPageController::class, 'show']);

    // Fields CMS
    Route::get('/cms/pages/{id}/fields', [CmsFieldController::class, 'index']);
    Route::post('/cms/pages/{id}/fields', [CmsFieldController::class, 'store']);
    Route::patch('/cms/pages/{id}/fields/{field}', [CmsFieldController::class, 'update']);

    // Images 
    Route::post('/cms/upload', [CmsFileController::class, 'upload']);

    Route::delete('/cms/pages/{id}/fields/{field}', [CmsFieldController::class, 'destroy']);

});