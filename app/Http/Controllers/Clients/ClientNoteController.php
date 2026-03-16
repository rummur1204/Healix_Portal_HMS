<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientNote;
use App\Models\ClientTimelineEvent;
use Illuminate\Http\Request;

class ClientNoteController extends Controller
{
    public function store(Request $request, Client $client)
    {
        $validated = $request->validate([
            'note_type' => 'required|in:internal,client_visible',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'required|in:low,medium,high,critical',
        ]);

        $note = $client->notes()->create([
            ...$validated,
            'created_by_user_id' => auth()->id(),
        ]);

        // Timeline Log
        ClientTimelineEvent::create([
            'client_id' => $client->id,
            'event_type' => 'note_added',
            'description' => "Note added: {$note->title}",
            'performed_by' => auth()->id(),
        ]);

        return back()->with('success', 'Note added successfully.');
    }

    public function update(Request $request, Client $client, ClientNote $note)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'priority' => 'required|in:low,medium,high,critical',
    ]);

    $note->update($validated);

    return back()->with('success', 'Note updated.');
}

    public function destroy(Client $client, ClientNote $note)
    {
        $note->delete();

        return back()->with('success', 'Note deleted.');
    }
}
