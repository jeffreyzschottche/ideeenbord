<?php

namespace App\Http\Controllers\Ideas;

use App\Models\Idea;
use Illuminate\Http\Request;
use App\Mail\IdeaStatusChangedMail;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIdeaRequest;
use App\Models\IdeaReport;

/** *
 * This controller manages all idea-related actions within the application,
 * including creation, liking/disliking, status updates, pinning/unpinning, 
 * and retrieving ideas based on various criteria.
 */
class IdeaController extends Controller
{
    /**
     * Store a new idea for a specific brand by the authenticated user.
     *
     * @param Request $request The HTTP request containing idea details.
     * @return \Illuminate\Http\JsonResponse JSON response with confirmation or error.
     */
    public function store(StoreIdeaRequest $request)
    {
        $user = $request->user();          // ingelogde gebruiker
        $data = $request->validated();     // brand_id, title, description

        // ── Limiet: max 5 ideeën per brand ─────────────────────────
        $already = Idea::where('brand_id', $data['brand_id'])
            ->where('user_id', $user->id)
            ->count();

        if ($already >= 5) {
            return response()->json(
                ['message' => 'Je mag maximaal 5 ideeën per merk plaatsen.'],
                403
            );
        }

        // ── Aanmaken ───────────────────────────────────────────────
        $idea = Idea::create([
            'brand_id' => $data['brand_id'],
            'user_id' => $user->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
        ]);

        // ── Bijhouden in user->created_posts ───────────────────────
        $user->created_posts = [...($user->created_posts ?? []), $idea->id];
        $user->save();

        return response()->json([
            'message' => 'Idee succesvol geplaatst',
            'idea' => $idea,
        ]);
    }
    /**
     * Get all ideas for a specific brand, sorted by pinned and created date.
     *
     * @param int $brandId The ID of the brand.
     * @return \Illuminate\Http\JsonResponse JSON response containing a list of ideas.
     */
    public function index(Request $request, $brandId)
    {
        $ideas = \App\Models\Idea::with('user:id,username')   // 👈 alleen id + username
            ->where('brand_id', $brandId)
            ->latest()
            ->get([
                'id',
                'brand_id',
                'title',
                'description',
                'status',
                'likes',
                'dislikes',
                'is_pinned',
                'user_id',
            ]);

        return response()->json($ideas);
    }

    // public function index($brandId)
    // {
    //     $ideas = Idea::where('brand_id', $brandId)
    //         ->orderBy('is_pinned', 'desc')
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     return response()->json($ideas);
    // }
    /**
     * Like a specific idea by the authenticated user.
     *
     * @param Idea $idea The idea to like.
     * @param Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse JSON response with confirmation or error.
     */
    public function like(Idea $idea, Request $request)
    {
        $user = $request->user();

        if (in_array($idea->id, $user->liked_posts ?? [])) {
            return response()->json(['message' => 'Je hebt dit idee al geliked.'], 403);
        }

        if (in_array($idea->id, $user->disliked_posts ?? [])) {
            $idea->decrement('dislikes');
            $user->disliked_posts = array_diff($user->disliked_posts, [$idea->id]);
        }

        $idea->increment('likes');
        $user->liked_posts = [...($user->liked_posts ?? []), $idea->id];
        $user->save();

        $user = $idea->user;
        $notifications = $user->notifications ?? [];
        $notifications[] = [
            'type' => 'idea_like',
            'idea_id' => $idea->id,
            'message' => "👍 Je idee '{$idea->title}' heeft een nieuwe like gekregen!",
            'timestamp' => now(),
        ];
        $user->notifications = $notifications;
        $user->save();


        return response()->json(['message' => 'Je idee is nu geliked!']);
    }
    /**
     * Dislike a specific idea by the authenticated user.
     *
     * @param Idea $idea The idea to dislike.
     * @param Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse JSON response with confirmation or error.
     */
    public function dislike(Idea $idea, Request $request)
    {
        $user = $request->user();

        // Als al gedisliked is: error
        if (in_array($idea->id, $user->disliked_posts ?? [])) {
            return response()->json(['message' => 'Je hebt dit idee al gedisliked.'], 403);
        }

        // Als geliked was: like ongedaan maken
        if (in_array($idea->id, $user->liked_posts ?? [])) {
            $idea->decrement('likes');
            $user->liked_posts = array_diff($user->liked_posts, [$idea->id]);
        }

        // Dislike toevoegen
        $idea->increment('dislikes');
        $user->disliked_posts = [...($user->disliked_posts ?? []), $idea->id];
        $user->save();

        return response()->json(['message' => 'Je idee is nu gedisliked!']);
    }
    /**
     * Update the status of an idea and notify the user.
     *
     * @param Request $request The HTTP request containing status.
     * @param Idea $idea The idea to update.
     * @return \Illuminate\Http\JsonResponse JSON response with confirmation.
     */
    public function update(Request $request, Idea $idea)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:rejected,in_progress,completed,pending',
        ]);

        $oldStatus = $idea->status;

        $idea->status = $validated['status'];
        $idea->save();

        if ($idea->status !== $oldStatus) {
            $user = $idea->user;

            $notifications = $user->notifications ?? [];
            $notifications[] = [
                'type' => 'idea_status',
                'idea_id' => $idea->id,
                'message' => "🔄 De status van je idee '{$idea->title}' is gewijzigd naar '{$idea->status}'.",
                'timestamp' => now(),
            ];
            $user->notifications = $notifications;
            $user->save();

            if ($user->email) {
                Mail::to($user->email)->send(new IdeaStatusChangedMail($idea));
            }
        }

        return response()->json(['message' => 'Status succesvol aangepast.']);
    }

    /**
     * Pin an idea for a brand by the authenticated brand owner.
     *
     * @param Idea $idea The idea to pin.
     * @return \Illuminate\Http\JsonResponse JSON response with confirmation or error.
     */
    public function pin(Idea $idea)
    {
        $user = auth('brand_owner')->user();

        if (!$user) {
            return response()->json(['message' => 'Niet geautoriseerd.'], 403);
        }

        $brand = $idea->brand;

        if (!$brand || $brand->id !== $user->brand_id) {
            return response()->json(['message' => 'Geen toegang tot dit merk.'], 403);
        }

        // Update het idee als gepind
        $idea->is_pinned = true;
        $idea->save();

        // Voeg toe aan pinned_ideas van het merk
        $pinnedIdeas = $brand->pinned_ideas ?? [];
        if (!in_array($idea->id, $pinnedIdeas)) {
            $pinnedIdeas[] = $idea->id;
            $brand->pinned_ideas = $pinnedIdeas;
            $brand->save();
        }

        return response()->json(['message' => 'Idee succesvol gepind.']);
    }

    /**
     * Unpin an idea for a brand by the authenticated brand owner.
     *
     * @param Idea $idea The idea to unpin.
     * @return \Illuminate\Http\JsonResponse JSON response with confirmation or error.
     */
    public function unpin(Idea $idea)
    {
        $user = auth('brand_owner')->user();

        if (!$user) {
            return response()->json(['message' => 'Niet geautoriseerd.'], 403);
        }

        $brand = $idea->brand;

        if (!$brand || $brand->id !== $user->brand_id) {
            return response()->json(['message' => 'Geen toegang tot dit merk.'], 403);
        }

        $idea->is_pinned = false;
        $idea->save();

        $pinnedIdeas = $brand->pinned_ideas ?? [];
        $pinnedIdeas = array_filter($pinnedIdeas, fn($id) => $id != $idea->id);
        $brand->pinned_ideas = array_values($pinnedIdeas);
        $brand->save();

        return response()->json(['message' => 'Idee succesvol ontpind.']);
    }
    /**
     * Get multiple ideas by a list of IDs.
     *
     * @param Request $request The HTTP request containing IDs query parameter.
     * @return \Illuminate\Http\JsonResponse JSON response with ideas.
     */
    public function getMultipleByIds(Request $request)
    {
        $ids = $request->query('ids');

        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }

        $ideas = \App\Models\Idea::with('brand')->whereIn('id', $ids)->get();

        return response()->json($ideas);
    }
    /**
     * Get all ideas submitted by a specific user by username.
     *
     * @param string $username The username to filter ideas.
     * @return \Illuminate\Http\JsonResponse JSON response with user's ideas.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If user is not found.
     */
    public function getIdeasByUser($username)
    {
        $user = \App\Models\User::where('username', $username)->firstOrFail();

        $ideas = \App\Models\Idea::with('brand')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($ideas);
    }
    public function destroyByUser(Idea $idea)
    {
        $user = auth()->user();                     // ingelogde “user”

        if ($idea->user_id !== $user->id) {
            return response()->json(['message' => 'Geen toegang tot dit idee.'], 403);
        }

        /* 1. created_posts veld bijwerken */
        $user->created_posts = array_values(
            array_diff($user->created_posts ?? [], [$idea->id])
        );
        $user->save();

        /* 2. pinned_ideas op het merk bijwerken, indien nodig */
        $brand = $idea->brand;
        if ($brand && is_array($brand->pinned_ideas)) {
            $brand->pinned_ideas = array_values(
                array_diff($brand->pinned_ideas, [$idea->id])
            );
            $brand->save();
        }

        /* 3. finally delete */
        $idea->delete();

        return response()->json(['message' => 'Idee succesvol verwijderd.']);
    }
    public function feed()
    {
        return \App\Models\Idea::with([
            'brand:id,slug,title,logo_path',
            'user:id,username',            // 👈 username ophalen
        ])
            ->orderByDesc('created_at')
            ->limit(500)
            ->get([
                'id',
                'brand_id',
                'user_id',
                'title',
                'description',
                'status',
                'likes',
                'category',
                'dislikes',
                'is_pinned',
                'created_at',
            ]);
    }
    public function destroy(Idea $idea)
    {
        $owner = auth('brand_owner')->user();

        // 1. Check of deze brand-owner het merk bezit
        if (!$owner || $idea->brand->brand_owner_id !== $owner->id) {
            return response()->json(['message' => 'Geen toegang tot dit idee.'], 403);
        }

        // 2. User-relaties schonen (notifications, created_posts, pinned)
        $user = $idea->user;
        if ($user) {
            // created_posts bijwerken
            $user->created_posts = array_values(
                array_diff($user->created_posts ?? [], [$idea->id])
            );
            $user->save();
        }

        // 3. pinned_ideas op het merk bijwerken
        $brand = $idea->brand;
        if ($brand && is_array($brand->pinned_ideas)) {
            $brand->pinned_ideas = array_values(
                array_diff($brand->pinned_ideas, [$idea->id])
            );
            $brand->save();
        }

        // 4. Idee verwijderen
        $idea->delete();

        return response()->json(['message' => 'Idee succesvol verwijderd.']);
    }
    public function report(Request $request, Idea $idea)
    {
        $request->validate(['reason' => 'nullable|string|max:255']);
        $user = $request->user();

        // Dubbele melding voorkomen
        if (
            IdeaReport::where('idea_id', $idea->id)
                ->where('user_id', $user->id)->exists()
        ) {
            return response()->json(['message' => 'Je hebt dit idee al gemeld.'], 409);
        }

        IdeaReport::create([
            'idea_id' => $idea->id,
            'user_id' => $user->id,
            'reason' => $request->reason,
        ]);

        /* ── Notificatie voor de brand-owner ───────────── */
        return response()->json(['message' => 'Rapport succesvol verzonden.']);
    }

    public function showPublic(\App\Models\Idea $idea)
    {
        // Optioneel: alleen tonen als het bijbehorende merk publiek/accepted is
        $idea->loadMissing(['brand:id,slug,title,accepted', 'user:id,username']);

        if (!$idea->brand || !$idea->brand->accepted) {
            // Laat het voelen als 'niet gevonden' i.p.v. toegang geweigerd
            abort(404, 'Idea not found');
        }

        // Eventueel extra velden verbergen
        return response()->json([
            'id' => $idea->id,
            'brand_id' => $idea->brand_id,
            'user' => $idea->user ? [
                'id' => $idea->user->id,
                'username' => $idea->user->username,
            ] : null,
            'title' => $idea->title,
            'description' => $idea->description,
            'likes' => (int) $idea->likes,
            'dislikes' => (int) $idea->dislikes,
            'is_pinned' => (bool) $idea->is_pinned,
            'status' => $idea->status,
            'brand' => [
                'id' => $idea->brand->id,
                'slug' => $idea->brand->slug,
                'title' => $idea->brand->title,
                'accepted' => (bool) $idea->brand->accepted,
            ],
            'created_at' => $idea->created_at,
            'updated_at' => $idea->updated_at,
        ]);
    }



}
