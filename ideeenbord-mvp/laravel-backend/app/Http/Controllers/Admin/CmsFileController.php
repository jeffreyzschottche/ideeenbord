<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CmsFileController
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:2048',
        ]);

        $path = $request->file('file')->store('images-cms', 'public');

        return ['url' => Storage::url($path)];
    }
}
