# Email Configuration Setup

This document explains how to configure email sending for the support form.

## Environment Variables

Create a `.env` file in the `apps/marketing-site` directory with the following variables:

```env
# SMTP Email Configuration
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=465
SMTP_USER=support@neibrpay.com
SMTP_PASSWORD=your_smtp_password_here
SUPPORT_EMAIL=support@neibrpay.com

# Other Configuration (if needed)
ADMIN_WEB_URL=http://localhost:3000
CALENDLY_URL=https://calendly.com/imailtahir/neibrpay-demo
POSTHOG_KEY=
POSTHOG_HOST=https://us.i.posthog.com
```

## SMTP Configuration Details

Based on Hostinger email settings:

- **SMTP Host**: `smtp.hostinger.com`
- **SMTP Port**: `465` (SSL/TLS)
- **SMTP User**: `support@neibrpay.com`
- **SMTP Password**: Your email account password

## How It Works

When a user submits the support form:

1. The form data is validated
2. An email is sent to `support@neibrpay.com` with:
   - Reason for contact
   - Name
   - Email (set as reply-to)
   - Community Name
   - Callback Number
   - Timestamp

3. The email is formatted as HTML with a clean, professional design
4. A plain text version is also included for email clients that don't support HTML

## Security Notes

- The `.env` file is gitignored and should never be committed
- All user input is HTML-escaped to prevent XSS attacks
- SMTP credentials are stored server-side only (not exposed to the client)

## Testing

To test the email functionality:

1. Ensure your `.env` file is configured correctly
2. Start the development server: `npm run dev`
3. Navigate to `/support` page
4. Fill out and submit the form
5. Check the `support@neibrpay.com` inbox for the email
