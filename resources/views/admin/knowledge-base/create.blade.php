<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Knowledge</title>

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
            max-width: 850px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header {
            margin-bottom: 25px;
        }

        .header h1 {
            margin-bottom: 8px;
        }

        .back {
            color: #2563eb;
            text-decoration: none;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 15px;
            font-family: Arial, sans-serif;
        }

        textarea {
            min-height: 140px;
            resize: vertical;
        }

        .hint {
            margin-top: 5px;
            color: #6b7280;
            font-size: 13px;
        }

        .error {
            color: #dc2626;
            font-size: 13px;
            margin-top: 5px;
        }

        .checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox input {
            width: auto;
        }

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .btn {
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
        }

        .save {
            background: #2563eb;
            color: white;
        }

        .cancel {
            background: #e5e7eb;
            color: #374151;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">

        <a
            href="{{ route('admin.knowledge-base.index') }}"
            class="back"
        >
            ← Back to Knowledge Base
        </a>

        <h1>Add Knowledge</h1>

        <p>
            Add information that the AI Support Assistant can use to answer customer questions.
        </p>

    </div>

    <div class="card">

        <form
            action="{{ route('admin.knowledge-base.store') }}"
            method="POST"
        >

            @csrf

            <div class="form-group">

                <label for="title">
                    Title *
                </label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title') }}"
                    placeholder="Example: Product Return Policy"
                    required
                >

                @error('title')
                    <div class="error">{{ $message }}</div>
                @enderror

            </div>

            <div class="form-group">

                <label for="question">
                    Customer Question
                </label>

                <textarea
                    id="question"
                    name="question"
                    placeholder="Example: Can I return a product?"
                >{{ old('question') }}</textarea>

                <div class="hint">
                    Write the type of question customers may ask.
                </div>

                @error('question')
                    <div class="error">{{ $message }}</div>
                @enderror

            </div>

            <div class="form-group">

                <label for="answer">
                    Answer *
                </label>

                <textarea
                    id="answer"
                    name="answer"
                    placeholder="Write the correct answer the AI should provide..."
                    required
                >{{ old('answer') }}</textarea>

                <div class="hint">
                    The AI Support Assistant will use this information when answering customers.
                </div>

                @error('answer')
                    <div class="error">{{ $message }}</div>
                @enderror

            </div>

            <div class="form-group">

                <label for="category">
                    Category
                </label>

                <input
                    type="text"
                    id="category"
                    name="category"
                    value="{{ old('category') }}"
                    placeholder="Example: Returns, Payments, Products, Technical Support"
                >

                @error('category')
                    <div class="error">{{ $message }}</div>
                @enderror

            </div>

            <div class="form-group">

                <label for="keywords">
                    Keywords
                </label>

                <input
                    type="text"
                    id="keywords"
                    name="keywords"
                    value="{{ old('keywords') }}"
                    placeholder="return, refund, exchange, product return"
                >

                <div class="hint">
                    Separate multiple keywords with commas.
                </div>

                @error('keywords')
                    <div class="error">{{ $message }}</div>
                @enderror

            </div>

            <div class="form-group">

                <label class="checkbox">

                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old('is_active', true) ? 'checked' : '' }}
                    >

                    Active

                </label>

            </div>

            <div class="buttons">

                <button
                    type="submit"
                    class="btn save"
                >
                    Save Knowledge
                </button>

                <a
                    href="{{ route('admin.knowledge-base.index') }}"
                    class="btn cancel"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>
