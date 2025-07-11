<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Rules\ProfanityFree;

class BrandController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required','string','max:255', new ProfanityFree],
            'category' => 'required|string|max:255',
            'website_url' => ['nullable','url', new ProfanityFree],
            'intro' => ['nullable','string', new ProfanityFree],
            'intro_short' => ['nullable','string','max:160', new ProfanityFree],
            'email' => ['required','email','unique:brands,email', new ProfanityFree],
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'socials' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            if (in_array('profanity-detected', $errors->all())) {
                return response()->json(['message' => 'profanity-detected'], 422);
            }
            return response()->json(['errors' => $errors], 422);
        }

        $data = $validator->validated();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('brands', 'public');
            $data['logo_path'] = $path;
        }

        $brand = Brand::create([
            ...$data,
            'verified' => false,
            'rating' => 0,
            'has_paid' => false,
            'subscription' => null,
            'brand_owner_id' => null,
            'likes' => 0,
            'dislikes' => 0,
            'quizzes' => [],
            'giveaways' => [],
            'main_question' => null,
            'ideas' => [],
            'pinned_ideas' => [],
        ]);

        return response()->json(['brand' => $brand], 201);
    }
    public function index(Request $request)
    {
        if ($request->has('verified')) {
            return Brand::where('verified', (bool)$request->input('verified'))->get(['id', 'title']);
        }

        return Brand::all(['id', 'title']);
    }

public function show($slug)
{
    // $brand = Brand::whereRaw('LOWER(title) = ?', [Str::slug($slug)])->firstOrFail();
    // $brand = Brand::where('slug', $slug)->firstOrFail();
    $brand = Brand::with('mainQuestion')->where('slug', $slug)->firstOrFail();
    return response()->json($brand);
}
public function rate(Request $request, Brand $brand)
{
    $request->validate([
        'rating' => 'required|integer|min:1|max:10',
    ]);

    $user = $request->user();

    // Check of user al gerated heeft
    if (in_array($brand->id, $user->ratings_given ?? [])) {
        return response()->json(['message' => 'Je hebt al een rating gegeven.'], 403);
    }

    // Brand ratings bijwerken
    $brand->rating_sum += $request->rating;
    $brand->rating_count += 1;
    $brand->save();

    // User ratings bijwerken
    $user->ratings_given = [...($user->ratings_given ?? []), $brand->id];
    $user->save();

    return response()->json([
        'message' => 'Rating succesvol opgeslagen.',
        'average_rating' => round($brand->rating_sum / $brand->rating_count, 1),
    ]);
}
public function setMainQuestion(Request $request, Brand $brand)
{
    $user = auth('brand_owner')->user();

    if (!$user || $brand->brand_owner_id !== $user->id) {
        return response()->json(['message' => 'Geen toegang tot dit merk.'], 403);
    }

    $validated = $request->validate([
        'main_question_id' => 'required|exists:main_questions,id',
    ]);

    $brand->main_question_id = $validated['main_question_id'];
    $brand->save();

    return response()->json([
        'message' => 'Vraag gekoppeld aan merk',
        'brand' => $brand->load('mainQuestion'),
    ]);
}
public function update(Request $request, Brand $brand)
{
    $user = auth('brand_owner')->user();

    if (!$user || $brand->brand_owner_id !== $user->id) {
        return response()->json(['message' => 'Geen toegang tot dit merk.'], 403);
    }

    $validated = $request->validate([
        'title' => 'sometimes|string|max:255',
        'category' => 'sometimes|string|max:255',
        'website_url' => 'nullable|url',
        'intro' => 'nullable|string',
        'intro_short' => 'nullable|string|max:160',
        'email' => 'nullable|email',
        'subscription' => 'nullable|string',
        'socials' => 'nullable|array',
    ]);

    $brand->update($validated);

    return response()->json(['message' => 'Merk bijgewerkt.', 'brand' => $brand]);
}





}
