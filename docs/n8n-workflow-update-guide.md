# n8n Workflow Update Guide: Add Signup Activation Email

## Overview

Update workflow `ezCq61xykab9NF7D` to handle both invoice notifications and signup activation emails by routing based on the payload action.

## Current Workflow Structure

```
Webhook → Send email (invoice) → Success/Error Response
```

## Updated Workflow Structure

```
Webhook → IF (Check action type) →
  ├─ TRUE (has signup_link) → Send Activation Email → Success/Error Response
  └─ FALSE (has invoice_summary) → Send Invoice Email → Success/Error Response
```

## Step-by-Step Update Instructions

### 1. Add IF Node (Route by Action Type)

- **Node Type**: IF
- **Position**: After "Webhook", before "Send email"
- **Configuration**:
  - **Condition**: `{{ $json.body.signup_link !== undefined && $json.body.signup_link !== null }}`
  - **TRUE Output**: Routes to Signup Activation Email
  - **FALSE Output**: Routes to existing Invoice Email

### 2. Create New "Send Activation Email" Node

- **Node Type**: Send Email (SMTP)
- **Position**: After IF node (TRUE branch)
- **Configuration**:
  - **From Email**: `admin@geninvoices.com`
  - **To Email**: `={{ $json.body.recipient.email }}`
  - **Subject**: `Setup Your Account - {{ $json.body.tenant_name }}`
  - **Email Format**: HTML
  - **HTML Content**: (See template below)
  - **Credentials**: Use existing SMTP account

### 3. Update Connections

- **Webhook** → **IF** (main output)
- **IF** (TRUE) → **Send Activation Email**
- **IF** (FALSE) → **Send email** (existing invoice node)
- **Send Activation Email** → **Success Response** (main output)
- **Send Activation Email** → **Error Response** (error output)

## HTML Email Template for Signup Activation

Use the following HTML template in the "Send Activation Email" node:

```html
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  </head>
  <body
    style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;"
  >
    <table
      role="presentation"
      cellspacing="0"
      cellpadding="0"
      border="0"
      width="100%"
      style="background-color: #f4f4f4;"
    >
      <tr>
        <td align="center" style="padding: 40px 20px;">
          <table
            role="presentation"
            cellspacing="0"
            cellpadding="0"
            border="0"
            width="600"
            style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"
          >
            <!-- Header -->
            <tr>
              <td
                style="padding: 40px 40px 20px; text-align: center; background-color: #2563eb; border-radius: 8px 8px 0 0;"
              >
                <h1
                  style="margin: 0; color: #ffffff; font-size: 28px; font-weight: bold;"
                >
                  Setup Your Account
                </h1>
              </td>
            </tr>

            <!-- Content -->
            <tr>
              <td style="padding: 40px;">
                <p
                  style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #333333;"
                >
                  Dear {{ $json.body.recipient.name }},
                </p>

                <p
                  style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #333333;"
                >
                  Welcome to <strong>{{ $json.body.tenant_name }}</strong>!
                  We're excited to have you join our community management
                  platform.
                </p>

                <p
                  style="margin: 0 0 30px; font-size: 16px; line-height: 1.6; color: #333333;"
                >
                  To get started, please click the button below to set up your
                  account. This will only take a few minutes.
                </p>

                <!-- CTA Button -->
                <table
                  role="presentation"
                  cellspacing="0"
                  cellpadding="0"
                  border="0"
                  width="100%"
                >
                  <tr>
                    <td align="center" style="padding: 20px 0 30px;">
                      <a
                        href="{{ $json.body.signup_link }}"
                        style="display: inline-block; padding: 16px 32px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: 600; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);"
                      >
                        Setup My Account
                      </a>
                    </td>
                  </tr>
                </table>

                <!-- Additional Info -->
                <div
                  style="background-color: #f3f4f6; padding: 20px; border-radius: 6px; margin: 30px 0;"
                >
                  <p
                    style="margin: 0 0 10px; font-size: 14px; color: #6b7280; font-weight: 600;"
                  >
                    What's next?
                  </p>
                  <ul
                    style="margin: 10px 0 0; padding-left: 20px; font-size: 14px; color: #6b7280; line-height: 1.8;"
                  >
                    <li>Create your secure password</li>
                    <li>Complete your profile information</li>
                    <li>Access your community dashboard</li>
                  </ul>
                </div>

                <p
                  style="margin: 30px 0 0; font-size: 14px; line-height: 1.6; color: #6b7280;"
                >
                  If the button doesn't work, you can copy and paste this link
                  into your browser:
                </p>
                <p
                  style="margin: 10px 0 0; font-size: 12px; line-height: 1.6; color: #2563eb; word-break: break-all;"
                >
                  {{ $json.body.signup_link }}
                </p>

                <p
                  style="margin: 30px 0 0; font-size: 12px; line-height: 1.6; color: #9ca3af;"
                >
                  This invitation link is valid for your email address only. If
                  you didn't request this invitation, please ignore this email.
                </p>
              </td>
            </tr>

            <!-- Footer -->
            <tr>
              <td
                style="padding: 30px 40px; text-align: center; background-color: #f9fafb; border-radius: 0 0 8px 8px; border-top: 1px solid #e5e7eb;"
              >
                <p style="margin: 0 0 10px; font-size: 14px; color: #6b7280;">
                  Best regards,<br />
                  <strong>{{ $json.body.tenant_name }} Management Team</strong>
                </p>
                <p style="margin: 20px 0 0; font-size: 12px; color: #9ca3af;">
                  This is an automated message from {{ $json.body.tenant_name
                  }}. Please do not reply to this email.
                </p>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>
```

## Payload Structure

### Signup Activation Payload (from backend)

```json
{
  "recipient": {
    "email": "user@example.com",
    "name": "John Doe"
  },
  "signup_link": "https://app.example.com/signup?member=true&email=...",
  "tenant_name": "Sunset HOA"
}
```

### Invoice Notification Payload (existing)

```json
{
  "recipient": {
    "email": "user@example.com",
    "name": "John Doe"
  },
  "invoice_summary": { ... },
  "magic_link": "...",
  "tenant_name": "Sunset HOA"
}
```

## Testing

1. **Test Invoice Flow**: Send a POST request to the webhook with `invoice_summary` in the payload
2. **Test Signup Flow**: Send a POST request to the webhook with `signup_link` in the payload
3. Verify both emails are sent correctly with appropriate content

## Notes

- The IF node checks for `signup_link` presence to route to activation email
- Both email nodes use the same SMTP credentials
- Success/Error Response nodes can handle both flows
- The HTML template uses n8n expressions ({{ }}) to inject dynamic data
