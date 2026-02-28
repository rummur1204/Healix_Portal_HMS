<?php

namespace App\Http\Controllers;

use App\Models\TicketAttachment;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TicketAttachmentController extends Controller
{
    public function index(Request $request)
    {
        $query = TicketAttachment::with(['ticket', 'uploadedBy']);

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $query->where('file_name', 'like', '%' . $request->search . '%');
        }

        // Sorting
        $sortField = $request->get('sort_field', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        if (in_array($sortField, ['id', 'file_name', 'created_at'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        $attachments = $query->paginate(10)->withQueryString();

        return Inertia::render('TicketAttachments/Index', [
            'attachments' => $attachments,
            'filters' => $request->only(['search', 'sort_field', 'sort_direction'])
        ]);
    }

    public function create()
    {
        $tickets = Ticket::select('id', 'ticket_number')
            ->orderBy('ticket_number')
            ->get();

        return Inertia::render('TicketAttachments/Create', [
            'tickets' => $tickets
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'file' => 'required|file|max:10240', // 10MB
        ]);

        try {
            $file = $request->file('file');
            $path = $file->store('ticket_attachments', 'public');

            TicketAttachment::create([
                'ticket_id' => $request->ticket_id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'upload_by_user_id' => auth()->id(),
            ]);

            return redirect()->route('ticket-attachments.index')
                ->with('success', 'Attachment uploaded successfully.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to upload attachment: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit(TicketAttachment $ticketAttachment)
    {
        $tickets = Ticket::select('id', 'ticket_number')
            ->orderBy('ticket_number')
            ->get();

        return Inertia::render('TicketAttachments/Edit', [
            'attachment' => $ticketAttachment,
            'tickets' => $tickets
        ]);
    }

    public function update(Request $request, TicketAttachment $ticketAttachment)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
        ]);

        try {
            $ticketAttachment->update([
                'ticket_id' => $request->ticket_id,
            ]);

            return redirect()->route('ticket-attachments.index')
                ->with('success', 'Attachment updated successfully.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update attachment: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(TicketAttachment $ticketAttachment)
    {
        try {
            // Delete the file from storage if it exists
            if ($ticketAttachment->file_path && Storage::disk('public')->exists($ticketAttachment->file_path)) {
                Storage::disk('public')->delete($ticketAttachment->file_path);
            }
            
            // Delete the database record
            $ticketAttachment->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Attachment deleted successfully.'
                ]);
            }

            return redirect()->back()->with('success', 'Attachment deleted successfully.');
            
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete attachment: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to delete attachment: ' . $e->getMessage());
        }
    }
}