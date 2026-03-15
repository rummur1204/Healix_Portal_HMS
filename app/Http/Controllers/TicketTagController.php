<?php

namespace App\Http\Controllers;

use App\Models\TicketTag;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketTagController extends Controller
{
    /**
     * Display a listing of all tags (standalone)
     */
    public function index()
    {
        $tags = TicketTag::latest()->paginate(10);

        return Inertia::render('TicketTags/Index', [
            'tags' => $tags
        ]);
    }

    /**
     * Show form for creating a new tag
     */
    public function create()
    {
        return Inertia::render('TicketTags/Create');
    }

    /**
     * Store a newly created tag
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:ticket_tags',
            'color' => 'nullable|string|max:7',
            'is_active' => 'sometimes|boolean'
        ]);

        TicketTag::create([
            'name' => $request->name,
            'color' => $request->color ?? '#3B82F6',
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('ticket-tags.index')
            ->with('success', 'Tag created successfully');
    }

    /**
     * Show form for editing a tag
     */
    public function edit(TicketTag $ticketTag)
    {
        return Inertia::render('TicketTags/Edit', [
            'tag' => $ticketTag
        ]);
    }

    /**
     * Update the specified tag
     */
    public function update(Request $request, TicketTag $ticketTag)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:ticket_tags,name,' . $ticketTag->id,
            'color' => 'nullable|string|max:7',
            'is_active' => 'sometimes|boolean'
        ]);

        $ticketTag->update([
            'name' => $request->name,
            'color' => $request->color ?? '#3B82F6',
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('ticket-tags.index')
            ->with('success', 'Tag updated successfully');
    }

    /**
     * Remove the specified tag
     */
    public function destroy(TicketTag $ticketTag)
    {
        // Detach from all tickets first (if many-to-many relationship)
        $ticketTag->tickets()->detach();
        
        $ticketTag->delete();
        
        return redirect()->route('ticket-tags.index')
            ->with('success', 'Tag deleted successfully');
    }

    /**
     * Get tags for a specific ticket
     */
    public function indexForTicket(Ticket $ticket)
    {
        return response()->json([
            'tags' => $ticket->tags
        ]);
    }

    /**
     * Attach tag to ticket
     */
    public function attachToTicket(Request $request, Ticket $ticket)
    {
        $request->validate([
            'tag_id' => 'required|exists:ticket_tags,id'
        ]);

        $ticket->tags()->syncWithoutDetaching([$request->tag_id]);

        return response()->json([
            'message' => 'Tag attached successfully',
            'tags' => $ticket->tags
        ]);
    }

    /**
     * Detach tag from ticket
     */
    public function detachFromTicket(Ticket $ticket, TicketTag $tag)
    {
        $ticket->tags()->detach($tag->id);

        return response()->json([
            'message' => 'Tag detached successfully',
            'tags' => $ticket->tags
        ]);
    }
}