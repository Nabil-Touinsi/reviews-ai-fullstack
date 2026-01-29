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
            'keywords_detected' => $analysis['keywords_detected'] ?? [],
        ]);

        return response()->json($review, 201);
    }

    // GET /api/reviews/{id}
public function show(Request $request, Review $review)
{
    $this->authorizeReviewAccess($request, $review);
    return response()->json($review);
}

// PUT /api/reviews/{id}
public function update(Request $request, Review $review, ReviewAnalyzerService $analyzer)
{
    $this->authorizeReviewAccess($request, $review);

    $request->validate([
        'content' => 'required|string|min:3',
    ]);

    // Re-analyse IA (on garde sentiment/score/topics cohérents)
    $analysis = $analyzer->analyze($request->content);

    $review->update([
        'content'   => $request->content,
        'sentiment' => $analysis['sentiment'],
        'score'     => $analysis['score'],
        'topics'    => $analysis['topics'],
        'keywords_detected' => $analysis['keywords_detected'] ?? [],
    ]);

    return response()->json($review);
}

// DELETE /api/reviews/{id}
public function destroy(Request $request, Review $review)
{
    $this->authorizeReviewAccess($request, $review);

    $review->delete();
    return response()->json(null, 204);
}

/**
 * Accès autorisé si :
 * - propriétaire de l'avis
 * - ou admin
 */
private function authorizeReviewAccess(Request $request, Review $review): void
{
    $user = $request->user();

    $isOwner = (int) $review->user_id === (int) $user->id;
    $isAdmin = ($user->role ?? null) === 'admin';

    if (! $isOwner && ! $isAdmin) {
        abort(403, 'Forbidden');
    }
}

}
