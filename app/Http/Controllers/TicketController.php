<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with(['client', 'assignedTo', 'createdBy'])
        ->withCount('comments') // This adds a comments_count attribute
            ->latest()
            ->paginate(10);

        return Inertia::render('Tickets/Index', [
            'tickets' => $tickets
        ]);
    }

    public function create()
    {
        $clients = Client::all();
        $users = User::all();

        return Inertia::render('Tickets/Create', [
            'clients' => $clients,
            'users' => $users
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'ticket_type' => 'required|in:support_issue,feature_request,bug,billing,deployment,other',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,critical',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'created_by_user_id' => 'required|exists:users,id',
        ]);

        // 1️⃣ Create the ticket
        $ticket = Ticket::create($validated);

        // 2️⃣ Log initial creation in history
        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'changed_field' => 'initial_creation',
            'old_value' => null,
            'new_value' => 'Ticket created',
            'changed_by_user_id' => $validated['created_by_user_id'],
        ]);

        return redirect()->route('tickets.index')->with('success', 'Ticket created successfully and history logged!');
    }

    public function edit(Ticket $ticket)
    {
        $clients = Client::all();
        $users = User::all();

        return Inertia::render('Tickets/Edit', [
            'ticket' => $ticket->load(['client','assignedTo']),
            'clients' => $clients,
            'users' => $users
        ]);
    }

    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:new,in_progress,waiting_for_client,resolved,closed,rejected',
            'priority' => 'nullable|in:low,medium,high,critical',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        // Save original values before update
        $original = $ticket->only(['status','priority','assigned_to_user_id','due_date']);

        // Update ticket
        $ticket->update($validated);

        // Log changes in history
        foreach ($ticket->getChanges() as $field => $newValue) {
            if (!in_array($field, ['status','priority','assigned_to_user_id','due_date'])) {
                continue;
            }

            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'changed_field' => $field,
                'old_value' => $original[$field] ?? null,
                'new_value' => $newValue,
                'changed_by_user_id' => Auth::id(), // current logged-in user
            ]);
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket updated successfully and history logged!');
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return redirect()->route('tickets.index')->with('success', 'Ticket deleted successfully!');
    }
    public function show(Ticket $ticket)
{
    return Inertia::render('Tickets/Partials/Comments', [
        'ticket' => $ticket->load(['client', 'assignedTo', 'createdBy', 'histories' => function($query) {
            $query->with('changedBy')->latest();
        }])
    ]);
}
public function comments(Ticket $ticket)
{
    // Load comments for the ticket
    $comments = $ticket->comments()->with('user')->latest()->get();
    
    return Inertia::render('Tickets/Comments', [
        'ticket' => $ticket,
        'comments' => $comments
    ]);
}
}