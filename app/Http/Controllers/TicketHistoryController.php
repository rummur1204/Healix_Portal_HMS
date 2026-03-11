<?php

namespace App\Http\Controllers;

use App\Models\TicketHistory;
use App\Models\User; // ✅ import User here
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class TicketHistoryController extends Controller
{
    /**
     * Show all ticket history (Inertia page)
     */
    public function indexAll(Request $request)
    {
        $history = TicketHistory::with(['ticket', 'changedBy:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15))
            ->withQueryString();

        // Pass all users for ID -> name mapping
        $users = User::select('id', 'name')->get();

        return Inertia::render('Tickets/History', [
            'history' => $history,
            'users' => $users
        ]);
    }

    /**
     * Delete a history entry
     */
    public function destroy($id)
    {
        $history = TicketHistory::findOrFail($id);
        $history->delete();

        return redirect()->back()->with('success', 'History entry deleted successfully.');
    }
}