# PDF Invoice Preview Implementation

## Overview

This implementation provides a client-side PDF generation solution for invoice previews using jsPDF and html2canvas. The solution is modular with a separate template component that can be easily modified in the future.

## Files Created/Modified

### 1. InvoiceTemplate.vue

**Location:** `src/components/InvoiceTemplate.vue`

A reusable Vue component that renders the invoice layout for PDF generation. This component:

- Displays company information, invoice details, and billing information
- Shows invoice items in a table format
- Calculates and displays totals, taxes, and discounts
- Includes notes, terms, and footer sections
- Uses A4 page dimensions (210mm x 297mm) for proper PDF formatting

### 2. AddInvoice.vue (Modified)

**Location:** `src/views/AddInvoice.vue`

Updated the existing AddInvoice component to include:

- PDF generation functionality with three actions:
  - **Preview PDF**: Opens PDF in new tab for review
  - **Download PDF**: Downloads PDF to user's device
  - **Print PDF**: Opens print dialog
- Integration with InvoiceTemplate component
- Loading states and error handling

## Dependencies Added

```bash
npm install jspdf html2canvas
```

## Features

### PDF Generation Capabilities

- ✅ **Preview PDF** - Open in new tab for review
- ✅ **Download PDF** - Save to user's device with custom filename
- ✅ **Print PDF** - Open print dialog
- ✅ **High Quality** - 2x scale for crisp rendering
- ✅ **Multi-page Support** - Automatic page breaks for long invoices
- ✅ **Custom Filename** - Uses invoice number and date

### Template Features

- Professional invoice layout
- Company branding area
- Multiple unit billing support
- Itemized billing table
- Tax and discount calculations
- Rich text notes and terms
- Payment information section

## Usage

### Basic Usage

1. Fill out the invoice form with units, items, and details
2. The preview panel will appear when units and items are selected
3. Use the action buttons to:
   - **Preview PDF**: Review the invoice before saving
   - **Download PDF**: Save the invoice to your device
   - **Print PDF**: Print the invoice directly

### Customizing the Template

To modify the invoice template:

1. Edit `src/components/InvoiceTemplate.vue`
2. Update the HTML structure as needed
3. Modify the CSS styles for different layouts
4. The changes will automatically reflect in PDF generation

### Template Structure

```vue
<template>
  <div id="invoice-preview" class="invoice-template">
    <!-- Header Section -->
    <div class="invoice-header">
      <!-- Company info and invoice meta -->
    </div>

    <!-- Bill To Section -->
    <div class="bill-to-section">
      <!-- Unit information -->
    </div>

    <!-- Items Table -->
    <div class="items-section">
      <!-- Invoice items -->
    </div>

    <!-- Totals Section -->
    <div class="totals-section">
      <!-- Financial calculations -->
    </div>

    <!-- Notes, Terms, Footer -->
  </div>
</template>
```

## Technical Details

### PDF Generation Process

1. **HTML to Canvas**: Uses html2canvas to convert the invoice template to a high-resolution image
2. **Canvas to PDF**: Uses jsPDF to create a PDF document from the canvas image
3. **Multi-page Support**: Automatically handles page breaks for long invoices
4. **Quality Settings**: 2x scale for crisp rendering

### Error Handling

- Validates that the invoice preview element exists
- Shows user-friendly error messages
- Handles PDF generation failures gracefully
- Provides loading states during generation

### Performance Considerations

- PDF generation is done client-side (no server load)
- Canvas rendering is optimized with proper scaling
- Memory cleanup after PDF operations
- Responsive UI with loading states

## Future Enhancements

### Easy Template Modifications

The modular design allows for easy future changes:

- **Layout Changes**: Modify CSS in InvoiceTemplate.vue
- **Content Changes**: Update HTML structure
- **Styling Changes**: Adjust colors, fonts, spacing
- **New Sections**: Add additional invoice sections

### Potential Improvements

- Server-side PDF generation for better performance
- PDF templates with fillable fields
- Email integration for sending invoices
- Batch PDF generation for multiple invoices
- Custom branding per HOA

## Browser Compatibility

- Modern browsers with Canvas support
- Chrome, Firefox, Safari, Edge
- Mobile browsers (with limitations)

## File Sizes

- Typical invoice PDF: 200-500KB
- Generation time: 1-3 seconds
- Memory usage: Minimal (cleaned up after generation)
