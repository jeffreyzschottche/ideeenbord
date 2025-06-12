<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\CmsField;
use Illuminate\Http\Request;

class CmsFieldController extends Controller
{
    public function index($pageId)
    {
        return CmsField::where('page_id', $pageId)->get();
    }

    public function store(Request $request, $pageId)
    {
        $validated = $request->validate([
            'label' => 'required',
            'key' => 'required',
            'type' => 'required|in:text,image,html,link',
            'value' => 'required',
        ]);

        $validated['page_id'] = $pageId;
        return CmsField::create($validated);
    }

    public function update(Request $request, $pageId, $fieldId)
    {
        $field = CmsField::where('page_id', $pageId)->findOrFail($fieldId);

        $validated = $request->validate([
            'label' => 'sometimes',
            'key' => 'sometimes',
            'type' => 'sometimes|in:text,image,html,link',
            'value' => 'sometimes',
        ]);

        $field->update($validated);
        return $field;
    }
    public function destroy($pageId, CmsField $field)
    {
        $field->delete();
        return response()->json(['message' => 'Veld verwijderd']);
    }

}
