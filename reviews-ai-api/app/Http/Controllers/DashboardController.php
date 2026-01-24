<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $userId = $request->user()->id;

        $total = Review::where('user_id', $userId)->count();

        $avgScore = Review::where('user_id', $userId)->avg('score');
        $avgScore = $avgScore ? round($avgScore, 1) : null;

        $bySentiment = Review::where('user_id', $userId)
            ->selectRaw('sentiment, COUNT(*) as count')
            ->groupBy('sentiment')
            ->pluck('count', 'sentiment');

        $positive = (int) ($bySentiment['positive'] ?? 0);
        $neutral  = (int) ($bySentiment['neutral'] ?? 0);
        $negative = (int) ($bySentiment['negative'] ?? 0);

        $percent = function (int $n) use ($total) {
            return $total > 0 ? round(($n / $total) * 100, 1) : 0;
        };

        // Top topics (SQLite compatible) : on lit les topics en PHP
        $topicsCount = [];
        $topicsLists = Review::where('user_id', $userId)->pluck('topics'); // cast array dans Review model
        foreach ($topicsLists as $topics) {
            if (!is_array($topics)) continue;
            foreach ($topics as $t) {
                $topicsCount[$t] = ($topicsCount[$t] ?? 0) + 1;
            }
        }
        arsort($topicsCount);
        $topTopics = [];
        foreach (array_slice($topicsCount, 0, 5, true) as $topic => $count) {
            $topTopics[] = ['topic' => $topic, 'count' => $count];
        }

        $latest = Review::where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get(['id', 'content', 'sentiment', 'score', 'topics', 'created_at']);

        return response()->json([
            'total_reviews' => $total,
            'avg_score' => $avgScore,
            'sentiments' => [
                'positive' => ['count' => $positive, 'percent' => $percent($positive)],
                'neutral'  => ['count' => $neutral,  'percent' => $percent($neutral)],
                'negative' => ['count' => $negative, 'percent' => $percent($negative)],
            ],
            'top_topics' => $topTopics,
            'latest_reviews' => $latest,
        ]);
    }
}
