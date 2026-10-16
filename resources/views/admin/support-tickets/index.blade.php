<!DOCTYPE html>
<html>
<head>
    <title>Support Tickets</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6fa;
            color: #172b4d;
        }

        .container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 25px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        h1 {
            margin: 0;
            font-size: 34px;
        }

        .subtitle {
            margin-top: 8px;
            color: #667085;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            text-align: left;
            padding: 16px;
        }

        td {
            padding: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .badge {
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            display: inline-block;
        }

        .open {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .in_progress {
            background: #fef3c7;
            color: #92400e;
        }

        .resolved {
            background: #dcfce7;
            color: #166534;
        }

        .closed {
            background: #e5e7eb;
            color: #374151;
        }

        .low {
            background: #dcfce7;
            color: #166534;
        }

        .medium {
            background: #fef3c7;
            color: #92400e;
        }

        .high {
            background: #fee2e2;
            color: #b91c1c;
        }

        .view-btn {
            background: #2563eb;
            color: white;
            padding: 8px 14px;
            border-radius: 7px;
            text-decoration: none;
        }

        .empty {
            text-align: center;
            padding: 60px;
            color: #667085;
            font-size: 18px;
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

    <div class="header">
        <div>
            <h1>Support Tickets</h1>
            <div class="subtitle">
                Manage customer support requests created by the AI Assistant.
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">

        @if($tickets->count())

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                @foreach($tickets as $ticket)

                    <tr>
                        <td>#{{ $ticket->id }}</td>

                        <td>
                            {{ $ticket->user?->name ?? 'Unknown User' }}
                        </td>

                        <td>
                            {{ $ticket->subject }}
                        </td>

                        <td>
                            <span class="badge {{ $ticket->status }}">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </td>

                        <td>
                            <span class="badge {{ $ticket->priority }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </td>

                        <td>
                            {{ $ticket->created_at->format('d M Y, h:i A') }}
                        </td>

                        <td>
                            <a
                                href="{{ route('admin.support-tickets.show', $ticket) }}"
                                class="view-btn"
                            >
                                View
                            </a>
                        </td>
                    </tr>

                @endforeach

                </tbody>
            </table>

        @else

            <div class="empty">
                No support tickets found.
            </div>

        @endif

    </div>

</div>

</body>
</html>
