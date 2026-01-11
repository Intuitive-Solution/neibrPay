<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HoaDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Display a listing of HOA documents.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = HoaDocument::forTenant($user->tenant_id)
            ->with('uploader:id,name,email')
            ->with('folder:id,name')
            ->orderBy('created_at', 'desc');

        // If 'all' parameter is true, return all documents (for search)
        if ($request->boolean('all')) {
            // Return all documents without folder_id filtering
        } elseif ($request->has('folder_id')) {
            // Filter by folder_id to show only documents in the current folder
            // If folder_id is null, show root documents (no folder)
            // If folder_id is provided, show only documents in that folder
            if ($request->input('folder_id') === null || $request->input('folder_id') === '') {
                // Get documents at root level (no folder)
                $query->whereNull('folder_id');
            } else {
                // Get documents with specific folder_id
                $query->where('folder_id', $request->input('folder_id'));
            }
        } else {
            // If folder_id is not provided, default to root documents
            $query->whereNull('folder_id');
        }

        // If user is a resident, automatically filter by visible_to_residents = true
        if ($user->isResident()) {
            $query->where('visible_to_residents', true);
        } elseif ($request->has('visible_to_residents')) {
            // For admins/bookkeepers, allow filtering by visibility if requested
            $query->where('visible_to_residents', $request->boolean('visible_to_residents'));
        }

        $documents = $query->get();

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
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Only admins can upload documents
        if (!$user->isAdmin()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Only admins can upload documents',
            ], 403);
        }

        $validated = $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,txt,jpg,jpeg,png,gif,xls,xlsx,csv,zip',
            'description' => 'nullable|string|max:500',
            'visible_to_residents' => 'nullable|boolean',
            'folder_id' => [
                'nullable',
                'integer',
                'exists:hoa_document_folders,id',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value !== null) {
                        $folder = \App\Models\HoaDocumentFolder::find($value);
                        if ($folder && $folder->tenant_id !== $user->tenant_id) {
                            $fail('The selected folder does not belong to your tenant.');
                        }
                    }
                },
            ],
        ]);

        $file = $validated['file'];

        // Generate unique filename
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;

        // Store file
        $filePath = $file->storeAs('hoa-documents', $filename, 'public');

        // Calculate file hash for duplicate detection
        $fileHash = hash_file('sha256', $file->getRealPath());

        // Check for duplicate files within the same tenant
        $existingDocument = HoaDocument::where('file_hash', $fileHash)
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if ($existingDocument) {
            // Delete the uploaded file since it's a duplicate
            Storage::disk('public')->delete($filePath);

            return response()->json([
                'error' => 'File already exists',
                'message' => 'A document with the same content already exists.',
            ], 409);
        }

        // Create document record
        $document = HoaDocument::create([
            'tenant_id' => $user->tenant_id,
            'folder_id' => $validated['folder_id'] ?? null,
            'file_name' => $originalName,
            'file_path' => $filePath,
            'file_hash' => $fileHash,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $validated['description'] ?? null,
            'visible_to_residents' => (bool) ($validated['visible_to_residents'] ?? false),
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
    public function show(Request $request, HoaDocument $document): JsonResponse
    {
        $user = $request->user();

        // Ensure user can access this document
        if ($document->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Residents can only see documents marked as visible
        if ($user->isResident() && !$document->visible_to_residents) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $document->load('uploader:id,name,email');

        return response()->json([
            'data' => $document,
        ]);
    }

    /**
     * Update the specified document.
     */
    public function update(Request $request, HoaDocument $document): JsonResponse
    {
        $user = $request->user();

        // Only admins can update documents
        if (!$user->isAdmin()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Only admins can update documents',
            ], 403);
        }

        // Ensure user can access this document
        if ($document->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'description' => 'nullable|string|max:500',
            'visible_to_residents' => 'nullable|boolean',
            'folder_id' => [
                'nullable',
                'integer',
                'exists:hoa_document_folders,id',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value !== null) {
                        $folder = \App\Models\HoaDocumentFolder::find($value);
                        if ($folder && $folder->tenant_id !== $user->tenant_id) {
                            $fail('The selected folder does not belong to your tenant.');
                        }
                    }
                },
            ],
        ]);

        $document->update($validated);
        $document->load('uploader:id,name,email');

        return response()->json([
            'data' => $document,
            'message' => 'Document updated successfully',
        ]);
    }

    /**
     * Download the specified document.
     */
    public function download(Request $request, HoaDocument $document)
    {
        $user = $request->user();

        // Ensure user can access this document
        if ($document->tenant_id !== $user->tenant_id) {
            abort(403, 'Unauthorized');
        }

        // Residents can only download documents marked as visible
        if ($user->isResident() && !$document->visible_to_residents) {
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
    public function destroy(Request $request, HoaDocument $document): JsonResponse
    {
        $user = $request->user();

        // Only admins can delete documents
        if (!$user->isAdmin()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Only admins can delete documents',
            ], 403);
        }

        // Ensure user can access this document
        if ($document->tenant_id !== $user->tenant_id) {
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
    public function forceDelete(Request $request, HoaDocument $document): JsonResponse
    {
        $user = $request->user();

        // Only admins can permanently delete documents
        if (!$user->isAdmin()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Only admins can permanently delete documents',
            ], 403);
        }

        // Ensure user can access this document
        if ($document->tenant_id !== $user->tenant_id) {
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
