# n8n Verification Code Email Workflow

This document describes how to set up the n8n workflow for sending verification code emails.

## Workflow Structure

1. **Webhook Trigger Node**
   - Method: POST
   - Path: `/verification-code`
   - Authentication: None (or use webhook secret if configured)

2. **Email Node** (or HTTP Request to email service)
   - Use your email service (SendGrid, Mailgun, SMTP, etc.)
   - Subject: "Your NeibrPay verification code is {code}"
   - HTML Body: Use the template below

## Webhook Payload

The webhook will receive the following JSON payload:

```json
{
  "type": "verification_code",
  "to": "user@example.com",
  "email": "user@example.com",
  "recipient": "user@example.com",
  "code": "123456",
  "user_name": "John Doe",
  "tenant_name": "Sunset Community",
  "expires_in_minutes": 15
}
```

**Note:** The payload includes multiple recipient fields (`to`, `email`, `recipient`) to support different n8n email node configurations. Use whichever field your email node expects.

## Email Template

See `n8n-verification-code-email-template.html` for the complete HTML email template.

## Setup Instructions

1. Create a new workflow in n8n
2. Add a Webhook node:
   - Set method to POST
   - Set path to `/verification-code`
   - Copy the webhook URL
3. Add an Email node (or HTTP Request node for your email service):
   - Configure your email service credentials
   - **Set recipient/To field:** Use `{{ $json.to }}` or `{{ $json.email }}` or `{{ $json.recipient }}` (depending on your email node)
   - Set subject: `Your NeibrPay verification code is {{ $json.code }}`
   - Set HTML body: Use the template from `n8n-verification-code-email-template.html`
   - **Important:** Make sure the recipient field is properly mapped from the webhook data
4. Connect the nodes: Webhook → Email
5. Activate the workflow
6. Copy the webhook URL and add it to your Laravel `.env`:
   ```
   N8N_VERIFICATION_CODE_WEBHOOK_URL=https://your-n8n-instance.com/webhook/verification-code
   ```

## Testing

Test the workflow by sending a POST request to the webhook URL:

```bash
curl -X POST https://your-n8n-instance.com/webhook/verification-code \
  -H "Content-Type: application/json" \
  -d '{
    "type": "verification_code",
    "email": "test@example.com",
    "code": "123456",
    "user_name": "Test User",
    "tenant_name": "Test Community",
    "expires_in_minutes": 15
  }'
```
