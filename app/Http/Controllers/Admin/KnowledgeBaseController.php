<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeBase;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{
    public function index()
    {
        $knowledgeBases = KnowledgeBase::latest()->paginate(10);

        return view('admin.knowledge-base.index', compact('knowledgeBases'));
    }

    public function create()
    {
        return view('admin.knowledge-base.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'question' => 'nullable|string|max:1000',
            'answer' => 'required|string',
            'category' => 'nullable|string|max:255',
            'keywords' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        KnowledgeBase::create($validated);

        return redirect()
            ->route('admin.knowledge-base.index')
            ->with('success', 'Knowledge base entry added successfully.');
    }

    public function show(KnowledgeBase $knowledgeBase)
    {
        return view('admin.knowledge-base.show', compact('knowledgeBase'));
    }

    public function edit(KnowledgeBase $knowledgeBase)
    {
        return view('admin.knowledge-base.edit', compact('knowledgeBase'));
    }

    public function update(Request $request, KnowledgeBase $knowledgeBase)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'question' => 'nullable|string|max:1000',
            'answer' => 'required|string',
            'category' => 'nullable|string|max:255',
            'keywords' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $knowledgeBase->update($validated);

        return redirect()
            ->route('admin.knowledge-base.index')
            ->with('success', 'Knowledge base entry updated successfully.');
    }

    public function destroy(KnowledgeBase $knowledgeBase)
    {
        $knowledgeBase->delete();

        return redirect()
            ->route('admin.knowledge-base.index')
            ->with('success', 'Knowledge base entry deleted successfully.');
    }
}
