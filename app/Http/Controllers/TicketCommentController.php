<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TicketCommentController extends Controller
{
    /**
     * Display comments for a ticket
     */
    public function index(Ticket $ticket)
    {
        // Load comments with user relationship
        $comments = $ticket->comments()
            ->with('user')
            ->when(!Auth::user()->is_admin, function ($query) {
                // Non-admin users only see non-internal comments
                $query->where('is_internal', false);
            })
            ->latest()
            ->get();

        // Return the comments view
        return Inertia::render('Tickets/Partials/Comments', [
            'ticket' => $ticket->load(['client', 'assignedTo', 'createdBy']),
            'comments' => $comments
        ]);
    }

    /**
     * Store new comment
     */
    public function store(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'comment_text' => 'required|string|max:5000',
            'is_internal' => 'nullable|boolean',
        ]);

        $ticket->comments()->create([
            'user_id' => Auth::id(),
            'comment_text' => $validated['comment_text'],
            'is_internal' => Auth::user()->is_admin
                ? ($validated['is_internal'] ?? false)
                : false
        ]);

        return redirect()->back()->with('success', 'Comment added successfully.');
    }

    /**
     * Update comment
     */
    public function update(Request $request, Ticket $ticket, TicketComment $comment)
    {
        abort_if($comment->ticket_id !== $ticket->id, 404);

        if (!Auth::user()->is_admin && $comment->user_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized.');
        }

        $validated = $request->validate([
            'comment_text' => 'required|string|max:5000'
        ]);

        $comment->update([
            'comment_text' => $validated['comment_text']
        ]);

        return back()->with('success', 'Comment updated successfully.');
    }

    /**
     * Delete comment
     */
    public function destroy(Ticket $ticket, TicketComment $comment)
    {
        abort_if($comment->ticket_id !== $ticket->id, 404);

        if (!Auth::user()->is_admin && $comment->user_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized.');
        }

        $comment->delete();

        return back()->with('success', 'Comment deleted successfully.');
    }
}