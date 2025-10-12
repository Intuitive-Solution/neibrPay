# Invoice API Documentation

## Overview

The Invoice API provides endpoints for managing invoices on a per-unit basis. Each invoice is associated with a specific unit and can contain multiple items stored as JSON.

## Base URL

```
/api/invoices
```

## Authentication

All endpoints require Firebase authentication via the `firebase.auth` middleware.

## Endpoints

### 1. List Invoices

**GET** `/api/invoices`

Query Parameters:

- `include_deleted` (boolean): Include soft-deleted invoices
- `unit_id` (integer): Filter by unit ID
- `status` (string): Filter by status (draft, sent, paid, overdue, cancelled)

Response:

```json
{
  "data": [
    {
      "id": 1,
      "tenant_id": 1,
      "unit_id": 1,
      "invoice_number": "Villa43-2025-10-001",
      "po_number": null,
      "frequency": "monthly",
      "start_date": "2025-10-08",
      "remaining_cycles": "endless",
      "due_date": "net_30",
      "discount_amount": "0.00",
      "discount_type": "amount",
      "auto_bill": "disabled",
      "items": [
        {
          "name": "Monthly HOA Fee",
          "description": "Homeowners Association monthly maintenance fee",
          "unit_cost": 150.0,
          "quantity": 1,
          "line_total": 150.0
        }
      ],
      "subtotal": "150.00",
      "tax_rate": "10.00",
      "tax_amount": "15.00",
      "total": "165.00",
      "paid_to_date": "0.00",
      "balance_due": "165.00",
      "status": "draft",
      "parent_invoice_id": null,
      "created_by": 1,
      "created_at": "2025-10-08T09:30:00.000000Z",
      "updated_at": "2025-10-08T09:30:00.000000Z",
      "unit": {
        "id": 1,
        "title": "Villa43",
        "address": "123 Main St",
        "city": "Anytown",
        "state": "CA"
      },
      "creator": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com"
      }
    }
  ],
  "meta": {
    "total": 1,
    "include_deleted": false,
    "filters": {
      "unit_id": null,
      "status": null
    }
  }
}
```

### 2. Create Invoice

**POST** `/api/invoices`

Request Body:

```json
{
  "unit_ids": [1, 2],
  "frequency": "monthly",
  "start_date": "2025-10-08",
  "remaining_cycles": "endless",
  "due_date": "net_30",
  "discount_amount": 0,
  "discount_type": "amount",
  "auto_bill": "disabled",
  "items": [
    {
      "name": "Monthly HOA Fee",
      "description": "Homeowners Association monthly maintenance fee",
      "unit_cost": 150.0,
      "quantity": 1,
      "line_total": 150.0
    }
  ],
  "tax_rate": 10,
  "notes": {
    "public_notes": "Thank you for your payment",
    "private_notes": "Internal note",
    "terms": "Payment due within 30 days",
    "footer": "Contact us for questions"
  },
  "po_number": "PO-12345"
}
```

Response:

```json
{
  "data": [
    {
      "id": 1,
      "invoice_number": "Villa43-2025-10-001",
      "total": "165.00",
      "status": "draft"
    },
    {
      "id": 2,
      "invoice_number": "Villa44-2025-10-001",
      "total": "165.00",
      "status": "draft"
    }
  ],
  "message": "Invoice(s) created successfully"
}
```

### 3. Get Invoice

**GET** `/api/invoices/{id}`

Response:

```json
{
  "data": {
    "id": 1,
    "invoice_number": "Villa43-2025-10-001",
    "unit": {
      "id": 1,
      "title": "Villa43",
      "owners": [
        {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com"
        }
      ]
    },
    "notes": [
      {
        "type": "public_notes",
        "content": "Thank you for your payment"
      }
    ],
    "payments": [],
    "schedule": {
      "next_due_date": "2025-11-08",
      "remaining_cycles": null,
      "is_active": true
    }
  }
}
```

### 4. Update Invoice

**PUT** `/api/invoices/{id}`

Request Body:

```json
{
  "po_number": "PO-12345-UPDATED",
  "items": [
    {
      "name": "Updated HOA Fee",
      "description": "Updated description",
      "unit_cost": 175.0,
      "quantity": 1,
      "line_total": 175.0
    }
  ],
  "tax_rate": 8.5,
  "notes": {
    "public_notes": "Updated public notes"
  }
}
```

### 5. Delete Invoice

**DELETE** `/api/invoices/{id}`

Response:

```json
{
  "message": "Invoice deleted successfully"
}
```

### 6. Restore Invoice

**POST** `/api/invoices/{id}/restore`

Response:

```json
{
  "data": {
    "id": 1,
    "status": "draft"
  },
  "message": "Invoice restored successfully"
}
```

### 7. Mark Invoice as Sent

**POST** `/api/invoices/{id}/mark-sent`

Response:

```json
{
  "data": {
    "id": 1,
    "status": "sent"
  },
  "message": "Invoice marked as sent"
}
```

### 8. Get Invoices for Unit

**GET** `/api/units/{unit_id}/invoices`

Response:

```json
{
  "data": [
    {
      "id": 1,
      "invoice_number": "Villa43-2025-10-001",
      "total": "165.00",
      "status": "draft",
      "created_at": "2025-10-08T09:30:00.000000Z"
    }
  ],
  "meta": {
    "unit_id": 1,
    "total": 1
  }
}
```

## Data Models

### InvoiceUnit

- `id`: Primary key
- `tenant_id`: Foreign key to tenants table
- `unit_id`: Foreign key to units table
- `invoice_number`: Unique invoice number (auto-generated)
- `po_number`: Purchase order number (optional)
- `frequency`: one-time, monthly, weekly, quarterly, yearly
- `start_date`: Invoice start date
- `remaining_cycles`: Number of remaining cycles or "endless"
- `due_date`: Payment terms (use_payment_terms, net_15, net_30, net_45, net_60, due_on_receipt)
- `discount_amount`: Discount amount
- `discount_type`: amount or percentage
- `auto_bill`: disabled, enabled, on_due_date, on_send
- `items`: JSON array of invoice items
- `subtotal`: Calculated subtotal
- `tax_rate`: Tax rate percentage
- `tax_amount`: Calculated tax amount
- `total`: Calculated total
- `paid_to_date`: Amount paid to date
- `balance_due`: Remaining balance
- `status`: draft, sent, paid, overdue, cancelled
- `parent_invoice_id`: For grouped invoices
- `created_by`: User who created the invoice

### Invoice Item Structure

```json
{
  "name": "Item name",
  "description": "Item description",
  "unit_cost": 100.0,
  "quantity": 1,
  "line_total": 100.0,
  "sort_order": 0,
  "category": "maintenance",
  "taxable": true,
  "custom_fields": {
    "service_date": "2024-01-01",
    "contractor": "ABC Services"
  }
}
```

## Error Responses

### Validation Error

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "unit_ids": ["Please select at least one unit."],
    "items.0.name": ["Item name is required."]
  }
}
```

### Not Found Error

```json
{
  "message": "Invoice not found"
}
```

### Unauthorized Error

```json
{
  "message": "Unauthenticated."
}
```

## Status Codes

- `200`: Success
- `201`: Created
- `400`: Bad Request
- `401`: Unauthorized
- `404`: Not Found
- `422`: Validation Error
- `500`: Internal Server Error
