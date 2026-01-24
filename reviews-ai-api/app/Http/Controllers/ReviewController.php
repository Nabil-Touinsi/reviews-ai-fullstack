<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use App\Services\ReviewAnalyzerService;

class ReviewController extends Controller
{
    // GET /api/reviews
    public function index(Request $request)
    {
        return response()->json(
            $request->user()
                ->reviews()
                ->latest()
                ->get()
        );
    }

    // POST /api/reviews
    public function store(Request $request, ReviewAnalyzerService $analyzer)
    {
        $request->validate([
            'content' => 'required|string|min:3',
        ]);

        // Analyse IA
        $analysis = $analyzer->analyze($request->content);

        // Création review
        $review = Review::create([
            'user_id'   => $request->user()->id,
            'content'   => $request->content,
            'sentiment' => $analysis['sentiment'],
            'score'     => $analysis['score'],
            'topics'    => $analysis['topics'],
        ]);

        return response()->json($review, 201);
    }
}
