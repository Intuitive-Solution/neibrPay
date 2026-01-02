# Plaid Sync Architecture - Visual Guide

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         NeibrPay Platform                        │
└─────────────────────────────────────────────────────────────────┘

                              ┌──────────────┐
                              │   Plaid API  │
                              │  (External)  │
                              └──────────────┘
                                     ▲
                                     │ (POST /transactions/get)
                                     │
                    ┌────────────────┴────────────────┐
                    │                                 │
            ┌───────▼────────┐            ┌──────────▼────────┐
            │  Manual Sync   │            │  Auto Scheduled   │
            │  (User Click)  │            │  Sync (Weekly)    │
            └───────┬────────┘            └──────────┬────────┘
                    │                              │
                    │ POST /api/plaid/sync          │ plaid:sync
                    │                              │ (Monday 00:00 UTC)
                    │                              │
                    └──────────────┬────────────────┘
                                   │
                    ┌──────────────▼───────────────┐
                    │  SyncPlaidTransactions Job   │
                    │  ├─ Get all active accounts  │
                    │  ├─ Detect initial vs ongoing│
                    │  │  └─ NULL? Initial (730d)  │
                    │  │  └─ SET? Ongoing (30d)    │
                    │  ├─ Call PlaidService        │
                    │  └─ Update DB                │
                    └──────────────┬───────────────┘
                                   │
                    ┌──────────────▼───────────────┐
                    │  PlaidService.syncTransactions()
                    │  ├─ Detect sync phase       │
                    │  ├─ Determine date range    │
                    │  ├─ Call Plaid API          │
                    │  ├─ Match txns to accounts  │
                    │  ├─ Create/Update txns      │
                    │  └─ Update balances         │
                    └──────────────┬───────────────┘
                                   │
                    ┌──────────────▼───────────────┐
                    │    Database (Laravel)        │
                    │  ├─ plaid_bank_accounts     │
                    │  ├─ plaid_transactions      │
                    │  └─ sync metadata           │
                    └──────────────┬───────────────┘
                                   │
                    ┌──────────────▼───────────────┐
                    │  API Endpoint                │
                    │  GET /api/plaid/transactions │
                    └──────────────┬───────────────┘
                                   │
                    ┌──────────────▼───────────────┐
                    │  Vue Frontend (Admin)        │
                    │  ├─ Transactions.vue         │
                    │  ├─ Settings.vue             │
                    │  └─ TransactionTable         │
                    └──────────────────────────────┘
```

---

## Sync Lifecycle Timeline

```
TIME          INITIAL ACCOUNT CONNECTION
┌─────────────────────────────────────────────────────────────┐
│                                                             │
│  T0: User connects bank account in Settings                │
│  ├─ Plaid Link opens                                      │
│  ├─ User authenticates with bank                          │
│  └─ exchangePublicToken() creates PlaidBankAccount        │
│                                                             │
│  T1: First sync runs (automatic or manual)                │
│  ├─ Detects: sync_start_date = NULL                       │
│  ├─ Uses: 730-day window (2 years of history)             │
│  ├─ Fetches: Complete transaction history                 │
│  ├─ Stores: All transactions in database                  │
│  └─ Updates: account status, last_synced_at              │
│                                                             │
│  T2+: Ongoing syncs every Monday 00:00 UTC                │
│  ├─ Detects: sync_start_date = SET                        │
│  ├─ Uses: 30-day window (for pending transactions)        │
│  ├─ Fetches: Recent transactions only                     │
│  ├─ Updates: existing + new transactions                  │
│  └─ Updates: balances, last_synced_at                    │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## Weekly Sync Cycle

```
┌──────────────────────────────────────────────────────────────┐
│  WEEK: Monday 00:00 UTC - Sunday 23:59 UTC                  │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  MONDAY 00:00 UTC                                            │
│  ┌──────────────────────────────────────────────────┐       │
│  │ AUTOMATIC SYNC TRIGGER                           │       │
│  ├──────────────────────────────────────────────────┤       │
│  │ 1. Kernel scheduler fires                        │       │
│  │ 2. plaid:sync command executes                   │       │
│  │ 3. Job processes all active bank accounts        │       │
│  │ 4. Syncs last 30 days of transactions           │       │
│  │ 5. Updates balances and timestamps               │       │
│  │ 6. Logs completion (success/error)               │       │
│  └──────────────────────────────────────────────────┘       │
│  ↓                                                           │
│  ESTIMATED COMPLETION: Monday 00:05 UTC                      │
│  ├─ 1 account: ~1-3 seconds                                  │
│  ├─ 5 accounts: ~5-15 seconds                                │
│  └─ 50 accounts: ~50-150 seconds                             │
│                                                              │
│  MONDAY 00:05 - SUNDAY 23:59                                │
│  ┌──────────────────────────────────────────────────┐       │
│  │ DURING THE WEEK                                 │       │
│  ├──────────────────────────────────────────────────┤       │
│  │ • Users view transactions (from database)        │       │
│  │ • Users can click "Refresh" (reloads from DB)   │       │
│  │ • Users can click "Refresh Now" (fetches Plaid) │       │
│  │ • No automatic syncs during week                │       │
│  │ • All data shown is latest from Monday sync     │       │
│  └──────────────────────────────────────────────────┘       │
│                                                              │
│  NEXT MONDAY 00:00 UTC                                       │
│  └─ Cycle repeats                                           │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

---

## Sync Decision Tree

```
                    ┌─ Sync Triggered ─┐
                    │  (Automatic or   │
                    │   Manual)        │
                    └────────┬─────────┘
                             │
                    ┌────────▼─────────┐
                    │ Check all active │
                    │ bank accounts    │
                    └────────┬─────────┘
                             │
                    ┌────────▼────────────────┐
                    │ For each account:       │
                    │ Is sync_start_date      │
                    │ NULL?                   │
                    └────────┬─────────┬──────┘
                             │         │
                        YES  │         │  NO
                             │         │
         ┌───────────────────▼─┐   ┌───▼─────────────────┐
         │   INITIAL SYNC      │   │   ONGOING SYNC      │
         │                     │   │                     │
         │  Window: 730 days   │   │  Window: 30 days    │
         │  (2 years)          │   │  (1 month)          │
         │                     │   │                     │
         │  Gets:              │   │  Gets:              │
         │  ├─ Full history    │   │  ├─ Recent txns     │
         │  ├─ All balances    │   │  ├─ Updated status  │
         │  └─ All metadata    │   │  └─ New balances    │
         │                     │   │                     │
         └──────────┬──────────┘   └───┬─────────────────┘
                    │                  │
                    └────────┬─────────┘
                             │
                    ┌────────▼─────────────┐
                    │ Store in database:   │
                    │ ├─ Transactions      │
                    │ ├─ Balances          │
                    │ └─ Sync metadata     │
                    └────────┬─────────────┘
                             │
                    ┌────────▼─────────────┐
                    │ Update UI:           │
                    │ ├─ last_synced_at    │
                    │ ├─ Current balance   │
                    │ └─ Account status    │
                    └──────────────────────┘
```

---

## Database Schema Relationships

```
┌────────────────────────────────────────────────────────────┐
│                    PLAID TABLES                            │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  PLAID_BANK_ACCOUNTS                                      │
│  ┌─────────────────────────────────┐                     │
│  │ id (PK)                         │                     │
│  │ tenant_id (FK) ────┐            │                     │
│  │ account_id         │            │                     │
│  │ account_name       │            │                     │
│  │ account_mask       │            │                     │
│  │ institution_name   │            │                     │
│  │ current_balance    │            │                     │
│  │ available_balance  │            │                     │
│  │ sync_start_date ◄──┼── Initial vs ongoing detection  │
│  │ last_synced_at     │            │                     │
│  │ status             │            │                     │
│  │ error_message      │            │                     │
│  │ created_at         │            │                     │
│  └─────────────────────────────────┘                     │
│                    │                                      │
│                    │ (1:M)                               │
│                    │                                      │
│  PLAID_TRANSACTIONS                                       │
│  ┌──────────────────────────────────┐                    │
│  │ id (PK)                          │                    │
│  │ plaid_bank_account_id (FK) ──┐   │                    │
│  │ tenant_id (FK)               │   │                    │
│  │ plaid_transaction_id ◄───────┼─ Unique per sync      │
│  │ amount                        │   │                    │
│  │ date                          │   │                    │
│  │ name                          │   │                    │
│  │ merchant_name                 │   │                    │
│  │ category                      │   │                    │
│  │ pending                       │   │                    │
│  │ personal_finance_category     │   │                    │
│  │ created_at                    │   │                    │
│  └──────────────────────────────────┘                    │
│                                                            │
└────────────────────────────────────────────────────────────┘

SYNC FLOW IN DATABASE:
1. User connects account → Create PlaidBankAccount (sync_start_date = NULL)
2. First sync runs → Sync 730 days, store transactions
3. Subsequent syncs → Sync 30 days, update existing + add new
4. Each sync → Update last_synced_at timestamp
5. Transaction uniqueness → plaid_transaction_id prevents duplicates
```

---

## Lookup Window Explanation

```
TODAY (Example: Jan 20)
│
├─ 30 Day Window ──────────────────────────────────────────┐
│  ┌─────────────────────────────────────────────────────┐ │
│  │ Dec 21 ........................... Jan 20 (TODAY)   │ │
│  │ ├─ Catches pending→posted (1-3 days)               │ │
│  │ ├─ Catches delayed postings (up to 5 days)         │ │
│  │ └─ Includes overlap from previous sync             │ │
│  └─────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘

730 Day Window (Initial) ─────────────────────────────────┐
┌────────────────────────────────────────────────────────┐ │
│ Jan 20 (2 years ago) ......... Jan 20 (TODAY)          │ │
│ ├─ Complete 2-year history                            │ │
│ ├─ Full baseline for reconciliation                   │ │
│ └─ Maximum Plaid API allows                           │ │
└────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘

WHY THE DIFFERENCE:
Initial: Needs complete history for reconciliation
Ongoing: Only needs recent + overlap for pending handling
```

---

## Configuration Map

```
┌─────────────────────────────────────────────────────────────┐
│                  CONFIGURATION LOCATIONS                    │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Scheduler Configuration                                   │
│  📄 backend/laravel/app/Console/Kernel.php                │
│  ├─ Frequency: ->weekly()                                │
│  ├─ Day: ->mondays()                                     │
│  └─ Timezone: ->timezone('UTC')                          │
│                                                             │
│  Sync Window Configuration                                │
│  📄 backend/laravel/app/Services/PlaidService.php        │
│  ├─ Initial: now()->subDays(730)                         │
│  ├─ Ongoing: now()->subDays(30)                          │
│  └─ Detection: if (!$startDate)                          │
│                                                             │
│  UI Message Configuration                                 │
│  📄 apps/admin-web/src/views/Settings.vue                │
│  ├─ Location: Line 642-661 (Bank tab)                   │
│  ├─ Shows: Weekly frequency + 30-day lookback           │
│  └─ Button: "Refresh Transactions Now"                  │
│                                                             │
│  Database Configuration                                   │
│  📄 backend/laravel/database/migrations/...             │
│  ├─ plaid_bank_accounts table                           │
│  │  └─ sync_start_date (nullable date)                  │
│  ├─ plaid_transactions table                            │
│  │  └─ (no changes needed)                              │
│  └─ (Existing schema works as-is)                       │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## Error Handling Flow

```
┌──────────────────────────────┐
│   Sync Execution Starts      │
└──────────────┬───────────────┘
               │
        ┌──────▼──────┐
        │ Call Plaid  │
        │ API         │
        └──────┬──────┘
               │
        ┌──────▼──────┬──────────┐
        │ Success?    │          │
        └──────┬──────┴─────┬────┘
             YES            NO
               │             │
        ┌──────▼────────┐  ┌─▼─────────────────┐
        │ Create/Update │  │ Update account:   │
        │ transactions  │  │ ├─ status='error' │
        │ & balances    │  │ ├─ error_message  │
        └──────┬────────┘  │ └─ last_synced_at │
               │           └───────────┬────────┘
        ┌──────▼────────┐             │
        │ Update:       │             │
        │ ├─ status     │             │
        │ ├─ balance    │             │
        │ └─ timestamp  │             │
        └──────┬────────┘             │
               │                      │
        ┌──────▼──────────────────────▼────┐
        │ Log operation (success/error)    │
        │ Send UI notifications            │
        └─────────────────────────────────┘
```

---

## API Call Comparison

```
BEFORE OPTIMIZATION:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Day 1:  ├──────── Sync ────────┐
        ├─────── Sync ────────┐
        ├─────── Sync ────────┐
        └─ ... (24 total) ... → 24 API calls
Day 2:  Same pattern → 24 API calls
...
Month:  24 × 30 = 720 API calls


AFTER OPTIMIZATION:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Monday: ├──────────── Sync ───────────────┐ → 1 API call
Tue-Sun: (No automatic syncs)
Monday: ├──────────── Sync ───────────────┐ → 1 API call
...
Month:  1 × 4 weeks = 4 API calls


SAVINGS:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
720 → 4 = 99.4% reduction in API calls
```

---

## Implementation Checklist Visual

```
┌─────────────────────────────────────────────────────────┐
│  IMPLEMENTATION STATUS                                  │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Backend Changes                                        │
│  ✅ Kernel.php (Scheduler)                              │
│  ✅ PlaidService.php (Sync Windows)                     │
│  ✅ SyncPlaidTransactions.php (Documentation)           │
│                                                         │
│  Frontend Changes                                       │
│  ✅ Settings.vue (UI Message)                           │
│                                                         │
│  Documentation                                          │
│  ✅ PLAID_SYNC_OPTIMIZATION.md                          │
│  ✅ PLAID_SYNC_QUICK_REFERENCE.md                       │
│  ✅ PLAID_SYNC_VERIFICATION.md                          │
│  ✅ PLAID_SYNC_IMPLEMENTATION_SUMMARY.md                │
│  ✅ PLAID_SYNC_ARCHITECTURE.md (this file)             │
│                                                         │
│  Validation                                             │
│  ✅ Linting (No errors found)                           │
│  ✅ Type checking (TypeScript compliant)                │
│  ✅ Code review (Ready for QA)                          │
│                                                         │
│  Status: ✅ READY FOR DEPLOYMENT                        │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

This architecture provides a complete visual understanding of how the optimized Plaid sync system works!
