<?php

namespace App\Http\Controllers\Content;



use App\Http\Controllers\Controller;
use App\Models\CmsPage;

class ContentController extends Controller
{
    public function show(string $slug)
    {
        $page = CmsPage::where('title', $slug)->firstOrFail();

        return response()->json([
            'data' => $page->fields()->select('key', 'value')->get()
        ]);
    }
}
