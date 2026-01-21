<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoicePdf;
use App\Models\InvoiceUnit;
use App\Services\FileStorageService;
use App\Services\InvoicePdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;

class InvoicePdfController extends Controller
{
    public function __construct(
        private InvoicePdfService $pdfService,
        private FileStorageService $fileStorageService
    ) {}

    /**
     * Generate PDF from HTML for an invoice.
     */
    public function generate(Request $request, InvoiceUnit $invoice): JsonResponse
    {
        $user = $request->user();
        
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
        $user = $request->user();
        
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
        $user = $request->user();
        
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
        $user = $request->user();
        
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
     * Get signed URL for the latest PDF (for S3 access).
     */
    public function getSignedUrl(Request $request, InvoiceUnit $invoice): JsonResponse
    {
        $user = $request->user();
        
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

        if ($this->fileStorageService->isS3Disk()) {
            $signedUrl = $this->fileStorageService->getTemporaryUrl(
                $latestPdf->file_path,
                6,
                ['ResponseCacheControl' => 'no-store, max-age=0']
            );
        } else {
            // Ensure HTTPS is used for signed URLs
            $signedUrl = URL::temporarySignedRoute(
                'invoices.pdf.signed',
                now()->addMinutes(6),
                ['invoice' => $invoice->id],
                true // absolute URL
            );
            
            // Force HTTPS if the URL is HTTP (for production environments)
            if (str_starts_with($signedUrl, 'http://') && (config('app.env') === 'production' || str_contains($signedUrl, 'neibrpay.com'))) {
                $signedUrl = str_replace('http://', 'https://', $signedUrl);
            }
        }

        return response()->json([
            'data' => [
                'id' => $latestPdf->id,
                'file_name' => $latestPdf->file_name,
                'file_url' => $signedUrl,
                'file_size' => $latestPdf->file_size,
                'created_at' => $latestPdf->created_at,
            ],
        ]);
    }

    /**
     * View the latest PDF for an invoice via signed URL.
     */
    public function viewSigned(InvoiceUnit $invoice): Response
    {
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
            ->header('Cache-Control', 'no-store, max-age=0');
    }
}
