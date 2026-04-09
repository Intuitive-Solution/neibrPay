import nodemailer from 'nodemailer';

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

  const { name, phone, email, communityName, numberOfUnits } = body;

  if (!name || !phone || !email || !communityName || !numberOfUnits) {
    throw createError({
      statusCode: 400,
      statusMessage: 'All fields are required',
      data: { message: 'Please fill in all required fields.' },
    });
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    throw createError({
      statusCode: 400,
      statusMessage: 'Invalid email format',
      data: { message: 'Please provide a valid email address.' },
    });
  }

  const phoneRegex = /^[+]?[\d\s\-().]{7,20}$/;
  if (!phoneRegex.test(phone)) {
    throw createError({
      statusCode: 400,
      statusMessage: 'Invalid phone format',
      data: { message: 'Please provide a valid phone number.' },
    });
  }

  const transporter = nodemailer.createTransport({
    host: config.smtpHost,
    port: parseInt(config.smtpPort),
    secure: true,
    auth: {
      user: config.smtpUser,
      pass: config.smtpPassword,
    },
  });

  const safeName = escapeHtml(name);
  const safeEmail = escapeHtml(email);
  const safePhone = escapeHtml(phone);
  const safeCommunityName = escapeHtml(communityName);
  const safeUnits = escapeHtml(String(numberOfUnits));

  const mailOptions = {
    from: `"NeibrPay Get Started" <${config.smtpUser}>`,
    to: config.supportEmail,
    replyTo: email,
    subject: `New Get Started Request from ${communityName} (${numberOfUnits} units)`,
    html: `
      <!DOCTYPE html>
      <html>
        <head>
          <meta charset="utf-8">
          <style>
            body { font-family: 'Inter', Arial, sans-serif; line-height: 1.6; color: #242D38; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #00C27A; color: white; padding: 24px; border-radius: 8px 8px 0 0; }
            .header h1 { margin: 0; font-size: 22px; }
            .header p { margin: 8px 0 0; opacity: 0.9; font-size: 14px; }
            .content { background-color: #F9FAFB; padding: 32px; border-radius: 0 0 8px 8px; }
            .field { margin-bottom: 20px; }
            .field-label { font-weight: 600; color: #242D38; margin-bottom: 4px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
            .field-value { color: #6B7280; padding: 10px 14px; background-color: white; border-radius: 6px; border-left: 3px solid #00C27A; }
            .highlight { background-color: #E6FAF2; border: 1px solid #C2F4E0; border-radius: 8px; padding: 16px; margin-top: 24px; }
            .highlight p { margin: 0; font-weight: 600; color: #004D30; }
            .footer { margin-top: 24px; padding-top: 16px; border-top: 1px solid #E5E7EB; font-size: 12px; color: #6B7280; }
          </style>
        </head>
        <body>
          <div class="container">
            <div class="header">
              <h1>New Get Started Request</h1>
              <p>A potential customer wants to get started with NeibrPay</p>
            </div>
            <div class="content">
              <div class="field">
                <div class="field-label">Name</div>
                <div class="field-value">${safeName}</div>
              </div>
              <div class="field">
                <div class="field-label">Phone</div>
                <div class="field-value"><a href="tel:${safePhone}">${safePhone}</a></div>
              </div>
              <div class="field">
                <div class="field-label">Email</div>
                <div class="field-value"><a href="mailto:${safeEmail}">${safeEmail}</a></div>
              </div>
              <div class="field">
                <div class="field-label">Community Name</div>
                <div class="field-value">${safeCommunityName}</div>
              </div>
              <div class="field">
                <div class="field-label">Number of Units</div>
                <div class="field-value">${safeUnits}</div>
              </div>
              <div class="highlight">
                <p>Action required: Contact this lead within 24 hours.</p>
              </div>
              <div class="footer">
                <p><strong>Submitted:</strong> ${new Date().toLocaleString('en-US', { dateStyle: 'long', timeStyle: 'short' })}</p>
                <p>This is an automated notification from the NeibrPay website.</p>
              </div>
            </div>
          </div>
        </body>
      </html>
    `,
    text: `
New Get Started Request

Name: ${name}
Phone: ${phone}
Email: ${email}
Community Name: ${communityName}
Number of Units: ${numberOfUnits}

Action required: Contact this lead within 24 hours.

Submitted: ${new Date().toLocaleString('en-US', { dateStyle: 'long', timeStyle: 'short' })}
    `.trim(),
  };

  try {
    await transporter.sendMail(mailOptions);

    console.log('Get Started Email Sent:', {
      name,
      email,
      phone,
      communityName,
      numberOfUnits,
      timestamp: new Date().toISOString(),
    });

    return {
      success: true,
      message: 'Your request has been submitted successfully.',
    };
  } catch (error: any) {
    console.error('Failed to send get-started email:', error);

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
