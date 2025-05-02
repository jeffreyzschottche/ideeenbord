<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\BrandOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandOwnerController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:brand_owners,email',
            'phone' => 'nullable|string',
            'url' => 'nullable|url',
            'subscription_plan' => 'required|string|in:Brons,Zilver,Goud',
            'password' => 'required|string|min:6',
        ]);

        $brandOwner = BrandOwner::create([
            ...$data,
            'verified_owner' => false,
        ]);

        return response()->json(['owner' => $brandOwner], 201);
    }
    public function index()
        {
            return BrandOwner::where('verified_owner', false)->get();
        }
}
