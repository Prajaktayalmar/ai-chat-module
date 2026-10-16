<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\SupportTicket;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function chat(Request $request, AIService $aiService)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $message = trim($request->message);
        $sessionId = session()->getId();

        $history = Conversation::where('user_id', Auth::id())
            ->where('session_id', $sessionId)
            ->latest()
            ->take(10)
            ->get()
            ->reverse()
            ->map(function ($conversation) {
                return [
                    'user_message' => $conversation->user_message,
                    'ai_response' => $conversation->ai_response,
                ];
            })
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | REFUND FLOW
        |--------------------------------------------------------------------------
        */

        $refundOrderNumber = session('refund_order_number');
        $refundPending = session('refund_pending', false);
        $refundReasonPending = session('refund_reason_pending', false);

        $lowerMessage = strtolower($message);

        $refundIntent =
            str_contains($lowerMessage, 'refund') ||
            str_contains($lowerMessage, 'money back');

        /*
        |--------------------------------------------------------------------------
        | Start Refund Flow
        |--------------------------------------------------------------------------
        */

        if ($refundIntent && !$refundPending && !$refundReasonPending) {
            session([
                'refund_pending' => true,
                'refund_reason_pending' => false,
                'refund_order_number' => null,
            ]);

            $reply = "We can certainly help you with that.\n\nCould you please provide your order number?";

            $conversation = Conversation::create([
                'user_id' => Auth::id(),
                'session_id' => $sessionId,
                'user_message' => $message,
                'ai_response' => $reply,
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
                'response_time' => 0,
            ]);

            return response()->json([
                'reply' => $reply,
                'support_ticket_created' => false,
                'ticket_id' => null,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Extract Order Number
        |--------------------------------------------------------------------------
        */

        $orderNumber = $this->extractOrderNumber($message);

        /*
        |--------------------------------------------------------------------------
        | Waiting For Order Number
        |--------------------------------------------------------------------------
        */

        if ($refundPending && !$refundReasonPending) {

            if (!$orderNumber) {
                $reply = "Please provide a valid order number first so I can help you with your refund request.";

                $conversation = Conversation::create([
                    'user_id' => Auth::id(),
                    'session_id' => $sessionId,
                    'user_message' => $message,
                    'ai_response' => $reply,
                    'prompt_tokens' => 0,
                    'completion_tokens' => 0,
                    'total_tokens' => 0,
                    'response_time' => 0,
                ]);

                return response()->json([
                    'reply' => $reply,
                    'support_ticket_created' => false,
                    'ticket_id' => null,
                ]);
            }

            session([
                'refund_order_number' => $orderNumber,
                'refund_pending' => false,
                'refund_reason_pending' => true,
            ]);

            $reply = "Thank you for providing your order number, {$orderNumber}. Our support team can verify it to assist you with your refund request.\n\nCould you please share the reason for the refund?";

            $conversation = Conversation::create([
                'user_id' => Auth::id(),
                'session_id' => $sessionId,
                'user_message' => $message,
                'ai_response' => $reply,
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
                'response_time' => 0,
            ]);

            return response()->json([
                'reply' => $reply,
                'support_ticket_created' => false,
                'ticket_id' => null,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Waiting For Refund Reason
        |--------------------------------------------------------------------------
        */

        if ($refundReasonPending && $refundOrderNumber) {

            $reason = $message;

            $reply = "Thank you for providing the reason. Your refund request for order {$refundOrderNumber} has been recorded.\n\nReason: {$reason}\n\nOur support team will verify the order and process your request according to the return policy.";

            $conversation = Conversation::create([
                'user_id' => Auth::id(),
                'session_id' => $sessionId,
                'user_message' => $message,
                'ai_response' => $reply,
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
                'response_time' => 0,
            ]);

            SupportTicket::create([
                'user_id' => Auth::id(),
                'conversation_id' => $conversation->id,
                'subject' => 'Refund Request - ' . $refundOrderNumber,
                'description' => "Order Number: {$refundOrderNumber}\nReason: {$reason}",
                'status' => 'open',
                'priority' => 'medium',
            ]);

            session()->forget([
                'refund_order_number',
                'refund_pending',
                'refund_reason_pending',
            ]);

            return response()->json([
                'reply' => $reply,
                'support_ticket_created' => true,
                'ticket_id' => $conversation->id,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Normal AI Flow
        |--------------------------------------------------------------------------
        */

        $result = $aiService->getResponse($message, $history);

        if (!$result['success']) {
            return response()->json([
                'reply' => $result['response'],
            ], 500);
        }

        $conversation = Conversation::create([
            'user_id' => Auth::id(),
            'session_id' => $sessionId,
            'user_message' => $message,
            'ai_response' => $result['response'],
            'prompt_tokens' => $result['prompt_tokens'],
            'completion_tokens' => $result['completion_tokens'] ?? 0,
            'total_tokens' => $result['total_tokens'],
            'response_time' => $result['response_time'],
        ]);

        $needsSupport = $this->needsHumanSupport(
            $message,
            $result['response']
        );

        $ticket = null;

        if ($needsSupport) {
            $ticket = SupportTicket::create([
                'user_id' => Auth::id(),
                'conversation_id' => $conversation->id,
                'subject' => 'AI Support Request',
                'description' => $message,
                'status' => 'open',
                'priority' => 'medium',
            ]);
        }

        return response()->json([
            'reply' => $result['response'],
            'support_ticket_created' => $ticket !== null,
            'ticket_id' => $ticket?->id,
        ]);
    }

    private function extractOrderNumber(string $message): ?string
    {
        $patterns = [
            '/\b(?:ORD|ORDER|AD)[A-Z0-9-]*\d[A-Z0-9-]*\b/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                return strtoupper($matches[0]);
            }
        }

        return null;
    }

    private function needsHumanSupport(string $message, string $response): bool
    {
        $keywords = [
            'human support',
            'speak to agent',
            'talk to agent',
            'customer care',
            'customer support',
            'complaint',
            'escalate',
            'manager',
            'representative',
        ];

        $text = strtolower($message . ' ' . $response);

        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }

    public function history()
    {
        $conversations = Conversation::where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json([
            'conversations' => $conversations
        ]);
    }

    public function clearHistory()
    {
        Conversation::where('user_id', Auth::id())->delete();

        session()->forget([
            'refund_order_number',
            'refund_pending',
            'refund_reason_pending',
        ]);

        return response()->json([
            'message' => 'Chat history cleared successfully.'
        ]);
    }
}
