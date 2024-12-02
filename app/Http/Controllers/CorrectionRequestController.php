<?php
// app/Http/Controllers/CorrectionRequestController.php

namespace App\Http\Controllers;

use App\Models\CorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CorrectionRequestController extends Controller
{
    public function index()
    {
        $requests = CorrectionRequest::latest()->paginate(10);
        return response()->json($requests);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'email' => 'required|email',
            'document_type' => 'required|string',
            'description' => 'required|string',
            'attachment' => 'required|file|max:10240', // 10MB max
        ]);

        $path = $request->file('attachment')->store('correction-requests');

        $correctionRequest = CorrectionRequest::create([
            'client_name' => $validated['client_name'],
            'email' => $validated['email'],
            'document_type' => $validated['document_type'],
            'description' => $validated['description'],
            'attachment_path' => $path,
            'status' => 'pending',
        ]);

        return response()->json($correctionRequest, 201);
    }

    public function update(Request $request, CorrectionRequest $correctionRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in-progress,completed',
            'notes' => 'nullable|string',
        ]);

        $correctionRequest->update($validated);

        return response()->json($correctionRequest);
    }

    public function show(CorrectionRequest $correctionRequest)
    {
        return response()->json($correctionRequest);
    }
}