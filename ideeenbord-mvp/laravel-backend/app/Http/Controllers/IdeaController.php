<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use Illuminate\Http\Request;

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
}
