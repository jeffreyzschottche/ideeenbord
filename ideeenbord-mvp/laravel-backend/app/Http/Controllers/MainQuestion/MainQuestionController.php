<?php

namespace App\Http\Controllers\MainQuestion;

use App\Models\MainQuestion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


/**
 * Class MainQuestionController
 *
 * This controller provides access to main questions that can be associated
 * with brands. It supports listing all main questions and retrieving a specific one.
 */
class MainQuestionController extends Controller
{
    /**
     * Retrieve all main questions.
     *
     * @return \Illuminate\Http\JsonResponse JSON response containing all main questions.
     */
    public function index()
    {
        $questions = MainQuestion::all();
        return response()->json($questions);
    }
    /**
     * Retrieve a specific main question by model binding.
     *
     * @param MainQuestion $mainQuestion The main question instance retrieved by route-model binding.
     * @return \Illuminate\Http\JsonResponse JSON response containing the main question data.
     */
    public function show(MainQuestion $mainQuestion)
    {
        return response()->json($mainQuestion);
    }

}
