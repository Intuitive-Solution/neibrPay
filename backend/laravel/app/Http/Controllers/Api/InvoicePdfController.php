<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoicePdf;
use App\Models\InvoiceUnit;
use App\Services\InvoicePdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class InvoicePdfController extends Controller
{
    public function __construct(
        private InvoicePdfService $pdfService
    ) {}

    /**
     * Generate PDF from HTML for an invoice.
     */
    public function generate(Request $request, InvoiceUnit $invoice): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        // Verify the invoice belongs to the user's tenant
        if ($invoice->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'html' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Generate PDF
            $invoicePdf = $this->pdfService->generatePdf(
                $invoice,
                $request->input('html'),
                $user->id
            );

            return response()->json([
                'data' => $invoicePdf->load('generator'),
                'message' => 'PDF generated successfully',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the latest PDF info for an invoice.
     */
    public function latest(Request $request, InvoiceUnit $invoice): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        // Verify the invoice belongs to the user's tenant
        if ($invoice->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $latestPdf = $this->pdfService->getLatestPdf($invoice);

        if (!$latestPdf) {
            return response()->json([
                'message' => 'No PDF found for this invoice'
            ], 404);
        }

        return response()->json([
            'data' => $latestPdf->load('generator'),
        ]);
    }

    /**
     * View the latest PDF for an invoice (for iframe display).
     */
    public function view(Request $request, InvoiceUnit $invoice): Response
    {
        $user = $request->get('firebase_user');
        
        // Verify the invoice belongs to the user's tenant
        if ($invoice->tenant_id !== $user->tenant_id) {
            return response('Invoice not found', 404);
        }

        $latestPdf = $this->pdfService->getLatestPdf($invoice);

        if (!$latestPdf) {
            return response('No PDF found for this invoice', 404);
        }

        $content = $this->pdfService->getPdfContent($latestPdf);

        if (!$content) {
            return response('PDF file not found', 404);
        }

        return response($content)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $latestPdf->file_name . '"')
            ->header('Content-Length', strlen($content))
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * Download the latest PDF for an invoice.
     */
    public function download(Request $request, InvoiceUnit $invoice): Response
    {
        $user = $request->get('firebase_user');
        
        // Verify the invoice belongs to the user's tenant
        if ($invoice->tenant_id !== $user->tenant_id) {
            return response('Invoice not found', 404);
        }

        $latestPdf = $this->pdfService->getLatestPdf($invoice);

        if (!$latestPdf) {
            return response('No PDF found for this invoice', 404);
        }

        $content = $this->pdfService->getPdfContent($latestPdf);

        if (!$content) {
            return response('PDF file not found', 404);
        }

        return response($content)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $latestPdf->file_name . '"')
            ->header('Content-Length', strlen($content));
    }

    /**
     * Get all versions of PDFs for an invoice.
     */
    public function versions(Request $request, InvoiceUnit $invoice): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        // Verify the invoice belongs to the user's tenant
        if ($invoice->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $versions = $this->pdfService->getAllVersions($invoice);

        return response()->json([
            'data' => $versions->load('generator'),
            'meta' => [
                'total' => $versions->count(),
                'invoice_id' => $invoice->id,
            ],
        ]);
    }

    /**
     * Download a specific version of PDF for an invoice.
     */
    public function downloadVersion(Request $request, InvoiceUnit $invoice, int $version): Response
    {
        $user = $request->get('firebase_user');
        
        // Verify the invoice belongs to the user's tenant
        if ($invoice->tenant_id !== $user->tenant_id) {
            return response('Invoice not found', 404);
        }

        $pdfVersion = $this->pdfService->getPdfVersion($invoice, $version);

        if (!$pdfVersion) {
            return response('PDF version not found', 404);
        }

        $content = $this->pdfService->getPdfContent($pdfVersion);

        if (!$content) {
            return response('PDF file not found', 404);
        }

        return response($content)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $pdfVersion->file_name . '"')
            ->header('Content-Length', strlen($content));
    }
}
