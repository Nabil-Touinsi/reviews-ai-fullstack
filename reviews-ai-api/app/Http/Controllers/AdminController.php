<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Review;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    /**
     * Statistiques + historique admin
     * Route protégée (admin uniquement)
     */
    public function stats(): JsonResponse
    {
        $usersCount = User::count();
        $reviewsCount = Review::count();

        $recentReviews = Review::query()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get([
                'id',
                'content',
                'sentiment',
                'score',
                'topics',
                'keywords_detected',
                'created_at'
            ])
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'content' => mb_strlen($r->content) > 120
                        ? mb_substr($r->content, 0, 120) . '…'
                        : $r->content,
                    'sentiment' => $r->sentiment,
                    'score' => $r->score,
                    'topics' => $r->topics ?? [],
                    'keywords_detected' => $r->keywords_detected ?? [],
                    'created_at' => $r->created_at,
                ];
            });

        return response()->json([
            'message' => 'OK',
            'users_count' => $usersCount,
            'reviews_count' => $reviewsCount,
            'recent_reviews' => $recentReviews,
        ]);
    }
}
