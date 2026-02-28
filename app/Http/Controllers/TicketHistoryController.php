<?php
namespace App\Http\Controllers;

use App\Models\TicketHistory;
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

        return Inertia::render('Tickets/History', [
            'history' => $history
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