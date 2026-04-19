<?php

namespace App\Services;

use App\Models\InvoiceUnit;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InvoiceN8nInvoiceEmailService
{
    public function __construct(
        private readonly InvoiceN8nNotificationService $notificationService
    ) {
    }

    /**
     * POST invoice email payload to the configured n8n webhook.
     *
     * @return array{attempted: bool, http_success: bool}
     */
    public function postInvoiceWebhook(InvoiceUnit $invoice, User $owner, ?string $recipientEmail = null): array
    {
        $n8nWebhookUrl = config('n8n.webhook_url');
        if (!$n8nWebhookUrl) {
            Log::warning('N8N_WEBHOOK_URL not configured. Invoice webhook not sent.', [
                'invoice_id' => $invoice->id,
            ]);

            return ['attempted' => false, 'http_success' => false];
        }

        $webhookSecret = config('n8n.webhook_secret');
        $payload = $this->notificationService->buildInvoicePayload($invoice, $owner, $recipientEmail);
        $headers = ['Content-Type' => 'application/json'];
        if ($webhookSecret) {
            $headers['X-Webhook-Token'] = $webhookSecret;
        }

        try {
            $response = Http::timeout(10)
                ->retry(2, 100)
                ->withHeaders($headers)
                ->post($n8nWebhookUrl, $payload);

            $ok = $response->successful();
            if (!$ok) {
                Log::warning('n8n invoice webhook returned non-success status', [
                    'invoice_id' => $invoice->id,
                    'http_status' => $response->status(),
                ]);
            } else {
                Log::info('n8n invoice webhook sent successfully', [
                    'invoice_id' => $invoice->id,
                    'http_status' => $response->status(),
                ]);
            }

            return ['attempted' => true, 'http_success' => $ok];
        } catch (\Throwable $e) {
            Log::error('n8n invoice webhook failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return ['attempted' => true, 'http_success' => false];
        }
    }
}
