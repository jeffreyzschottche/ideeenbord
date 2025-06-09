<?php

namespace App\Http\Controllers\Brand;

use App\Models\Brand;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Class BrandController
 *
 * This controller handles all operations related to brand management for brand owners.
 * It includes methods for creating, listing, viewing, updating, and managing brand properties
 * such as verification, acceptance, rating, and associating main questions.
 */
class BrandController extends Controller
{
    /**
     * Store a new brand.
     *
     * Validates input, handles optional logo upload, and stores the brand with default values.
     *
     * @param Request $request The HTTP request containing brand data.
     * @return \Illuminate\Http\JsonResponse The created brand data or validation errors.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'website_url' => 'nullable|url',
            'intro' => 'nullable|string',
            'intro_short' => 'nullable|string|max:160',
            'email' => 'required|email|unique:brands,email',
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'socials' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
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
            'accepted' => false,
            'rating' => 0,
            'has_paid' => false,
            'subscription' => null,
            'brand_owner_id' => null,
            'likes' => 0,
            'dislikes' => 0,
            'quizzes' => [],
            'giveaways' => [],
            'main_question_id' => null,
            'ideas' => [],
            'pinned_ideas' => [],
        ]);

        return response()->json(['brand' => $brand], 201);
    }

    /**
     * Get a list of brands with optional filters for verification and acceptance.
     *
     * @param Request $request The HTTP request containing optional filters.
     * @return \Illuminate\Support\Collection A collection of brand IDs and titles.
     */
    public function index(Request $request)
    {
        $query = Brand::query();

        if ($request->has('verified')) {
            $query->where('verified', (bool) $request->input('verified'));
        }

        if ($request->has('accepted')) {
            $query->where('accepted', (bool) $request->input('accepted'));
        }

        return $query->get(['id', 'title']);
    }

    /**
     * Retrieve all brands that are pending acceptance.
     *
     * @return \Illuminate\Database\Eloquent\Collection The collection of pending brands.
     */
    public function pending()
    {
        return Brand::where('accepted', false)->get();
    }

    /**
     * Accept a brand.
     *
     * @param Brand $brand The brand to accept.
     * @return \Illuminate\Http\JsonResponse A confirmation message.
     */
    public function accept(Brand $brand)
    {
        $brand->accepted = true;
        $brand->save();

        return response()->json(['message' => 'Brand geaccepteerd']);
    }

    /**
     * Show a brand by its slug, including the related main question.
     *
     * @param string $slug The slug of the brand.
     * @return \Illuminate\Http\JsonResponse The brand data.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If brand not found.
     */
    public function show($slug)
    {
        $brand = Brand::with('mainQuestion')->where('slug', $slug)->firstOrFail();

        return response()->json($brand);
    }

    /**
     * Rate a brand.
     *
     * Validates the rating, checks if the user already rated, updates the brand's average rating,
     * and stores the brand ID in the user's rating history.
     *
     * @param Request $request The HTTP request with rating data.
     * @param Brand $brand The brand being rated.
     * @return \Illuminate\Http\JsonResponse A confirmation message with the updated average rating.
     */
    public function rate(Request $request, Brand $brand)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:10',
        ]);

        $user = $request->user();

        // Check if the user has already rated this brand
        if (in_array($brand->id, $user->ratings_given ?? [])) {
            return response()->json(['message' => 'Je hebt al een rating gegeven.'], 403);
        }

        // Update brand ratings
        $brand->rating_sum += $request->rating;
        $brand->rating_count += 1;
        $brand->save();

        // Update user's given ratings
        $user->ratings_given = [...($user->ratings_given ?? []), $brand->id];
        $user->save();

        return response()->json([
            'message' => 'Rating succesvol opgeslagen.',
            'average_rating' => round($brand->rating_sum / $brand->rating_count, 1),
        ]);
    }

    /**
     * Set the main question for a brand by an authenticated brand owner.
     *
     * @param Request $request The HTTP request containing main question ID.
     * @param Brand $brand The brand to update.
     * @return \Illuminate\Http\JsonResponse A success message with the updated brand.
     */
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

    /**
     * Update editable fields for a brand.
     *
     * Only the authenticated brand owner is authorized to update their brand.
     *
     * @param Request $request The HTTP request with brand fields to update.
     * @param Brand $brand The brand to update.
     * @return \Illuminate\Http\JsonResponse A confirmation message with updated brand.
     */
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
