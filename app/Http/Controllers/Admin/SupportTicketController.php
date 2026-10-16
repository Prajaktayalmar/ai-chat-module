<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::with('user')
            ->latest()
            ->get();

        return view('admin.support-tickets.index', compact('tickets'));
    }

    public function show(SupportTicket $supportTicket)
    {
        $supportTicket->load(['user', 'conversation']);

        return view('admin.support-tickets.show', compact('supportTicket'));
    }

    public function update(Request $request, SupportTicket $supportTicket)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'priority' => 'required|in:low,medium,high',
        ]);

        $supportTicket->update([
            'status' => $request->status,
            'priority' => $request->priority,
        ]);

        return redirect()
            ->route('admin.support-tickets.show', $supportTicket)
            ->with('success', 'Support ticket updated successfully.');
    }

    public function destroy(SupportTicket $supportTicket)
    {
        $supportTicket->delete();

        return redirect()
            ->route('admin.support-tickets.index')
            ->with('success', 'Support ticket deleted successfully.');
    }
}
