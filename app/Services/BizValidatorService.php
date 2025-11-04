<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BizValidatorService
{
    public function validateIdea(string $idea): string
    {
        $prompt = "
You are BizValidator, an expert startup analyst.
Analyse this business idea and output a structured validation:
1. Summary
2. Market Fit
3. Target Audience
4. Monetization Options
5. Competitive Advantage
6. Potential Risks
7. Validation Score (0-10)
8. Verdict: Proceed, Pivot, or Drop.

Business Idea: {$idea}
";

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

        $response = Http::timeout(60) // increase timeout
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($url . '?key=' . config('services.gemini.api_key'), [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

        $json = $response->json();

        if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
            return $json['candidates'][0]['content']['parts'][0]['text'];
        }

        if (isset($json['error']['message'])) {
            return 'Gemini API Error: ' . $json['error']['message'];
        }

        return 'Unable to validate idea at this time.';
    }
}
