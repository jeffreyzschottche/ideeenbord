<?php

namespace App\Http\Controllers;

use App\Models\MainQuestion;
use Illuminate\Http\Request;

class MainQuestionController extends Controller
{
    public function index()
    {
        $questions = MainQuestion::all();
        return response()->json($questions);
    }
    public function show(MainQuestion $mainQuestion)
{
    return response()->json($mainQuestion);
}

}
