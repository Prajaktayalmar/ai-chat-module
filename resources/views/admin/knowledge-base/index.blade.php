<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowledge Base</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #1f2937;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
        }

        .add-btn {
            background: #2563eb;
            color: white;
            padding: 11px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }

        .success {
            background: #dcfce7;
            color: #166534;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background: #f9fafb;
            font-size: 14px;
        }

        td {
            font-size: 14px;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .active {
            background: #dcfce7;
            color: #166534;
        }

        .inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .action {
            padding: 7px 11px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
        }

        .view {
            background: #e0f2fe;
            color: #0369a1;
        }

        .edit {
            background: #fef3c7;
            color: #92400e;
        }

        .delete {
            background: #fee2e2;
            color: #991b1b;
            border: none;
            cursor: pointer;
        }

        .empty {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        .pagination {
            margin-top: 20px;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">
        <div>
            <h1>Knowledge Base</h1>
            <p>Manage information used by the AI Support Assistant.</p>
        </div>

        <a href="{{ route('admin.knowledge-base.create') }}" class="add-btn">
            + Add Knowledge
        </a>
    </div>

    @if(session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">

        @if($knowledgeBases->count())

            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Question</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                @foreach($knowledgeBases as $knowledgeBase)

                    <tr>

                        <td>
                            <strong>{{ $knowledgeBase->title }}</strong>
                        </td>

                        <td>
                            {{ Str::limit($knowledgeBase->question, 60) }}
                        </td>

                        <td>
                            {{ $knowledgeBase->category ?? '-' }}
                        </td>

                        <td>

                            @if($knowledgeBase->is_active)

                                <span class="badge active">
                                    Active
                                </span>

                            @else

                                <span class="badge inactive">
                                    Inactive
                                </span>

                            @endif

                        </td>

                        <td>

                            <div class="actions">

                                <a
                                    href="{{ route('admin.knowledge-base.show', $knowledgeBase) }}"
                                    class="action view"
                                >
                                    View
                                </a>

                                <a
                                    href="{{ route('admin.knowledge-base.edit', $knowledgeBase) }}"
                                    class="action edit"
                                >
                                    Edit
                                </a>

                                <form
                                    action="{{ route('admin.knowledge-base.destroy', $knowledgeBase) }}"
                                    method="POST"
                                    onsubmit="return confirm('Delete this knowledge?')"
                                >

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="action delete"
                                    >
                                        Delete
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                @endforeach

                </tbody>
            </table>

            <div class="pagination">
                {{ $knowledgeBases->links() }}
            </div>

        @else

            <div class="empty">
                <h3>No knowledge added yet</h3>
                <p>Add your first FAQ or support information.</p>
            </div>

        @endif

    </div>

</div>

</body>
</html>
