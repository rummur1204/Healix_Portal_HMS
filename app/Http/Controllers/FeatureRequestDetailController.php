<?php

namespace App\Http\Controllers;

use App\Models\FeatureRequestDetail;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeatureRequestDetailController extends Controller
{
    public function index()
    {
        $features = FeatureRequestDetail::with('ticket')->latest()->get();

        return Inertia::render('FeatureRequests/Index', [
            'features' => $features
        ]);
    }

    public function create()
    {
        $tickets = Ticket::select('id','title')->get();

        return Inertia::render('FeatureRequests/Create', [
            'tickets' => $tickets
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id|unique:feature_request_details,ticket_id',
            'business_value' => 'required|in:low,medium,high',
            'estimated_effort' => 'required|in:small,medium,large,xlarge',
            'target_release' => 'nullable|string',
            'approval_status' => 'required|in:proposed,approved,planned,delivered,rejected',
            'external_link' => 'nullable|string'
        ]);

        FeatureRequestDetail::create($request->all());

        return redirect()->route('feature-requests.index')
            ->with('success','Feature request created successfully');
    }

    public function edit($id)
    {
        $feature = FeatureRequestDetail::findOrFail($id);
        $tickets = Ticket::select('id','title')->get();

        return Inertia::render('FeatureRequests/Edit', [
            'feature' => $feature,
            'tickets' => $tickets
        ]);
    }

    public function update(Request $request, $id)
    {
        $feature = FeatureRequestDetail::findOrFail($id);

        $request->validate([
            'business_value' => 'required|in:low,medium,high',
            'estimated_effort' => 'required|in:small,medium,large,xlarge',
            'target_release' => 'nullable|string',
            'approval_status' => 'required|in:proposed,approved,planned,delivered,rejected',
            'external_link' => 'nullable|string'
        ]);

        $feature->update($request->all());

        return redirect()->route('feature-requests.index')
            ->with('success','Feature updated');
    }

    public function destroy($id)
    {
        FeatureRequestDetail::findOrFail($id)->delete();

        return redirect()->back();
    }
}