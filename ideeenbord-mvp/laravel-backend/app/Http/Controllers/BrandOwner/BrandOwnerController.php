<?php

namespace App\Http\Controllers\BrandOwner;

use App\Models\Brand;
use App\Models\BrandOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

/**
 * Class BrandOwnerController
 *
 * This controller manages actions related to brand owners,
 * including claiming a brand and retrieving unverified brand owner applications.
 */
class BrandOwnerController extends Controller
{
    /**
     * Store a new brand owner and link to the selected brand.
     *
     * Validates the claim request, creates a new brand owner marked as unverified,
     * and links the owner to the specified brand.
     *
     * @param Request $request The HTTP request containing brand owner claim data.
     * @return \Illuminate\Http\JsonResponse A success message with status code 201.
     *
     * @throws \Illuminate\Validation\ValidationException If the validation fails.
     */
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

        $brand = Brand::find($data['brand_id']);
        $brand->brand_owner_id = $brandOwner->id;
        $brand->save();

        return response()->json([
            'message' => 'Claim succesvol ontvangen. Je aanvraag wordt beoordeeld door een administrator.'
        ], 201);
    }

    /**
     * Retrieve all brand owners who have not yet been verified.
     *
     * @return \Illuminate\Database\Eloquent\Collection The list of unverified brand owners.
     */
    public function index()
    {
        return BrandOwner::where('verified_owner', false)->get();
    }
}
