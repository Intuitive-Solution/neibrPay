<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExpenseAttachment;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExpenseAttachmentController extends Controller
{
    /**
     * Display a listing of attachments for an expense.
     */
    public function index(Request $request, Expense $expense): JsonResponse
    {
        $user = $request->user();
        
        // Verify the expense belongs to the user's tenant
        if ($expense->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Expense not found'], 404);
        }

        $attachments = ExpenseAttachment::where('expense_id', $expense->id)
            ->with('uploader')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $attachments,
            'meta' => [
                'total' => $attachments->count(),
                'expense_id' => $expense->id,
            ],
        ]);
    }

    /**
     * Store a newly uploaded attachment.
     */
    public function store(Request $request, Expense $expense): JsonResponse
    {
        $user = $request->user();
        
        // Verify the expense belongs to the user's tenant
        if ($expense->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Expense not found'], 404);
        }

        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'description' => 'nullable|string|max:255',
        ]);

        $file = $validated['file'];
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();
        $fileHash = hash_file('sha256', $file->getPathname());

        // Check if file already exists for this specific expense
        $existingAttachment = ExpenseAttachment::where('file_hash', $fileHash)
            ->where('expense_id', $expense->id)
            ->first();

        if ($existingAttachment) {
            return response()->json(['message' => 'This file has already been uploaded to this expense'], 400);
        }

        // Generate unique filename for each expense (allow duplicate file storage)
        $filename = Str::uuid() . '.' . $extension;
        $filePath = 'expense-attachments/' . $filename;

        // Store the file (each expense gets its own copy)
        $stored = Storage::disk('public')->put($filePath, file_get_contents($file));

        if (!$stored) {
            return response()->json(['message' => 'Failed to store file'], 500);
        }

        // Determine attachment type
        $attachmentType = $this->getAttachmentType($mimeType, $extension);

        // Create attachment record
        $attachment = ExpenseAttachment::create([
            'expense_id' => $expense->id,
            'file_name' => $originalName,
            'file_path' => $filePath,
            'file_hash' => $fileHash,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'attachment_type' => $attachmentType,
            'uploaded_by' => $user->id,
        ]);

        $attachment->load('uploader');

        return response()->json([
            'data' => $attachment,
            'message' => 'File uploaded successfully',
        ], 201);
    }

    /**
     * Display the specified attachment.
     */
    public function show(Request $request, Expense $expense, int $attachmentId): JsonResponse
    {
        $user = $request->user();
        
        // Verify the expense belongs to the user's tenant
        if ($expense->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Expense not found'], 404);
        }

        $attachment = ExpenseAttachment::where('id', $attachmentId)
            ->where('expense_id', $expense->id)
            ->with('uploader')
            ->first();

        if (!$attachment) {
            return response()->json(['message' => 'Attachment not found'], 404);
        }

        return response()->json([
            'data' => $attachment,
        ]);
    }

    /**
     * Download the specified attachment.
     */
    public function download(Request $request, Expense $expense, int $attachmentId)
    {
        $user = $request->user();
        
        // Verify the expense belongs to the user's tenant
        if ($expense->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Expense not found'], 404);
        }

        $attachment = ExpenseAttachment::where('id', $attachmentId)
            ->where('expense_id', $expense->id)
            ->first();

        if (!$attachment) {
            return response()->json(['message' => 'Attachment not found'], 404);
        }

        $filePath = storage_path('app/public/' . $attachment->file_path);

        if (!file_exists($filePath)) {
            return response()->json(['message' => 'File not found on disk'], 404);
        }

        return response()->download($filePath, $attachment->file_name);
    }

    /**
     * Remove the specified attachment.
     */
    public function destroy(Request $request, Expense $expense, int $attachmentId): JsonResponse
    {
        $user = $request->user();
        
        // Verify the expense belongs to the user's tenant
        if ($expense->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Expense not found'], 404);
        }

        $attachment = ExpenseAttachment::where('id', $attachmentId)
            ->where('expense_id', $expense->id)
            ->first();

        if (!$attachment) {
            return response()->json(['message' => 'Attachment not found'], 404);
        }

        // Delete the file from storage
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        // Delete the attachment record
        $attachment->delete();

        return response()->json([
            'message' => 'Attachment deleted successfully',
        ]);
    }

    /**
     * Determine attachment type based on MIME type and extension.
     */
    private function getAttachmentType(string $mimeType, string $extension): string
    {
        // PDF files
        if ($mimeType === 'application/pdf' || $extension === 'pdf') {
            return 'pdf';
        }

        // Image files
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        // Document files
        $documentExtensions = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'];
        $documentMimeTypes = [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'application/rtf',
        ];

        if (in_array($extension, $documentExtensions) || in_array($mimeType, $documentMimeTypes)) {
            return 'document';
        }

        return 'other';
    }
}