<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientTask;

class ClientTaskController extends Controller
{
    public function store(Request $request, Client $client)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'nullable|date',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'reminder_at' => 'nullable|date'
        ]);

        ClientTask::create([
            'client_id' => $client->id,
            'title' => $request->title,
            'description'=> $request->description,
            'due_date' => $request->due_date,
            'assigned_to_user_id' => $request->assigned_to_user_id,
            'reminder_at' => $request->reminder_at,
            'created_by_user_id' => auth()->id()
        ]);

        return back();
    }

    public function update(ClientTask $task)
    {
        $task->update([
            'status' => 'done'
        ]);

        return back();
    }

    public function destroy(ClientTask $task)
    {
        $task->delete();

        return back();
    }
}
