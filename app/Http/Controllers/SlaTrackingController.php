<?php

namespace App\Http\Controllers;

use App\Models\SlaTracking;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SlaTrackingController extends Controller
{
    // Index - list all SLA tracking records
    public function index()
    {
        $slaTrackings = SlaTracking::with('ticket')->get();

        return Inertia::render('SlaTracking/Index', [
            'slaTrackings' => $slaTrackings
        ]);
    }

    // Create - show form
    public function create()
    {
        $tickets = Ticket::all(); // select ticket for tracking
        return Inertia::render('SlaTracking/Create', [
            'tickets' => $tickets
        ]);
    }

    // Store - save new SLA tracking
    public function store(Request $request)
    {
        $data = $request->validate([
            'ticket_id' => 'required|exists:tickets,id|unique:sla_tracking,ticket_id',
            'first_response_due' => 'nullable|date',
            'first_response_actual' => 'nullable|date',
            'resolution_due' => 'nullable|date',
            'resolution_actual' => 'nullable|date',
            'is_breached' => 'boolean',
            'breach_reason' => 'nullable|string',
        ]);

        SlaTracking::create($data);

        return redirect()->route('sla-tracking.index')->with('success', 'SLA Tracking created successfully.');
    }

    // Edit - show form for editing
    public function edit(SlaTracking $slaTracking)
    {
        $tickets = Ticket::all();
        return Inertia::render('SlaTracking/Edit', [
            'slaTracking' => $slaTracking,
            'tickets' => $tickets
        ]);
    }

    // Update - save changes
    public function update(Request $request, SlaTracking $slaTracking)
    {
        $data = $request->validate([
            'ticket_id' => 'required|exists:tickets,id|unique:sla_tracking,ticket_id,' . $slaTracking->id,
            'first_response_due' => 'nullable|date',
            'first_response_actual' => 'nullable|date',
            'resolution_due' => 'nullable|date',
            'resolution_actual' => 'nullable|date',
            'is_breached' => 'boolean',
            'breach_reason' => 'nullable|string',
        ]);

        $slaTracking->update($data);

        return redirect()->route('sla-tracking.index')->with('success', 'SLA Tracking updated successfully.');
    }

    // Destroy - delete record
    public function destroy(SlaTracking $slaTracking)
    {
        $slaTracking->delete();
        return redirect()->route('sla-tracking.index')->with('success', 'SLA Tracking deleted successfully.');
    }
}