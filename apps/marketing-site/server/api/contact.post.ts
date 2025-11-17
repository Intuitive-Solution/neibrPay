import nodemailer from 'nodemailer';

// Escape HTML to prevent XSS attacks
function escapeHtml(text: string): string {
  const map: Record<string, string> = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
  };
  return text.replace(/[&<>"']/g, m => map[m]);
}

export default defineEventHandler(async event => {
  const body = await readBody(event);
  const config = useRuntimeConfig();

  // Validate required fields
  const { name, email, communityName, message } = body;

  if (!name || !email || !communityName || !message) {
    throw createError({
      statusCode: 400,
      statusMessage: 'All fields are required',
      data: {
        message: 'Please fill in all required fields.',
      },
    });
  }

  // Validate email format
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    throw createError({
      statusCode: 400,
      statusMessage: 'Invalid email format',
      data: {
        message: 'Please provide a valid email address.',
      },
    });
  }

  // Validate message length
  if (message.length > 2000) {
    throw createError({
      statusCode: 400,
      statusMessage: 'Message too long',
      data: {
        message: 'Message must be 2000 characters or less.',
      },
    });
  }

  // Create SMTP transporter
  const transporter = nodemailer.createTransport({
    host: config.smtpHost,
    port: parseInt(config.smtpPort),
    secure: true, // true for 465, false for other ports
    auth: {
      user: config.smtpUser,
      pass: config.smtpPassword,
    },
  });

  // Escape user input for HTML email
  const safeName = escapeHtml(name);
  const safeEmail = escapeHtml(email);
  const safeCommunityName = escapeHtml(communityName);
  // Preserve line breaks in message
  const safeMessage = escapeHtml(message).replace(/\n/g, '<br>');

  // Email content
  const mailOptions = {
    from: `"NeibrPay Contact Form" <${config.smtpUser}>`,
    to: config.supportEmail,
    replyTo: email,
    subject: `Contact Form Submission from ${communityName}`,
    html: `
      <!DOCTYPE html>
      <html>
        <head>
          <meta charset="utf-8">
          <style>
            body {
              font-family: Arial, sans-serif;
              line-height: 1.6;
              color: #333;
            }
            .container {
              max-width: 600px;
              margin: 0 auto;
              padding: 20px;
            }
            .header {
              background-color: #00C27A;
              color: white;
              padding: 20px;
              border-radius: 8px 8px 0 0;
            }
            .content {
              background-color: #f9fafb;
              padding: 30px;
              border-radius: 0 0 8px 8px;
            }
            .field {
              margin-bottom: 20px;
            }
            .field-label {
              font-weight: bold;
              color: #1F2937;
              margin-bottom: 5px;
            }
            .field-value {
              color: #6B7280;
              padding: 8px 12px;
              background-color: white;
              border-radius: 4px;
              border-left: 3px solid #00C27A;
            }
            .message-content {
              color: #6B7280;
              padding: 12px;
              background-color: white;
              border-radius: 4px;
              border-left: 3px solid #00C27A;
              white-space: pre-wrap;
            }
            .footer {
              margin-top: 30px;
              padding-top: 20px;
              border-top: 1px solid #E5E7EB;
              font-size: 12px;
              color: #6B7280;
            }
          </style>
        </head>
        <body>
          <div class="container">
            <div class="header">
              <h1 style="margin: 0;">New Contact Form Submission</h1>
              <p style="margin: 10px 0 0 0; opacity: 0.9;">Someone has sent you a message</p>
            </div>
            <div class="content">
              <div class="field">
                <div class="field-label">Name</div>
                <div class="field-value">${safeName}</div>
              </div>
              
              <div class="field">
                <div class="field-label">Email</div>
                <div class="field-value">
                  <a href="mailto:${safeEmail}">${safeEmail}</a>
                </div>
              </div>
              
              <div class="field">
                <div class="field-label">Community Name</div>
                <div class="field-value">${safeCommunityName}</div>
              </div>
              
              <div class="field">
                <div class="field-label">Message</div>
                <div class="message-content">${safeMessage}</div>
              </div>
              
              <div class="footer">
                <p><strong>Submitted:</strong> ${new Date().toLocaleString(
                  'en-US',
                  {
                    dateStyle: 'long',
                    timeStyle: 'short',
                  }
                )}</p>
                <p>This is an automated notification from the NeibrPay contact form.</p>
              </div>
            </div>
          </div>
        </body>
      </html>
    `,
    text: `
New Contact Form Submission

Name: ${name}
Email: ${email}
Community Name: ${communityName}

Message:
${message}

Submitted: ${new Date().toLocaleString('en-US', {
      dateStyle: 'long',
      timeStyle: 'short',
    })}

This is an automated notification from the NeibrPay contact form.
    `.trim(),
  };

  try {
    // Send email
    await transporter.sendMail(mailOptions);

    console.log('Contact Form Email Sent:', {
      name,
      email,
      communityName,
      messageLength: message.length,
      timestamp: new Date().toISOString(),
    });

    return {
      success: true,
      message: 'Your message has been sent successfully.',
    };
  } catch (error: any) {
    console.error('Failed to send contact form email:', error);

    throw createError({
      statusCode: 500,
      statusMessage: 'Failed to send email',
      data: {
        message:
          'We encountered an error processing your request. Please try again or email us directly at support@neibrpay.com',
      },
    });
  }
});
