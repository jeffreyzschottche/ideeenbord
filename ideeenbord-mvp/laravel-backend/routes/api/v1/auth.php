<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Events\Registered;
use App\Models\User;
use App\Models\BrandOwner;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\BrandOwner\BrandOwnerAuthController;
use App\Http\Controllers\Ideas\IdeaController;
use App\Http\Controllers\Brand\BrandController;
use App\Http\Controllers\Quiz\QuizController;

Route::get('/test-verification/{id}', function ($id) {
    $user = \App\Models\User::findOrFail($id);
    $user->sendEmailVerificationNotification();
    return 'done';
});

Route::get('/brand-owner/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $owner = BrandOwner::findOrFail($id);

    if (!hash_equals((string) $hash, sha1($owner->getEmailForVerification()))) {
        return redirect(config('app.frontend_url') . '/notifications/verification-failed');
    }

    if (!$owner->hasVerifiedEmail()) {
        $owner->markEmailAsVerified();
        event(new Verified($owner));
    }

    return redirect(config('app.frontend_url') . '/notifications/brandowner-verification');
})->middleware('signed')->name('brandowner.verification.verify');

Route::post('/brand-owner/email/resend', function (Request $request) {
    $request->user('brand_owner')->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verificatie opnieuw verzonden']);
})->middleware('auth:brand_owner');

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);

    if (!URL::hasValidSignature($request)) {
        throw new AuthorizationException('Ongeldige of verlopen link.');
    }

    if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
        throw new AuthorizationException('Hash mismatch.');
    }

    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    $query = http_build_query([
        'id' => $id,
        'hash' => $hash,
        'expires' => $request->query('expires'),
        'signature' => $request->query('signature'),
    ]);

    return redirect(config('app.frontend_url') . "/notifications/email-verification?$query");
})->middleware('signed')->name('verification.verify');

Route::get('/verify-email', function (Request $request) {
    $request->validate(['id' => 'required|exists:users,id']);

    $user = User::findOrFail($request->query('id'));

    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email is succesvol geverifieerd.']);
    } else {
        return response()->json(['message' => 'Verificatie kon niet worden bevestigd.'], 422);
    }
})->name('verification.status.check');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verificatie-email verstuurd']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/brand-owner/login', [BrandOwnerAuthController::class, 'login']);

Route::middleware('auth:brand_owner')->group(function () {
    Route::post('/brand-owner/logout', [BrandOwnerAuthController::class, 'logout']);
    Route::get('/brand-owner/me', [BrandOwnerAuthController::class, 'me']);
    Route::patch('/brand-owner/account', function (Request $request) {
        $owner = auth('brand_owner')->user();

        $data = $request->validate([
            'email' => 'required|email|unique:brand_owners,email,' . $owner->id,
            'phone' => 'nullable|string',
            'subscription_plan' => 'required|in:Brons,Zilver,Goud',
            'password' => 'nullable|confirmed|min:6',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $owner->update($data);

        return response()->json(['message' => 'Gegevens bijgewerkt']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());
    Route::get('/me', fn(Request $request) => $request->user());
    Route::get('/bye', fn() => response()->json(['message' => 'Protected']));
    Route::patch('/users/{username}', [AuthController::class, 'update']);
    Route::get('/users/{username}', [AuthController::class, 'showByUsername']);
    Route::get('/users/{username}/notifications', [AuthController::class, 'notifications']);
});