<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use Illuminate\Http\Request;
use App\Mail\IdeaStatusChangedMail;
use Illuminate\Support\Facades\Mail;

class IdeaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $user = $request->user();

        // Check: Heeft user al 5 ideeën geplaatst voor deze brand?
        $ideaCount = Idea::where('brand_id', $request->brand_id)
                         ->where('user_id', $user->id)
                         ->count();

        if ($ideaCount >= 5) {
            return response()->json(['message' => 'Je mag maximaal 5 ideeën per merk plaatsen.'], 403);
        }

        $idea = Idea::create([
            'brand_id' => $request->brand_id,
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
        ]);
        $created = $user->created_posts ?? [];
        $created[] = $idea->id;
        $user->created_posts = $created;
        $user->save();

        return response()->json(['message' => 'Idee succesvol geplaatst', 'idea' => $idea]);
    }

    public function index($brandId)
    {
        $ideas = Idea::where('brand_id', $brandId)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($ideas);
    }

    public function like(Idea $idea, Request $request)
    {
        $user = $request->user();

        // Als al geliked is: error
        if (in_array($idea->id, $user->liked_posts ?? [])) {
            return response()->json(['message' => 'Je hebt dit idee al geliked.'], 403);
        }

        // Als gedisliked was: dislike ongedaan maken
        if (in_array($idea->id, $user->disliked_posts ?? [])) {
            $idea->decrement('dislikes');
            $user->disliked_posts = array_diff($user->disliked_posts, [$idea->id]);
        }

        // Like toevoegen
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
    public function update(Request $request, Idea $idea)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:rejected,in_progress,completed,pending',
        ]);
    
        $oldStatus = $idea->status;
    
        // Status updaten
        $idea->status = $validated['status'];
        $idea->save();
    
        // Als status is veranderd, verstuur mail en sla notificatie op
        if ($idea->status !== $oldStatus) {
            $user = $idea->user;
    
            // Notificatie
            $notifications = $user->notifications ?? [];
            $notifications[] = [
                'type' => 'idea_status',
                'idea_id' => $idea->id,
                'message' => "🔄 De status van je idee '{$idea->title}' is gewijzigd naar '{$idea->status}'.",
                'timestamp' => now(),
            ];
            $user->notifications = $notifications;
            $user->save();
    
            // E-mail verzenden (alleen als e-mailadres bestaat)
            if ($user->email) {
                Mail::to($user->email)->send(new IdeaStatusChangedMail($idea));
            }
        }
    
        return response()->json(['message' => 'Status succesvol aangepast.']);
    }
    
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

    // Update het idee als niet gepind
    $idea->is_pinned = false;
    $idea->save();

    // Verwijder uit pinned_ideas
    $pinnedIdeas = $brand->pinned_ideas ?? [];
    $pinnedIdeas = array_filter($pinnedIdeas, fn($id) => $id != $idea->id);
    $brand->pinned_ideas = array_values($pinnedIdeas);
    $brand->save();

    return response()->json(['message' => 'Idee succesvol ontpind.']);
}
public function getMultipleByIds(Request $request)
{
    $ids = $request->query('ids');

    if (!is_array($ids)) {
        $ids = explode(',', $ids);
    }

    $ideas = \App\Models\Idea::with('brand')->whereIn('id', $ids)->get();

    return response()->json($ideas);
}
public function getIdeasByUser($username)
{
    $user = \App\Models\User::where('username', $username)->firstOrFail();

    $ideas = \App\Models\Idea::with('brand')
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($ideas);
}




}
