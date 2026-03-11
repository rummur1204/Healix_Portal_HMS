<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientDoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientDocController extends Controller
{
    public function store(Request $request, Client $client)
    {
        $request->validate([
            'file_name' => 'required|string|max:255',
            'file' => 'required|file|max:10240'
        ]);

        $file = $request->file('file');

        $path = $file->store('client-docs', 'public');

        ClientDoc::create([
            'client_id' => $client->id,
            'file_name' => $request->file_name,
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
            'upload_by_user_id' => auth()->id()
        ]);

        return back();
    }

    public function destroy(ClientDoc $doc)
    {
        Storage::disk('public')->delete($doc->file_path);

        $doc->delete();

        return back();
    }
}

