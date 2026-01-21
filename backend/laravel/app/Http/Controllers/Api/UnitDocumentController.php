<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\UnitDocument;
use App\Services\FileStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UnitDocumentController extends Controller
{
    public function __construct(
        protected FileStorageService $fileStorage
    ) {
    }
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
        
        // Calculate file hash for duplicate detection
        $fileHash = hash_file('sha256', $file->getRealPath());
        
        // Check for duplicate files
        $existingDocument = UnitDocument::where('file_hash', $fileHash)
            ->where('unit_id', $unit->id)
            ->first();
            
        if ($existingDocument) {
            return response()->json([
                'error' => 'File already exists',
                'message' => 'A document with the same content already exists for this unit.',
            ], 409);
        }
        
        // Store file
        $filePath = $this->fileStorage->storeAs('unit-documents', $file->getRealPath(), $filename);

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

        return $this->fileStorage->getDownloadResponse($document->file_path, $document->file_name);
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
        $this->fileStorage->delete($document->file_path);

        // Force delete the document record
        $document->forceDelete();

        return response()->json([
            'message' => 'Document permanently deleted',
        ]);
    }
}
