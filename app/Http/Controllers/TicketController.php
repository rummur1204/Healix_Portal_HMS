<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class TicketController extends Controller
{
    // List tickets with filters
    public function index(Request $request)
    {
        $query = Ticket::with(['client', 'assignedTo', 'createdBy'])
            ->withCount('comments','attachments');
            
        // Apply search filter
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('ticket_number', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Apply priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply active filter
        if ($request->filled('active')) {
            $query->where('active', $request->active);
        }

        // Apply sorting
        $sortField = $request->get('sort_field', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        if (in_array($sortField, ['id', 'ticket_number', 'title', 'priority', 'status', 'due_date', 'created_at', 'active'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        $tickets = $query->paginate(10)->withQueryString();

        return Inertia::render('Tickets/Index', [
            'tickets' => $tickets,
            'filters' => $request->only(['search', 'priority', 'status', 'active', 'sort_field', 'sort_direction'])
        ]);
    }

    // Show create form
    public function create()
    {
        $clients = Client::all();
        $users = User::all();

        return Inertia::render('Tickets/Create', [
            'clients' => $clients,
            'users' => $users
        ]);
    }

    // Store new ticket
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
        ]);

        // Set default values
        $validated['created_by_user_id'] = Auth::id();
        $validated['status'] = 'new';
        $validated['active'] = true;
        $validated['ticket_number'] = $this->generateTicketNumber();

        $ticket = Ticket::create($validated);

        // Log initial creation
        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'changed_field' => 'initial_creation',
            'old_value' => null,
            'new_value' => 'Ticket created',
            'changed_by_user_id' => Auth::id(),
        ]);

        return redirect()->route('tickets.index')->with('success', 'Ticket created successfully!');
    }

    // Generate unique ticket number
    private function generateTicketNumber()
    {
        $prefix = 'TICKET-';
        $year = date('Y');
        $month = date('m');
        
        $lastTicket = Ticket::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastTicket && preg_match('/-(\d+)$/', $lastTicket->ticket_number, $matches)) {
            $sequence = intval($matches[1]) + 1;
        } else {
            $sequence = 1;
        }
        
        return sprintf('%s%s%s-%04d', $prefix, $year, $month, $sequence);
    }

    // Show edit form
    public function edit(Ticket $ticket)
    {
        $clients = Client::all();
        $users = User::all();

        return Inertia::render('Tickets/Edit', [
            'ticket' => $ticket->load(['client', 'assignedTo']),
            'clients' => $clients,
            'users' => $users
        ]);
    }

    // Update ticket
    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'client_id' => 'sometimes|exists:clients,id',
            'ticket_type' => 'sometimes|in:support_issue,feature_request,bug,billing,deployment,other',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'status' => 'sometimes|in:new,in_progress,waiting_for_client,resolved,closed,rejected',
            'priority' => 'sometimes|in:low,medium,high,critical',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'active' => 'sometimes|boolean',
        ]);

        $original = $ticket->only(array_keys($validated));
        $ticket->update($validated);

        // Log changes
        foreach ($ticket->getChanges() as $field => $newValue) {
            if (in_array($field, ['client_id', 'ticket_type', 'title', 'description', 'status', 'priority', 'assigned_to_user_id', 'due_date', 'active'])) {
                TicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'changed_field' => $field,
                    'old_value' => $original[$field] ?? null,
                    'new_value' => $newValue,
                    'changed_by_user_id' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket updated successfully!');
    }

    // Delete ticket
    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return redirect()->route('tickets.index')->with('success', 'Ticket deleted successfully!');
    }

    // Show ticket details
    public function show(Ticket $ticket)
{
    $ticket->load([
        'client',
        'assignedTo',
        'createdBy',
        'histories' => function ($query) {
            $query->with('changedBy')->latest();
        }
    ]);

    return Inertia::render('Tickets/Show', [
        'ticket' => $ticket
    ]);
}


public function detail($id)
{
    $ticket = Ticket::with([
         'client',
        'assignedTo',
        'createdBy',
        'comments.user',
        'attachments',
        'history.madeBy'  // ✅ correct relationship
    ])->findOrFail($id);

    // Transform history values for readability
    $ticket->history->transform(function ($entry) {
        switch ($entry->changed_field) {
            case 'assigned_to_user_id':
            case 'created_by_user_id':
                $entry->old_value = $entry->old_value 
                    ? optional(User::find($entry->old_value))->name 
                    : 'Unassigned';
                $entry->new_value = $entry->new_value 
                    ? optional(User::find($entry->new_value))->name 
                    : 'Unassigned';
                break;

            case 'status':
                $statusMap = [
                    'new' => 'New',
                    'in_progress' => 'In Progress',
                    'waiting_for_client' => 'Waiting for Client',
                    'resolved' => 'Resolved',
                    'closed' => 'Closed',
                    'rejected' => 'Rejected'
                ];
                $entry->old_value = $statusMap[$entry->old_value] ?? $entry->old_value;
                $entry->new_value = $statusMap[$entry->new_value] ?? $entry->new_value;
                break;

            case 'priority':
                $priorityMap = [
                    'low' => 'Low',
                    'medium' => 'Medium',
                    'high' => 'High',
                    'critical' => 'Critical'
                ];
                $entry->old_value = $priorityMap[$entry->old_value] ?? $entry->old_value;
                $entry->new_value = $priorityMap[$entry->new_value] ?? $entry->new_value;
                break;
        }

        return $entry;
    });

    return Inertia::render('Tickets/Detail', [
        'ticket' => $ticket
    ]);
}

    // Show ticket comments
    public function comments(Ticket $ticket)
    {
        $comments = $ticket->comments()->with('user')->latest()->paginate(20);
        
        return Inertia::render('Tickets/Comments', [
            'ticket' => $ticket->load(['client', 'assignedTo']),
            'comments' => $comments
        ]);
    }

    // Show ticket attachments
    public function attachments(Ticket $ticket)
    {
        $attachments = $ticket->attachments()->latest()->paginate(20);
        
        return Inertia::render('Tickets/Attachments', [
            'ticket' => $ticket->load(['client', 'assignedTo']),
            'attachments' => $attachments
        ]);
    }

    // Toggle active status
    public function toggleActive(Request $request, Ticket $ticket)
    {
        try {
            $oldValue = $ticket->active;
            
            $ticket->active = !$ticket->active;
            $ticket->save();

            // Log the change
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'changed_field' => 'active',
                'old_value' => $oldValue ? 'Active' : 'Inactive',
                'new_value' => $ticket->active ? 'Active' : 'Inactive',
                'changed_by_user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'active' => $ticket->active,
                'message' => 'Ticket status updated successfully!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Toggle active error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle ticket status: ' . $e->getMessage()
            ], 500);
        }
    }
}