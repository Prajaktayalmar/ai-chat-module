<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Support Assistant</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6fa;
            color: #111827;
        }

        .container {
            width: 92%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px 0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h2 {
            margin: 0;
            font-size: 24px;
        }

        .logout {
            background: #dc3545;
            color: white;
            border: none;
            padding: 11px 20px;
            border-radius: 7px;
            cursor: pointer;
            font-size: 16px;
        }

        .clear-btn {
            background: #ffdede;
            color: #111;
            border: none;
            padding: 13px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 12px;
        }

        .chat-box {
            background: #fff;
            border: 1px solid #d9d9d9;
            border-radius: 12px;
            height: 600px;
            padding: 20px;
            overflow-y: auto;
        }

        .message {
            max-width: 80%;
            padding: 18px 20px;
            margin-bottom: 14px;
            border-radius: 18px;
            font-size: 18px;
            line-height: 1.5;
            white-space: pre-wrap;
        }

        .user-message {
            background: #d8e8ff;
            margin-left: auto;
            text-align: right;
        }

        .ai-message {
            background: #d9fbe7;
            margin-right: auto;
        }

        .input-area {
            display: flex;
            gap: 12px;
            margin-top: 18px;
        }

        #message {
            flex: 1;
            padding: 15px;
            border: 2px solid #222;
            border-radius: 10px;
            font-size: 16px;
            outline: none;
        }

        #sendBtn {
            width: 80px;
            border: none;
            border-radius: 10px;
            background: #f0f0f0;
            font-size: 16px;
            cursor: pointer;
        }

        #sendBtn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .ticket-card {
            margin-top: 25px;
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #ddd;
        }

        .ticket-card h3 {
            margin-top: 0;
        }

        .ticket {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .ticket-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 13px;
        }

        .ticket-priority {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            background: #fef3c7;
            color: #92400e;
            font-size: 13px;
            margin-left: 5px;
        }

        .empty-ticket {
            color: #666;
        }

        .loading {
            opacity: 0.7;
        }

        @media (max-width: 700px) {
            .container {
                width: 96%;
            }

            .message {
                max-width: 90%;
                font-size: 16px;
            }

            .chat-box {
                height: 500px;
            }

            .input-area {
                flex-direction: column;
            }

            #sendBtn {
                width: 100%;
                padding: 13px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">
        <h2>Welcome, {{ Auth::user()->name }}</h2>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout">Logout</button>
        </form>
    </div>

    <button type="button" class="clear-btn" id="clearChat">
        Clear Chat
    </button>

    <div class="chat-box" id="chatBox">

        @php
            $conversations = \App\Models\Conversation::where('user_id', Auth::id())
                ->latest()
                ->take(20)
                ->get()
                ->reverse();
        @endphp

        @if($conversations->count() > 0)

            @foreach($conversations as $conversation)

                <div class="message user-message">
                    {{ $conversation->user_message }}
                </div>

                <div class="message ai-message">
                    {{ $conversation->ai_response }}
                </div>

            @endforeach

        @else

            <div class="message user-message">
                Hello, what can you help me with?
            </div>

            <div class="message ai-message">
                Hello! I am an AI Support Assistant. I can help answer questions,
                guide you through troubleshooting steps, and provide information
                about various products, services, or technical issues.

                How can I assist you today?
            </div>

        @endif

    </div>

    <div class="input-area">

        <input
            type="text"
            id="message"
            placeholder="Type your message..."
            autocomplete="off"
        >

        <button type="button" id="sendBtn">
            Send
        </button>

    </div>

    <div class="ticket-card">

        <h3>My Support Tickets</h3>

        @php
            $tickets = \App\Models\SupportTicket::where('user_id', Auth::id())
                ->latest()
                ->get();
        @endphp

        @if($tickets->count())

            @foreach($tickets as $ticket)

                <div class="ticket">

                    <strong>
                        #{{ $ticket->id }} - {{ $ticket->subject }}
                    </strong>

                    <p>
                        {{ $ticket->description }}
                    </p>

                    <span class="ticket-status">
                        {{ ucfirst($ticket->status) }}
                    </span>

                    <span class="ticket-priority">
                        {{ ucfirst($ticket->priority) }}
                    </span>

                    <small style="display:block;margin-top:8px;color:#666;">
                        {{ $ticket->created_at->format('d M Y, h:i A') }}
                    </small>

                </div>

            @endforeach

        @else

            <p class="empty-ticket">
                No support tickets created yet.
            </p>

        @endif

    </div>

</div>

<script>
    const messageInput = document.getElementById('message');
    const sendBtn = document.getElementById('sendBtn');
    const chatBox = document.getElementById('chatBox');
    const clearChat = document.getElementById('clearChat');

    function scrollToBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function addMessage(text, type) {
        const div = document.createElement('div');

        div.className = 'message ' + type;
        div.textContent = text;

        chatBox.appendChild(div);

        scrollToBottom();
    }

    async function sendMessage() {

        const message = messageInput.value.trim();

        if (!message) {
            return;
        }

        addMessage(message, 'user-message');

        messageInput.value = '';

        sendBtn.disabled = true;
        sendBtn.textContent = '...';

        try {

            const response = await fetch('{{ route('chat') }}', {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },

                body: JSON.stringify({
                    message: message
                })
            });

            const data = await response.json();

            if (data.reply) {

                addMessage(data.reply, 'ai-message');

            } else {

                addMessage(
                    'Sorry, I could not generate a response.',
                    'ai-message'
                );
            }

        } catch (error) {

            addMessage(
                'Sorry, I am unable to process your request right now.',
                'ai-message'
            );

        } finally {

            sendBtn.disabled = false;
            sendBtn.textContent = 'Send';

            messageInput.focus();
        }
    }

    sendBtn.addEventListener('click', sendMessage);

    messageInput.addEventListener('keydown', function(event) {

        if (event.key === 'Enter') {
            event.preventDefault();
            sendMessage();
        }

    });

    clearChat.addEventListener('click', async function() {

        if (!confirm('Are you sure you want to clear the chat?')) {
            return;
        }

        try {

            const response = await fetch('{{ route('chat.clear') }}', {
                method: 'DELETE',

                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();

            if (response.ok) {

                chatBox.innerHTML = '';

                addMessage(
                    'Hello! I am an AI Support Assistant. How can I assist you today?',
                    'ai-message'
                );

            }

        } catch (error) {

            alert('Unable to clear chat.');

        }

    });

    scrollToBottom();
</script>

</body>
</html>
