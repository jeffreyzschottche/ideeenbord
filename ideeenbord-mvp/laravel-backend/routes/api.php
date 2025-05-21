<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BrandOwnerController;
use App\Http\Controllers\BrandOwnerAuthController;
use App\Http\Controllers\IdeaController;
use App\Http\Middleware\IsAdmin;
use App\Http\Controllers\MainQuestionController;
USE App\Http\Controllers\MainQuestionResponseController;
use App\Http\Controllers\QuizController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\IdeaStatusChangedMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;


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

    
Route::get('/brand-owner/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $owner = \App\Models\BrandOwner::findOrFail($request->id);

    if (!hash_equals((string) $request->hash, sha1($owner->getEmailForVerification()))) {
        throw new \Illuminate\Auth\Access\AuthorizationException('Invalid verification link');
    }

    if (!$owner->hasVerifiedEmail()) {
        $owner->markEmailAsVerified();
        $owner->verified_owner = true; // ✅ Dit is jouw extra check
        $owner->save();
        event(new Verified($owner));
    }

    return redirect('http://localhost:3000/brand-owner/verify-success');
})->middleware('signed')->name('brandowner.verification.verify');

Route::post('/brand-owner/email/resend', function (Request $request) {
    $request->user('brand_owner')->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verificatie opnieuw verzonden']);
})->middleware('auth:brand_owner');
 
    Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
        $user = User::findOrFail($id);
    
        if (! URL::hasValidSignature($request)) {
            throw new AuthorizationException('Ongeldige of verlopen link.');
        }
    
        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            throw new AuthorizationException('Hash mismatch.');
        }
    
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }
    
        $query = http_build_query([
            'id' => $id,
            'hash' => $hash,
            'expires' => $request->query('expires'),
            'signature' => $request->query('signature'),
        ]);
        
        return redirect("http://localhost:3000/email-verification?$query");
        
    })->middleware('signed')->name('verification.verify');


Route::get('/verify-email', function (Request $request) {
    $user = User::findOrFail($request->query('id'));

    if (! URL::hasValidSignature($request)) {
        throw new AuthorizationException('Ongeldige of verlopen link');
    }

    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    return response()->json(['message' => 'Email succesvol geverifieerd.']);
});
    
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verificatie-email verstuurd']);
    });
   // BrandOwner Auth
   Route::post('/brand-owner/login', [BrandOwnerAuthController::class, 'login']);
   Route::get('/main-questions', [MainQuestionController::class, 'index']);
   Route::get('/main-questions/{mainQuestion}', [MainQuestionController::class, 'show']);

   Route::get('/brands/{brand}/quiz', [QuizController::class, 'activeForBrand']);
Route::get('/brands/{brand}/quiz/participants', [QuizController::class, 'getParticipants']);

Route::get('/brands/{brand}/quizzes', [QuizController::class, 'listForBrand']);

    
   Route::middleware('auth:brand_owner')->group(function () {
       Route::post('/brand-owner/logout', [BrandOwnerAuthController::class, 'logout']);
       Route::get('/brand-owner/me', [BrandOwnerAuthController::class, 'me']);
       Route::middleware('auth:brand_owner')->patch('/ideas/{idea}', [IdeaController::class, 'update']);
       Route::patch('/ideas/{idea}/pin', [IdeaController::class, 'pin']);
       Route::patch('/ideas/{idea}/unpin', [IdeaController::class, 'unpin']);
       Route::patch('/brands/{brand}/main-questions', [BrandController::class, 'setMainQuestion']);
       Route::post('/quizzes', [QuizController::class, 'store']);
       Route::patch('/quizzes/{quiz}', [QuizController::class, 'update']);
       Route::post('/quizzes/{quiz}/close', [QuizController::class, 'close']);
       Route::post('/quizzes/{quiz}/select-winner', [QuizController::class, 'selectWinner']);
       Route::get('/quizzes/{quiz}/participants', [QuizController::class, 'participantsByQuiz']);
       Route::patch('/brands/{brand}', [BrandController::class, 'update']);

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
        Route::post('/brands/{brand}/main-question-response', [MainQuestionResponseController::class, 'store']);
        Route::get('/quizzes/{quiz}', [QuizController::class, 'show']);
        Route::post('/quizzes/{quiz}/submit', [QuizController::class, 'submit']);
        Route::get('/users/{username}/quiz-submissions', [QuizController::class, 'quizzesForUser']);
        Route::get('/ideas', [IdeaController::class, 'getMultipleByIds']);
        Route::patch('/users/{username}', [AuthController::class, 'update']);
        Route::get('/users/{username}', [AuthController::class, 'showByUsername']);
        Route::get('/users/{username}/ideas', [IdeaController::class, 'getIdeasByUser']);
        Route::get('/users/{username}/notifications', [AuthController::class, 'notifications']);
    });

    // 🔒👑 Admin routes
    Route::middleware(['auth:sanctum', IsAdmin::class])->prefix('admin')->group(function () {
        // Route::post('/brands/owners/{id}/verify', function ($id) {
        //     $owner = \App\Models\BrandOwner::findOrFail($id);
        //     $owner->verified_owner = true;
        //     $owner->save();
        //     return response()->json(['message' => 'BrandOwner verified']);
        // });
        Route::post('/brands/owners/{id}/verify', function ($id) {
    $owner = \App\Models\BrandOwner::findOrFail($id);

    if ($owner->hasVerifiedEmail()) {
        return response()->json(['message' => 'Deze eigenaar is al geverifieerd.']);
    }

    $owner->verified_owner = true;
    $owner->save();

    // ✅ Verificatie-e-mail sturen
    event(new Registered($owner));

    return response()->json(['message' => 'BrandOwner verified & verificatiemail verzonden.']);
});

        Route::get('/brand-owners', [BrandOwnerController::class, 'index']);
    });

});

