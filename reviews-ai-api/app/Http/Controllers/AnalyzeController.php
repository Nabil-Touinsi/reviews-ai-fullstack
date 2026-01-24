<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReviewAnalyzerService;

class AnalyzeController extends Controller
{
    public function analyze(Request $request, ReviewAnalyzerService $analyzer)
    {
        $request->validate([
            'text' => 'required|string|min:3',
        ]);

        return response()->json($analyzer->analyze($request->text));
    }
}
