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

    
   Route::middleware('auth:brand_owner')->group(function () { // 👈 fix hier
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
    Route::get('/ideas', [IdeaController::class, 'getMultipleByIds']);



      

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

