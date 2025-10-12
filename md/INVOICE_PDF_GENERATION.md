# Invoice PDF Generation System

## Overview

The NeibrPay system implements a dual-approach PDF generation system for invoices, providing both immediate client-side preview capabilities and persistent server-side storage with versioning.

## Architecture

### 1. Frontend PDF Generation (Client-Side)

**Purpose**: Immediate PDF preview, download, and printing during invoice creation

**Location**: `apps/admin-web/src/views/AddInvoice.vue`

**Technology Stack**:

- `html2canvas` - Captures DOM elements as canvas
- `jsPDF` - Converts canvas to PDF format

**Process Flow**:

1. User fills out invoice form
2. `InvoiceTemplate.vue` renders live preview
3. User clicks PDF action buttons:
   - **Preview PDF**: Opens in new tab
   - **Download PDF**: Saves to device
   - **Print PDF**: Opens print dialog

**Key Features**:

- High-quality rendering (2x scale)
- Multi-page support with automatic page breaks
- Custom filename generation
- Real-time preview updates

**Code Example**:

```typescript
const generatePDFPreview = async () => {
  const canvas = await html2canvas(element, { scale: 2 });
  const pdf = new jsPDF('p', 'mm', 'a4');
  // Multi-page logic with automatic breaks
  pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
  // Open in new tab
  window.open(pdfUrl, '_blank');
};
```

### 2. Backend PDF Generation (Server-Side)

**Purpose**: Persistent PDF storage with versioning and audit trail

**Location**: `backend/laravel/app/Services/InvoicePdfService.php`

**Technology Stack**:

- `Barryvdh\DomPDF` - Laravel PDF generation package
- Laravel Storage - File system management
- Database versioning system

**Process Flow**:

1. Invoice created via API
2. HTML generated from invoice data
3. PDF created using DomPDF
4. File stored with versioning
5. Database record created

**Key Features**:

- PDF versioning system
- File storage management
- Audit trail (who generated, when)
- Automatic regeneration on status changes

**Code Example**:

```php
public function generatePdf(InvoiceUnit $invoice, string $html, int $generatedBy): InvoicePdf
{
    $version = InvoicePdf::getNextVersion($invoice->id);
    $pdf = Pdf::loadHTML($html);
    $pdf->setPaper('A4', 'portrait');
    $pdfContent = $pdf->output();

    $invoicePdf = InvoicePdf::create([
        'invoice_unit_id' => $invoice->id,
        'version' => $version,
        'file_name' => $filename,
        'file_path' => $filePath,
        'is_latest' => true,
        'generated_by' => $generatedBy,
    ]);

    return $invoicePdf;
}
```

## API Endpoints

### PDF Management Routes

```php
// Generate PDF from HTML
POST /api/invoices/{invoice}/pdf/generate

// Get latest PDF info
GET /api/invoices/{invoice}/pdf/info

// Download PDF
GET /api/invoices/{invoice}/pdf/download

// Get PDF versions
GET /api/invoices/{invoice}/pdf/versions

// Download specific version
GET /api/invoices/{invoice}/pdf/versions/{version}/download
```

### Request/Response Examples

**Generate PDF**:

```json
POST /api/invoices/123/pdf/generate
{
  "html": "<!DOCTYPE html>..."
}

Response:
{
  "data": {
    "id": 1,
    "version": 1,
    "file_name": "INV-001.pdf",
    "file_path": "invoices/INV-001.pdf",
    "is_latest": true,
    "generated_by": 1
  },
  "message": "PDF generated successfully"
}
```

## HTML Template System

### Frontend Template

**File**: `apps/admin-web/src/components/InvoiceTemplate.vue`

**Features**:

- Vue.js reactive component
- A4 page dimensions (210mm x 297mm)
- Professional invoice layout
- Company branding section
- Itemized billing table
- Tax and discount calculations
- Payment information section

### Backend Template

**Location**: `backend/laravel/app/Http/Controllers/Api/InvoiceController.php`

**Method**: `generateInvoiceHtmlWithPayment()`

**Features**:

- Similar HTML structure to frontend
- Embedded CSS for consistent styling
- Payment details integration
- "PAID" stamp for completed invoices
- Dynamic content generation

## PDF Versioning System

### Database Schema

**Model**: `InvoicePdf`

**Fields**:

- `invoice_unit_id` - Reference to invoice
- `version` - Sequential version number
- `file_name` - Generated filename
- `file_path` - Storage path
- `file_size` - File size in bytes
- `is_latest` - Boolean flag for current version
- `generated_by` - User ID who generated
- `created_at` - Generation timestamp

### Version Management

- Each invoice can have multiple PDF versions
- Only one version marked as "latest"
- Previous versions archived but preserved
- Automatic version incrementing
- Audit trail for all generations

## Integration Points

### Invoice Creation Flow

1. **Frontend**: User creates invoice → Live preview available
2. **Backend**: Invoice saved to database → Ready for PDF generation
3. **API**: PDF can be generated on-demand via API

### Invoice Status Changes

1. **Mark as Paid**: Automatically regenerates PDF with payment details
2. **Update Invoice**: New PDF version created
3. **Version History**: All versions preserved for audit

### API Client Integration

**File**: `packages/api-client/src/invoices.ts`

```typescript
// Generate PDF from HTML
async generateInvoicePdf(invoiceId: number, html: string): Promise<any> {
  const response = await apiClient.post(
    `/invoices/${invoiceId}/pdf/generate`,
    { html }
  );
  return response.data.data;
}

// Get latest PDF info
async getLatestInvoicePdf(invoiceId: number): Promise<any> {
  const response = await apiClient.get(`/invoices/${invoiceId}/pdf/info`);
  return response.data;
}
```

## File Structure

```
apps/admin-web/
├── src/
│   ├── components/
│   │   └── InvoiceTemplate.vue          # Frontend PDF template
│   ├── views/
│   │   └── AddInvoice.vue               # PDF generation logic
│   └── composables/
│       └── useInvoices.ts               # API integration

backend/laravel/
├── app/
│   ├── Http/Controllers/Api/
│   │   ├── InvoiceController.php        # Invoice CRUD + HTML generation
│   │   └── InvoicePdfController.php     # PDF management endpoints
│   ├── Services/
│   │   └── InvoicePdfService.php        # PDF generation service
│   └── Models/
│       └── InvoicePdf.php               # PDF versioning model

packages/api-client/
└── src/
    └── invoices.ts                      # API client methods
```

## Dependencies

### Frontend

```json
{
  "jspdf": "^2.5.1",
  "html2canvas": "^1.4.1"
}
```

### Backend

```json
{
  "barryvdh/laravel-dompdf": "^2.0"
}
```

## Usage Examples

### Frontend PDF Generation

```typescript
// In AddInvoice.vue
const downloadPDF = async () => {
  const element = document.getElementById('invoice-preview');
  const canvas = await html2canvas(element, { scale: 2 });
  const pdf = new jsPDF('p', 'mm', 'a4');

  // Generate filename
  const fileName = `invoice-${form.value.invoice_number}-${new Date().toISOString().split('T')[0]}.pdf`;
  pdf.save(fileName);
};
```

### Backend PDF Generation

```php
// In InvoiceController.php
public function markAsPaid(Request $request, InvoiceUnit $invoiceUnit): JsonResponse
{
    // Update invoice status
    $invoiceUnit->update(['status' => 'paid']);

    // Regenerate PDF with payment details
    $html = $this->generateInvoiceHtmlWithPayment($invoiceUnit, $payment);
    $this->pdfService->generatePdf($invoiceUnit, $html, $user->id);

    return response()->json(['message' => 'Invoice marked as paid']);
}
```

## Benefits

### Dual Approach Advantages

1. **Immediate Feedback**: Users get instant PDF preview
2. **Persistent Storage**: Server-side PDFs for long-term access
3. **Version Control**: Complete audit trail of PDF changes
4. **Consistency**: Same template used for both approaches
5. **Flexibility**: Can generate PDFs on-demand or automatically

### Technical Benefits

1. **Scalability**: Client-side reduces server load
2. **Reliability**: Server-side ensures PDF availability
3. **Audit Compliance**: Version history for regulatory requirements
4. **User Experience**: Fast preview with reliable storage

## Future Enhancements

1. **Template Customization**: Allow users to customize PDF templates
2. **Batch PDF Generation**: Generate multiple PDFs at once
3. **PDF Signing**: Digital signature integration
4. **Email Integration**: Automatic PDF emailing
5. **Cloud Storage**: Integration with cloud storage providers
6. **PDF Analytics**: Track PDF access and download patterns
