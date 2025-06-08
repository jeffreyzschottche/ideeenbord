<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\Brand\BrandController;
use App\Http\Controllers\BrandOwner\BrandOwnerController;
use App\Http\Controllers\BrandOwner\BrandOwnerAuthController;
use App\Http\Controllers\Ideas\IdeaController;
use App\Http\Middleware\IsAdmin;
use App\Http\Controllers\MainQuestion\MainQuestionController;
use App\Http\Controllers\MainQuestion\MainQuestionResponseController;
use App\Http\Controllers\Quiz\QuizController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\IdeaStatusChangedMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use App\Models\BrandOwner;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Register all API routes for the application. These routes are automatically
| assigned to the "api" middleware group.
|
*/
Route::prefix('v1')->group(function () {
    // ------------------------------------------------------------------
    // Email verification test routes
    // ------------------------------------------------------------------
    Route::get('/test-verification/{id}', function ($id) {
        $user = \App\Models\User::findOrFail($id);
        $user->sendEmailVerificationNotification();
        return 'done';
    });


    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/hi', fn() => response()->json(['message' => 'Hello World']));
    Route::prefix('brands')->group(function () {
        Route::get('/', [BrandController::class, 'index']);
        Route::get('/{brand}/ideas', [IdeaController::class, 'index']);
        Route::get('/{slug}', [BrandController::class, 'show']); // NIEUW
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/request', [BrandController::class, 'store']);
            Route::post('/claim', [BrandOwnerController::class, 'store']);
        });
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
            return response()->json(['message' => 'Verificatie kon niet worden bevestigd.'], 422); // Unprocessable Entity
        }
    })->name('verification.status.check');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verificatie-email verstuurd']);
    });

    // Public Brand Information Routes
    Route::post('/brand-owner/login', [BrandOwnerAuthController::class, 'login']);
    Route::get('/main-questions', [MainQuestionController::class, 'index']);
    Route::get('/main-questions/{mainQuestion}', [MainQuestionController::class, 'show']);
    Route::get('/brands/{brand}/quiz', [QuizController::class, 'activeForBrand']);
    Route::get('/brands/{brand}/quiz/participants', [QuizController::class, 'getParticipants']);
    Route::get('/brands/{brand}/quizzes', [QuizController::class, 'listForBrand']);

    // BrandOwner Auth
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

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', fn(Request $request) => $request->user());
        Route::get('/me', fn(Request $request) => $request->user());
        Route::get('/bye', fn() => response()->json(['message' => 'Protected']));

        Route::get('/quizzes/{quiz}', [QuizController::class, 'show']);
        Route::get('/users/{username}/quiz-submissions', [QuizController::class, 'quizzesForUser']);
        Route::get('/ideas', [IdeaController::class, 'getMultipleByIds']);
        Route::get('/users/{username}', [AuthController::class, 'showByUsername']);
        Route::get('/users/{username}/ideas', [IdeaController::class, 'getIdeasByUser']);
        Route::get('/users/{username}/notifications', [AuthController::class, 'notifications']);

        Route::post('/brands/{brand}/rate', [BrandController::class, 'rate']);
        Route::post('/brands/{brand}/main-question-response', [MainQuestionResponseController::class, 'store']);


        Route::post('/ideas', [IdeaController::class, 'store']);
        Route::post('/ideas/{idea}/like', [IdeaController::class, 'like']);
        Route::post('/ideas/{idea}/dislike', [IdeaController::class, 'dislike']);


        Route::post('/quizzes/{quiz}/submit', action: [QuizController::class, 'submit']);

        Route::patch('/users/{username}', [AuthController::class, 'update']);
    });


    // Admin-only Routes
    Route::middleware(['auth:sanctum', IsAdmin::class])->prefix('admin')->group(function () {
        Route::post('/brands/owners/{id}/verify', function ($id) {
            $owner = \App\Models\BrandOwner::findOrFail($id);

            $owner->verified_owner = true;
            $owner->save();

            event(new Registered($owner));

            return response()->json(['message' => 'BrandOwner succesvol goedgekeurd door admin.']);
        });
        Route::get('/brand-owners', [BrandOwnerController::class, 'index']);
        Route::get('/brands/pending', [BrandController::class, 'pending']);
        Route::post('/brands/{brand}/accept', [BrandController::class, 'accept']);
    });

});

