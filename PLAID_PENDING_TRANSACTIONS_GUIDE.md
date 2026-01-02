# Plaid Pending Transactions - Complete Guide

## Overview

**Yes, Plaid provides pending transactions** and **Yes, the current implementation fully supports them**.

---

## What Are Pending Transactions?

Pending transactions are transactions that have been initiated but not yet posted (settled) by the bank.

### Timeline Example:

```
Monday 10:00 AM: User makes purchase at coffee shop
                 ↓
                 Bank sees transaction (debits from account)
                 ↓
                 Status: PENDING
                 ↓
Tuesday/Wednesday: Bank confirms and settles the transaction
                   ↓
                   Status: POSTED
```

### Why Pending Matters:

- ✅ Gives accurate view of available balance
- ✅ Shows transactions before official posting
- ✅ Helps reconcile account balances
- ✅ Important for HOA cash flow forecasting

---

## Plaid API Support for Pending

### Plaid Returns:

```json
{
  "transactions": [
    {
      "transaction_id": "txn_12345",
      "pending": true,
      "amount": 150.0,
      "date": "2024-01-08",
      "name": "STARBUCKS",
      "merchant_name": "Starbucks Coffee"
    }
  ]
}
```

**Key Point:** The `pending` field is always returned by Plaid's `/transactions/get` endpoint.

---

## Current Implementation - Full Support

### 1. Database Support

#### Table Structure

```sql
plaid_transactions table:

Column: pending (boolean)
Default: false
Index: Yes (for fast filtering)
```

#### Migration

```php
$table->boolean('pending')->default(false);
$table->index('pending');  // Indexed for performance
```

### 2. Model Support

#### PlaidTransaction Model

```php
protected $fillable = [
    'pending',  // ✅ Included
    // ... other fields
];

protected $casts = [
    'pending' => 'boolean',  // ✅ Casts to boolean
];
```

#### Query Scopes for Filtering

```php
// Get only pending transactions
$pending = PlaidTransaction::pending()->get();

// Get only posted transactions
$posted = PlaidTransaction::posted()->get();

// Get both (with status filter)
$all = PlaidTransaction::where('pending', false)->get();
```

### 3. Sync Process - Stores Pending Status

#### In PlaidService::syncTransactions()

**When creating new transaction:**

```php
PlaidTransaction::create([
    // ... other fields
    'pending' => $transaction['pending'] ?? false,  // ✅ From Plaid API
]);
```

**When updating existing transaction:**

```php
$existingTransaction->update([
    // ... other fields
    'pending' => $transaction['pending'] ?? false,  // ✅ Updated from Plaid
]);
```

**Key Feature:** Updates `pending` status on every sync, so transactions automatically change from "Pending" → "Posted" when the bank posts them.

### 4. API Endpoint - Supports Pending Filtering

#### GET /api/plaid/transactions

**Request:**

```http
GET /api/plaid/transactions?pending=true&bank_account_id=123

Query Parameters:
- pending: true (get pending only)
- pending: false (get posted only)
- pending: null or omitted (get all)
```

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "pending": true,
      "name": "STARBUCKS",
      "amount": 45.5,
      "date": "2024-01-08",
      "status": "Pending" // ✅ Shown in API
    }
  ],
  "pagination": {
    "total": 150,
    "total_amount": 5432.1
  }
}
```

**Code:**

```php
// Filter by pending status
if (isset($validated['pending'])) {
    $query->where('pending', $validated['pending']);
}
```

### 5. Frontend Display - Shows Pending Status

#### Transactions.vue

**Status Filter Dropdown:**

```vue
<select v-model="filters.pending">
  <option :value="null">All</option>
  <option :value="false">Posted</option>
  <option :value="true">Pending</option>  <!-- ✅ Supported -->
</select>
```

**Transaction Row Display:**

```vue
<td>
  <span :class="[
    'px-2 py-1 rounded text-xs font-medium',
    transaction.pending
      ? 'bg-yellow-50 text-yellow-700'      <!-- ✅ Yellow for Pending -->
      : 'bg-green-50 text-green-700',       <!-- ✅ Green for Posted -->
  ]">
    {{ transaction.pending ? 'Pending' : 'Posted' }}
  </span>
</td>
```

**Visual Representation:**

```
Pending transactions: 🟡 Yellow badge
Posted transactions:  🟢 Green badge
```

---

## Complete Flow: Pending Transactions

```
PLAID API
    ↓
Returns: pending: true/false
    ↓
PlaidService::syncTransactions()
    ↓
    ├─ Creates new: pending = $transaction['pending']
    └─ Updates existing: pending = $transaction['pending']
    ↓
Database (plaid_transactions)
    ↓
    ├─ Stores: pending = true (for pending transactions)
    └─ Stores: pending = false (for posted transactions)
    ↓
API Endpoint: GET /api/plaid/transactions?pending=true
    ↓
    ├─ Filters WHERE pending = true
    └─ Returns only pending transactions
    ↓
Frontend (Transactions.vue)
    ↓
Displays:
├─ Filter by status (All, Pending, Posted)
├─ Shows status badge (Pending/Posted)
└─ Different colors (Yellow/Green)
```

---

## Example: Real-World Scenario

### Monday: User spends $150 at Starbucks

**Plaid API Response (Monday):**

```json
{
  "transaction_id": "txn_abc123",
  "pending": true,
  "amount": 150.0,
  "date": "2024-01-08",
  "name": "STARBUCKS",
  "status": "PENDING"
}
```

**Database Storage (Monday):**

```sql
INSERT INTO plaid_transactions
  (pending, amount, date, name, ...)
VALUES
  (true, 150.00, '2024-01-08', 'STARBUCKS', ...);
```

**Frontend Display (Monday):**

```
Status: 🟡 Pending
Amount: $150.00
Merchant: Starbucks Coffee
Date: Jan 8, 2024
```

---

## Wednesday: Transaction Posts

**Plaid API Response (Wednesday):**

```json
{
  "transaction_id": "txn_abc123",
  "pending": false, // ✅ Changed to false!
  "amount": 150.0,
  "date": "2024-01-08",
  "name": "STARBUCKS",
  "status": "POSTED"
}
```

**Sync Update (Wednesday):**

```php
$existingTransaction->update([
    'pending' => false,  // ✅ Updated!
    // ... other fields
]);
```

**Database Update (Wednesday):**

```sql
UPDATE plaid_transactions
SET pending = false
WHERE transaction_id = 'txn_abc123';
```

**Frontend Display (Wednesday):**

```
Status: 🟢 Posted
Amount: $150.00
Merchant: Starbucks Coffee
Date: Jan 8, 2024
```

---

## API Query Examples

### Get All Pending Transactions

```http
GET /api/plaid/transactions?pending=true
```

Response shows all pending transactions with yellow status badge.

### Get Only Posted Transactions

```http
GET /api/plaid/transactions?pending=false
```

Response shows only posted transactions with green status badge.

### Get All Transactions (Both Pending & Posted)

```http
GET /api/plaid/transactions
```

Response shows all, filtered by the `Status` dropdown.

### Filter by Account and Status

```http
GET /api/plaid/transactions?bank_account_id=123&pending=true
```

Shows pending transactions for specific account only.

---

## Database Queries

### Using Query Scopes

```php
// Get pending transactions only
$pending = PlaidTransaction::forTenant($tenantId)
    ->pending()
    ->get();

// Get posted transactions only
$posted = PlaidTransaction::forTenant($tenantId)
    ->posted()
    ->get();

// Get both with manual filter
$all = PlaidTransaction::forTenant($tenantId)
    ->get();
```

### Manual Queries

```php
// Pending only
$pending = PlaidTransaction::where('pending', true)->get();

// Posted only
$posted = PlaidTransaction::where('pending', false)->get();

// Count pending
$count = PlaidTransaction::where('pending', true)->count();

// Total pending amount
$amount = PlaidTransaction::where('pending', true)->sum('amount');
```

---

## UI/UX Implementation

### Status Filter in Transactions Page

**Current Implementation (Settings.vue):**

```vue
<select v-model="filters.pending" @change="currentPage = 1" class="input-field">
  <option :value="null">All</option>
  <option :value="false">Posted</option>
  <option :value="true">Pending</option>
</select>
```

### Status Badge

**Current Implementation (Transactions.vue):**

```vue
<span
  :class="[
    'px-2 py-1 rounded text-xs font-medium',
    transaction.pending
      ? 'bg-yellow-50 text-yellow-700'
      : 'bg-green-50 text-green-700',
  ]"
>
  {{ transaction.pending ? 'Pending' : 'Posted' }}
</span>
```

---

## Features Enabled by Pending Support

### 1. **Cash Flow Forecasting**

```
Total Balance = Posted Amount + Pending Amount
Example: $5,000 posted + $500 pending = $5,500 available
```

### 2. **Pending Transaction Alerts**

```
HOA Manager can see: "You have $1,200 in pending transactions"
```

### 3. **Accurate Reconciliation**

```
Posted: ✓ Confirmed by bank
Pending: ⏳ In progress, not yet confirmed
```

### 4. **Transaction Status Tracking**

```
User can see exactly when transactions change from:
Pending (🟡) → Posted (🟢)
```

---

## 30-Day Lookback Window Advantage

The current implementation uses a **30-day lookback window** specifically to capture pending transaction transitions:

```
Day 1: Transaction becomes pending
  └─ Synced in next Monday's sync ✅

Days 2-7: Transaction remains pending
  └─ Pending field stays true ✅

Days 8-30: Transaction posts (bank settles)
  └─ Pending field updated to false ✅
  └─ Within 30-day window, so captured ✅

Beyond 30 days:
  └─ Only new transactions or changes sync
  └─ Already posted transactions don't change anyway
```

---

## Summary: Pending Transaction Support

| Feature              | Supported | Details                              |
| -------------------- | --------- | ------------------------------------ |
| **Plaid API**        | ✅ YES    | Returns `pending` field              |
| **Database**         | ✅ YES    | `pending` boolean column with index  |
| **Model**            | ✅ YES    | `pending` in fillable & casts        |
| **Query Scopes**     | ✅ YES    | `.pending()` and `.posted()` methods |
| **API Filtering**    | ✅ YES    | `?pending=true/false` parameter      |
| **Sync Updates**     | ✅ YES    | Updates status on every sync         |
| **Frontend Display** | ✅ YES    | Yellow (Pending) vs Green (Posted)   |
| **Status Filter**    | ✅ YES    | Dropdown to filter by status         |
| **Auto-Transitions** | ✅ YES    | Pending → Posted when bank posts     |

---

## Best Practices

### 1. **Always Monitor Pending Transactions**

Pending transactions should transition to posted within 3-5 days for most transactions.

### 2. **Use 30-Day Lookback**

Currently implemented to catch pending → posted transitions.

### 3. **Weekly Sync Catches Transitions**

The Monday sync will update pending transactions that posted during the previous week.

### 4. **Don't Ignore Pending**

These are real money movements, even if not officially posted yet.

---

## Conclusion

**Yes, Plaid provides pending transactions and the implementation fully supports them:**

1. ✅ **Stored in database** - `pending` boolean field
2. ✅ **Synced from Plaid** - Updated on every sync
3. ✅ **Queryable** - With scopes and filters
4. ✅ **Filterable via API** - `?pending=true/false`
5. ✅ **Displayed in UI** - Status badge with color coding
6. ✅ **Auto-updating** - Changes to posted when bank settles

The system is production-ready for handling pending transactions!
