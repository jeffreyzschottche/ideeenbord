<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use Illuminate\Http\Request;

class CmsPageController extends Controller
{
    public function index()
    {
        // return CmsPage::all();
        return CmsPage::with('fields')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required',
        ]);
        return CmsPage::create($validated);
    }

    public function show($id)
    {
        return CmsPage::with('fields')->findOrFail($id);
    }
}
