<?php

namespace App\Services;

use App\Models\KnowledgeBase;
use Illuminate\Support\Facades\Http;

class AIService
{
    public function getResponse(string $message, array $history = []): array
    {
        $startTime = microtime(true);
        $message = trim($message);
        $lowerMessage = strtolower($message);

        /*
        |--------------------------------------------------------------------------
        | Refund reason detection
        |--------------------------------------------------------------------------
        */
        if (
            str_contains($lowerMessage, 'damaged') ||
            str_contains($lowerMessage, 'broken') ||
            str_contains($lowerMessage, 'defective') ||
            str_contains($lowerMessage, 'wrong product') ||
            str_contains($lowerMessage, 'wrong item') ||
            str_contains($lowerMessage, 'not working')
        ) {
            $orderNumber = $this->getLastOrderNumber($history);

            if ($orderNumber) {
                return [
                    'success' => true,
                    'response' => "Thank you for providing the reason. Your refund request for order {$orderNumber} has been recorded.\n\nReason: {$message}\n\nOur support team will verify the order and process your request according to the return policy.",
                    'prompt_tokens' => 0,
                    'completion_tokens' => 0,
                    'total_tokens' => 0,
                    'response_time' => round(microtime(true) - $startTime, 2),
                ];
            }

            return [
                'success' => true,
                'response' => 'Thank you for providing the reason. Could you please provide your order number?',
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
                'response_time' => round(microtime(true) - $startTime, 2),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Order number detection
        |--------------------------------------------------------------------------
        */
        if (preg_match('/\b(?:ORD|AD)[-\s]?[A-Z0-9]+\b/i', $message, $matches)) {
            $orderNumber = strtoupper(
                str_replace([' ', '-'], '', $matches[0])
            );

            return [
                'success' => true,
                'response' => "Thank you for providing your order number, {$orderNumber}. Our support team can verify it to assist you with your refund request.\n\nCould you please share the reason for the refund?",
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
                'response_time' => round(microtime(true) - $startTime, 2),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Knowledge Base
        |--------------------------------------------------------------------------
        */
        $knowledge = $this->findKnowledgeBase($message);

        if ($knowledge) {
            return [
                'success' => true,
                'response' => $knowledge->answer,
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
                'response_time' => round(microtime(true) - $startTime, 2),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Gemini
        |--------------------------------------------------------------------------
        */
        $contents = [];

        foreach ($history as $item) {
            if (!empty($item['user_message'])) {
                $contents[] = [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => $item['user_message']
                        ]
                    ]
                ];
            }

            if (!empty($item['ai_response'])) {
                $contents[] = [
                    'role' => 'model',
                    'parts' => [
                        [
                            'text' => $item['ai_response']
                        ]
                    ]
                ];
            }
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [
                [
                    'text' => $message
                ]
            ]
        ];

        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return [
                'success' => false,
                'response' => 'Sorry, I am unable to process your request right now.',
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
                'response_time' => round(microtime(true) - $startTime, 2),
            ];
        }

        try {
            $response = Http::timeout(30)->post(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=' . $apiKey,
                [
                    'systemInstruction' => [
                        'parts' => [
                            [
                                'text' => 'You are an AI Support Assistant. Help users clearly and professionally. Ask only one question at a time. Do not invent company policies or order information. If you cannot confidently answer, say human support may be required.'
                            ]
                        ]
                    ],
                    'contents' => $contents
                ]
            );
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'response' => 'Sorry, I am unable to process your request right now.',
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
                'response_time' => round(microtime(true) - $startTime, 2),
            ];
        }

        $responseTime = round(microtime(true) - $startTime, 2);

        if (!$response->successful()) {
            return [
                'success' => false,
                'response' => 'Sorry, I am unable to process your request right now.',
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
                'response_time' => $responseTime,
            ];
        }

        $data = $response->json();

        $reply = $data['candidates'][0]['content']['parts'][0]['text']
            ?? 'Sorry, I could not generate a response.';

        $usage = $data['usageMetadata'] ?? [];

        return [
            'success' => true,
            'response' => $reply,
            'prompt_tokens' => $usage['promptTokenCount'] ?? 0,
            'completion_tokens' => $usage['candidatesTokenCount'] ?? 0,
            'total_tokens' => $usage['totalTokenCount'] ?? 0,
            'response_time' => $responseTime,
        ];
    }

    private function getLastOrderNumber(array $history): ?string
    {
        foreach (array_reverse($history) as $item) {
            $text = ($item['user_message'] ?? '') . ' ' . ($item['ai_response'] ?? '');

            if (preg_match('/\b(?:ORD|AD)[-\s]?[A-Z0-9]+\b/i', $text, $matches)) {
                return strtoupper(
                    str_replace([' ', '-'], '', $matches[0])
                );
            }
        }

        return null;
    }

    private function findKnowledgeBase(string $message): ?KnowledgeBase
    {
        $message = strtolower(trim($message));

        $knowledgeBases = KnowledgeBase::where('is_active', true)->get();

        $bestMatch = null;
        $bestScore = 0;

        foreach ($knowledgeBases as $knowledge) {
            $text = strtolower(
                ($knowledge->title ?? '') . ' ' .
                ($knowledge->question ?? '') . ' ' .
                ($knowledge->keywords ?? '')
            );

            $keywords = array_filter(
                array_map(
                    'trim',
                    preg_split('/[,]+/', strtolower($knowledge->keywords ?? ''))
                )
            );

            $score = 0;

            foreach ($keywords as $keyword) {
                if ($keyword !== '' && str_contains($message, $keyword)) {
                    $score += strlen($keyword) >= 6 ? 3 : 2;
                }
            }

            $words = preg_split('/\s+/', $message);

            foreach ($words as $word) {
                $word = preg_replace('/[^a-z0-9]/', '', $word);

                if (strlen($word) >= 4 && str_contains($text, $word)) {
                    $score++;
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $knowledge;
            }
        }

        return $bestScore >= 2 ? $bestMatch : null;
    }
}
