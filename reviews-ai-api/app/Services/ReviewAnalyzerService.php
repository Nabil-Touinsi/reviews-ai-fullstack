<?php

namespace App\Services;

class ReviewAnalyzerService
{
    private array $positiveWords = [
        'excellent','parfait','super','genial','top','rapide','bon','bonne','nickel','incroyable','formidable','satisfait',
        'great','excellent','amazing','perfect','fast','good','awesome'
    ];

    private array $negativeWords = [
        'nul','mauvais','horrible','lent','decu','déçu','pire','arnaque','catastrophe','probleme','problème','bug',
        'bad','terrible','awful','slow','worst','scam'
    ];

    private array $topicKeywords = [
        'delivery' => ['livraison','delivery','livrer','retard','late','colis','shipping'],
        'price'    => ['prix','cher','chere','chère','price','cost','couteux','coûteux'],
        'quality'  => ['qualite','qualité','quality','materiel','matériel','durable','fragile'],
        'support'  => ['support','service','sav','help','assistance','contact'],
        'app'      => ['app','application','site','website','interface','ux','ui'],
        'refund'   => ['remboursement','refund','retour','return','annulation','cancel'],
    ];

    public function analyze(string $text): array
    {
        $t = mb_strtolower($text);

        // ✅ mots trouvés (explicabilité)
        $matchedPos = $this->findMatches($t, $this->positiveWords);
        $matchedNeg = $this->findMatches($t, $this->negativeWords);

        $pos = count($matchedPos);
        $neg = count($matchedNeg);

        // Sentiment (simple)
        $sentiment = 'neutral';
        if ($pos > $neg) $sentiment = 'positive';
        if ($neg > $pos) $sentiment = 'negative';

        // Score 0..100 (simple + stable)
        $base = 50 + ($pos - $neg) * 10;
        $lenBonus = min(10, intdiv(mb_strlen($t), 50)); // +0..10
        $score = max(0, min(100, $base + $lenBonus));

        // Topics (max 3) + keywords topics détectés
        $topics = [];
        $matchedTopicKeywords = [];

        foreach ($this->topicKeywords as $topic => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($t, $kw)) {
                    $topics[] = $topic;
                    $matchedTopicKeywords[] = $kw; // ✅ indice concret détecté
                    break;
                }
            }
        }

        $topics = array_values(array_unique($topics));
        $topics = array_slice($topics, 0, 3);

        // ✅ keywords_detected : fusion + unique + limite (évite spam)
        $keywordsDetected = array_values(array_unique(array_merge(
            $matchedPos,
            $matchedNeg,
            $matchedTopicKeywords
        )));
        $keywordsDetected = array_slice($keywordsDetected, 0, 12);

        return [
            'sentiment' => $sentiment,
            'score'     => $score,
            'topics'    => $topics,
            'keywords_detected' => $keywordsDetected,
        ];
    }

    private function findMatches(string $text, array $words): array
    {
        $found = [];
        foreach ($words as $w) {
            if (str_contains($text, $w)) {
                $found[] = $w;
            }
        }
        return array_values(array_unique($found));
    }
}
