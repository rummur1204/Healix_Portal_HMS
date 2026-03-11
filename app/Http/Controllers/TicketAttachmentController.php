<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TicketAttachmentController extends Controller
{
    /**
     * Display a listing of the attachments for a ticket.
     */
    public function index(Ticket $ticket)
    {
        // Add policy check if you have one
        // $this->authorize('view', $ticket);
        
        $attachments = $ticket->attachments()
            ->with('uploader:id,name')
            ->latest()
            ->get();
            
        // For Inertia, you can return as props or a dedicated page
        return Inertia::render('Tickets/Attachments/Index', [
            'ticket' => $ticket,
            'attachments' => $attachments
        ]);
    }

    /**
     * Store newly uploaded attachments.
     */
    public function store(Request $request, Ticket $ticket)
    {
        // Add policy check if you have one
        // $this->authorize('update', $ticket);

        $request->validate([
            'attachments' => 'required|array',
            'attachments.*' => 'required|file|max:10240', // 10MB max per file
        ]);

        $uploadedFiles = [];

        foreach ($request->file('attachments') as $file) {
            try {
                // Generate a unique filename
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                // Store file in ticket-specific directory
                $path = $file->storeAs(
                    'ticket-attachments/' . $ticket->id, 
                    $filename, 
                    'public'
                );

                // Create database record
                $attachment = $ticket->attachments()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'upload_by_user_id' => Auth::id(),
                ]);

                // Load the uploader relationship
                $attachment->load('uploader:id,name');
                
                $uploadedFiles[] = $attachment;

            } catch (\Exception $e) {
                Log::error('Failed to upload attachment: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Failed to upload file: ' . $file->getClientOriginalName());
            }
        }

        return redirect()->back()->with('success', count($uploadedFiles) . ' file(s) uploaded successfully');
    }

    /**
     * Remove the specified attachment.
     */
    public function destroy(Ticket $ticket, TicketAttachment $attachment)
    {
        // Verify attachment belongs to ticket
        if ($attachment->ticket_id !== $ticket->id) {
            return redirect()->back()->with('error', 'Attachment does not belong to this ticket');
        }

        // Add policy check if you have one
        // $this->authorize('delete', $attachment);

        try {
            // Delete file from storage
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            // Delete record from database
            $attachment->delete();

            return redirect()->back()->with('success', 'File deleted successfully');

        } catch (\Exception $e) {
            Log::error('Failed to delete attachment: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete attachment');
        }
    }

    /**
     * Download the specified attachment.
     */
    public function download(Ticket $ticket, TicketAttachment $attachment)
    {
        // Verify attachment belongs to ticket
        if ($attachment->ticket_id !== $ticket->id) {
            abort(403, 'Attachment does not belong to this ticket');
        }

        // Add policy check if you have one
        // $this->authorize('view', $ticket);

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }
}