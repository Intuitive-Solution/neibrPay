<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\UnitDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UnitDocumentController extends Controller
{
    /**
     * Display a listing of documents for a unit.
     */
    public function index(Request $request, Unit $unit): JsonResponse
    {
        $user = $request->user();
        
        // Ensure user can access this unit
        if ($unit->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $documents = $unit->documents()
            ->with('uploader:id,name,email')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $documents,
            'meta' => [
                'total' => $documents->count(),
            ],
        ]);
    }

    /**
     * Store a newly uploaded document.
     */
    public function store(Request $request, Unit $unit): JsonResponse
    {
        $user = $request->user();
        
        
        // Ensure user can access this unit
        if ($unit->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,txt,jpg,jpeg,png,gif,xls,xlsx,csv,zip',
            'description' => 'nullable|string|max:500',
        ]);

        $file = $validated['file'];
        
        // Generate unique filename
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        
        // Store file
        $filePath = $file->storeAs('unit-documents', $filename, 'public');
        
        // Calculate file hash for duplicate detection
        $fileHash = hash_file('sha256', $file->getRealPath());
        
        // Check for duplicate files
        $existingDocument = UnitDocument::where('file_hash', $fileHash)
            ->where('unit_id', $unit->id)
            ->first();
            
        if ($existingDocument) {
            // Delete the uploaded file since it's a duplicate
            Storage::disk('public')->delete($filePath);
            
            return response()->json([
                'error' => 'File already exists',
                'message' => 'A document with the same content already exists for this unit.',
            ], 409);
        }

        // Create document record
        $document = UnitDocument::create([
            'unit_id' => $unit->id,
            'tenant_id' => $user->tenant_id,
            'file_name' => $originalName,
            'file_path' => $filePath,
            'file_hash' => $fileHash,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $validated['description'] ?? null,
            'uploaded_by' => $user->id,
        ]);

        $document->load('uploader:id,name,email');

        return response()->json([
            'data' => $document,
            'message' => 'Document uploaded successfully',
        ], 201);
    }

    /**
     * Display the specified document.
     */
    public function show(Request $request, Unit $unit, UnitDocument $document): JsonResponse
    {
        $user = $request->user();
        
        // Ensure user can access this unit and document
        if ($unit->tenant_id !== $user->tenant_id || $document->unit_id !== $unit->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $document->load('uploader:id,name,email');

        return response()->json([
            'data' => $document,
        ]);
    }

    /**
     * Download the specified document.
     */
    public function download(Request $request, Unit $unit, UnitDocument $document)
    {
        $user = $request->user();
        
        // Ensure user can access this unit and document
        if ($unit->tenant_id !== $user->tenant_id || $document->unit_id !== $unit->id) {
            abort(403, 'Unauthorized');
        }

        // Check if file exists
        if (!$document->fileExists()) {
            abort(404, 'File not found');
        }

        $filePath = storage_path('app/public/' . $document->file_path);
        
        return response()->download($filePath, $document->file_name);
    }

    /**
     * Remove the specified document.
     */
    public function destroy(Request $request, Unit $unit, UnitDocument $document): JsonResponse
    {
        $user = $request->user();
        
        // Ensure user can access this unit and document
        if ($unit->tenant_id !== $user->tenant_id || $document->unit_id !== $unit->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Soft delete the document
        $document->delete();

        return response()->json([
            'message' => 'Document deleted successfully',
        ]);
    }

    /**
     * Permanently delete the specified document.
     */
    public function forceDelete(Request $request, Unit $unit, UnitDocument $document): JsonResponse
    {
        $user = $request->user();
        
        // Ensure user can access this unit and document
        if ($unit->tenant_id !== $user->tenant_id || $document->unit_id !== $unit->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Delete file from storage
        if ($document->fileExists()) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Force delete the document record
        $document->forceDelete();

        return response()->json([
            'message' => 'Document permanently deleted',
        ]);
    }
}
