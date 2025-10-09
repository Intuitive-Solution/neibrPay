<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceAttachment;
use App\Models\InvoiceUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvoiceAttachmentController extends Controller
{
    /**
     * Display a listing of attachments for an invoice.
     */
    public function index(Request $request, int $invoiceId): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        // Verify the invoice belongs to the user's tenant
        $invoice = InvoiceUnit::where('id', $invoiceId)
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $attachments = InvoiceAttachment::where('invoice_unit_id', $invoiceId)
            ->with('uploader')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $attachments,
            'meta' => [
                'total' => $attachments->count(),
                'invoice_id' => $invoiceId,
            ],
        ]);
    }

    /**
     * Store a newly uploaded attachment.
     */
    public function store(Request $request, int $invoiceId): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        // Verify the invoice belongs to the user's tenant
        $invoice = InvoiceUnit::where('id', $invoiceId)
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        // Prevent adding attachments to sent/paid invoices
        if (in_array($invoice->status, ['sent', 'paid'])) {
            return response()->json(['message' => 'Cannot add attachments to sent or paid invoices'], 400);
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

        // Check if file already exists
        $existingAttachment = InvoiceAttachment::where('file_hash', $fileHash)
            ->where('invoice_unit_id', $invoiceId)
            ->first();

        if ($existingAttachment) {
            return response()->json(['message' => 'This file has already been uploaded to this invoice'], 400);
        }

        // Determine attachment type
        $attachmentType = $this->getAttachmentType($mimeType, $extension);

        // Generate unique filename
        $filename = Str::uuid() . '.' . $extension;
        $filePath = 'invoice-attachments/' . $filename;

        // Store the file
        $stored = Storage::disk('public')->put($filePath, file_get_contents($file));

        if (!$stored) {
            return response()->json(['message' => 'Failed to store file'], 500);
        }

        // Create attachment record
        $attachment = InvoiceAttachment::create([
            'invoice_unit_id' => $invoiceId,
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
    public function show(Request $request, int $invoiceId, int $attachmentId): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        // Verify the invoice belongs to the user's tenant
        $invoice = InvoiceUnit::where('id', $invoiceId)
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $attachment = InvoiceAttachment::where('id', $attachmentId)
            ->where('invoice_unit_id', $invoiceId)
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
    public function download(Request $request, int $invoiceId, int $attachmentId)
    {
        $user = $request->get('firebase_user');
        
        // Verify the invoice belongs to the user's tenant
        $invoice = InvoiceUnit::where('id', $invoiceId)
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $attachment = InvoiceAttachment::where('id', $attachmentId)
            ->where('invoice_unit_id', $invoiceId)
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
    public function destroy(Request $request, int $invoiceId, int $attachmentId): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        // Verify the invoice belongs to the user's tenant
        $invoice = InvoiceUnit::where('id', $invoiceId)
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        // Prevent removing attachments from sent/paid invoices
        if (in_array($invoice->status, ['sent', 'paid'])) {
            return response()->json(['message' => 'Cannot remove attachments from sent or paid invoices'], 400);
        }

        $attachment = InvoiceAttachment::where('id', $attachmentId)
            ->where('invoice_unit_id', $invoiceId)
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
