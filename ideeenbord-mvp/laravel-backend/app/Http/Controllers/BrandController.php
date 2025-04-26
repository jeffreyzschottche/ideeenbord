<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BrandController extends Controller
{
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
    $brand = Brand::where('slug', $slug)->firstOrFail();
    return response()->json($brand);
}
}
