<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AIService;
use App\Models\Conversation;

class ChatController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $result = $this->aiService->getResponse($request->message);

        Conversation::create([
            'session_id' => session()->getId(),
            'user_message' => $request->message,
            'ai_response' => $result['response'],
            'prompt_tokens' => $result['prompt_tokens'],
            'completion_tokens' => $result['completion_tokens'],
            'total_tokens' => $result['total_tokens'],
            'response_time' => $result['response_time'],
        ]);

        return response()->json($result);
    }
}
