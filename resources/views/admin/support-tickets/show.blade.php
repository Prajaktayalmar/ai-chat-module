<!DOCTYPE html>
<html>
<head>
    <title>Support Ticket #{{ $supportTicket->id }}</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6fa;
            color: #172b4d;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 25px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
            margin-bottom: 20px;
        }

        h1 {
            margin-top: 0;
        }

        .row {
            margin-bottom: 20px;
        }

        .label {
            font-weight: bold;
            margin-bottom: 7px;
            display: block;
        }

        .description {
            background: #f8fafc;
            padding: 18px;
            border-radius: 10px;
            line-height: 1.6;
        }

        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d0d5dd;
            border-radius: 8px;
            font-size: 15px;
        }

        button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #2563eb;
        }

        .success {
            background: #dcfce7;
            color: #166534;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

<div class="container">

    <a href="{{ route('admin.support-tickets.index') }}" class="back">
        ← Back to Support Tickets
    </a>

    @if(session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">

        <h1>
            Support Ticket #{{ $supportTicket->id }}
        </h1>

        <div class="row">
            <span class="label">Customer</span>
            {{ $supportTicket->user?->name ?? 'Unknown User' }}
        </div>

        <div class="row">
            <span class="label">Subject</span>
            {{ $supportTicket->subject }}
        </div>

        <div class="row">
            <span class="label">Description</span>

            <div class="description">
                {{ $supportTicket->description }}
            </div>
        </div>

        <div class="row">
            <span class="label">Created At</span>
            {{ $supportTicket->created_at->format('d M Y, h:i A') }}
        </div>

    </div>

    <div class="card">

        <h2>Update Ticket</h2>

        <form
            method="POST"
            action="{{ route('admin.support-tickets.update', $supportTicket) }}"
        >

            @csrf
            @method('PUT')

            <div class="row">
                <label class="label">Status</label>

                <select name="status">

                    <option value="open"
                        {{ $supportTicket->status === 'open' ? 'selected' : '' }}>
                        Open
                    </option>

                    <option value="in_progress"
                        {{ $supportTicket->status === 'in_progress' ? 'selected' : '' }}>
                        In Progress
                    </option>

                    <option value="resolved"
                        {{ $supportTicket->status === 'resolved' ? 'selected' : '' }}>
                        Resolved
                    </option>

                    <option value="closed"
                        {{ $supportTicket->status === 'closed' ? 'selected' : '' }}>
                        Closed
                    </option>

                </select>
            </div>

            <div class="row">
                <label class="label">Priority</label>

                <select name="priority">

                    <option value="low"
                        {{ $supportTicket->priority === 'low' ? 'selected' : '' }}>
                        Low
                    </option>

                    <option value="medium"
                        {{ $supportTicket->priority === 'medium' ? 'selected' : '' }}>
                        Medium
                    </option>

                    <option value="high"
                        {{ $supportTicket->priority === 'high' ? 'selected' : '' }}>
                        High
                    </option>

                </select>
            </div>

            <button type="submit">
                Update Ticket
            </button>

        </form>

    </div>

</div>

</body>
</html>
