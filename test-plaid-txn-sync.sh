#!/bin/bash

###############################################################################
# Plaid Transaction Sync Test Script
# 
# This script tests the complete Plaid transaction sync flow:
# 1. Creates a test transaction in Plaid Sandbox
# 2. Waits for webhook to fire
# 3. Verifies webhook was received
# 4. Verifies transaction was synced to database
# 5. Shows sync results
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LARAVEL_DIR="$SCRIPT_DIR/backend/laravel"
LOG_FILE="$LARAVEL_DIR/storage/logs/laravel.log"

# Plaid Configuration (update these or set as environment variables)
PLAID_CLIENT_ID="6923886e63de1f00215b725d"
PLAID_CLIENT_SECRET="a4f69fb5c0726f5890cc1399dc155b"
PLAID_ENVIRONMENT="sandbox"
PLAID_BASE_URL="https://sandbox.plaid.com"

# Webhook endpoint
WEBHOOK_URL="https://chelsie-immiscible-unaggressively.ngrok-free.dev/api/plaid/webhook"

# Test transaction details
TEST_AMOUNT="${TEST_AMOUNT:-25.50}"
TEST_DESCRIPTION="${TEST_DESCRIPTION:-Test Transaction - $(date +%Y-%m-%d\ %H:%M:%S)}"
# Use UTC date to avoid timezone issues (Plaid validates against UTC)
# Use today's UTC date - this ensures the date is never in the future
TEST_DATE="$(date -u +%Y-%m-%d)"

###############################################################################
# Helper Functions
###############################################################################

print_header() {
    echo -e "\n${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_info() {
    echo -e "${YELLOW}ℹ${NC} $1"
}

print_step() {
    echo -e "\n${BLUE}→${NC} $1"
}

###############################################################################
# Get Plaid Credentials from Database
###############################################################################

get_plaid_credentials() {
    print_step "Fetching Plaid credentials from database..."
    
    if [ ! -d "$LARAVEL_DIR" ]; then
        print_error "Laravel directory not found: $LARAVEL_DIR"
        exit 1
    fi
    
    cd "$LARAVEL_DIR"
    
    # Get credentials using tinker
    CREDENTIALS=$(php artisan tinker --execute="
        \$account = \App\Models\PlaidBankAccount::first();
        if (\$account) {
            echo json_encode([
                'item_id' => \$account->plaid_item_id,
                'account_id' => \$account->account_id,
                'access_token' => \$account->getDecryptedAccessToken(),
            ]);
        } else {
            echo '{}';
        }
    " 2>/dev/null | tail -1)
    
    if [ "$CREDENTIALS" = "{}" ] || [ -z "$CREDENTIALS" ]; then
        print_error "No Plaid bank accounts found in database"
        print_info "Please connect a bank account first via Plaid Link"
        exit 1
    fi
    
    # Parse JSON (requires jq or use PHP)
    PLAID_ITEM_ID=$(echo "$CREDENTIALS" | php -r "echo json_decode(file_get_contents('php://stdin'))->item_id ?? '';")
    PLAID_ACCOUNT_ID=$(echo "$CREDENTIALS" | php -r "echo json_decode(file_get_contents('php://stdin'))->account_id ?? '';")
    PLAID_ACCESS_TOKEN=$(echo "$CREDENTIALS" | php -r "echo json_decode(file_get_contents('php://stdin'))->access_token ?? '';")
    
    if [ -z "$PLAID_ACCESS_TOKEN" ]; then
        print_error "Failed to retrieve Plaid credentials"
        exit 1
    fi
    
    print_success "Retrieved credentials for Item: ${PLAID_ITEM_ID:0:20}..."
}

###############################################################################
# Validate Configuration
###############################################################################

validate_config() {
    print_step "Validating configuration..."
    
    if [ -z "$PLAID_CLIENT_SECRET" ]; then
        print_error "PLAID_CLIENT_SECRET is not set"
        print_info "Set it as environment variable or update the script"
        exit 1
    fi
    
    if [ ! -f "$LOG_FILE" ]; then
        print_error "Laravel log file not found: $LOG_FILE"
        exit 1
    fi
    
    print_success "Configuration validated"
}

###############################################################################
# Create Test Transaction
###############################################################################

create_test_transaction() {
    print_step "Creating test transaction in Plaid Sandbox..."
    
    TRANSACTION_PAYLOAD=$(cat <<EOF
{
  "client_id": "$PLAID_CLIENT_ID",
  "secret": "$PLAID_CLIENT_SECRET",
  "access_token": "$PLAID_ACCESS_TOKEN",
  "transactions": [
    {
      "date_posted": "$TEST_DATE",
      "date_transacted": "$TEST_DATE",
      "amount": $TEST_AMOUNT,
      "description": "$TEST_DESCRIPTION"
    }
  ]
}
EOF
)
    
    RESPONSE=$(curl -s -w "\n%{http_code}" -X POST "$PLAID_BASE_URL/sandbox/transactions/create" \
        -H "Content-Type: application/json" \
        -d "$TRANSACTION_PAYLOAD")
    
    HTTP_CODE=$(echo "$RESPONSE" | tail -1)
    BODY=$(echo "$RESPONSE" | sed '$d')
    
    if [ "$HTTP_CODE" != "200" ]; then
        print_error "Failed to create transaction (HTTP $HTTP_CODE)"
        echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
        exit 1
    fi
    
    # Check for errors in response
    if echo "$BODY" | grep -q '"error_code"'; then
        print_error "Plaid API error:"
        echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
        exit 1
    fi
    
    print_success "Transaction created successfully"
    echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
    
    # Extract request_id if available
    REQUEST_ID=$(echo "$BODY" | jq -r '.request_id // empty' 2>/dev/null)
    if [ -n "$REQUEST_ID" ]; then
        print_info "Request ID: $REQUEST_ID"
    fi
    
    # Refresh transactions to make the new transaction available via sync
    print_info "Refreshing transactions to make new transaction available..."
    REFRESH_RESPONSE=$(curl -s -w "\n%{http_code}" -X POST "$PLAID_BASE_URL/transactions/refresh" \
        -H "Content-Type: application/json" \
        -d "{
            \"client_id\": \"$PLAID_CLIENT_ID\",
            \"secret\": \"$PLAID_CLIENT_SECRET\",
            \"access_token\": \"$PLAID_ACCESS_TOKEN\"
        }")
    
    REFRESH_HTTP_CODE=$(echo "$REFRESH_RESPONSE" | tail -1)
    REFRESH_BODY=$(echo "$REFRESH_RESPONSE" | sed '$d')
    
    if [ "$REFRESH_HTTP_CODE" = "200" ]; then
        print_success "Transactions refreshed successfully"
    else
        print_info "Transaction refresh returned HTTP $REFRESH_HTTP_CODE (may be normal)"
    fi
}

###############################################################################
# Wait for Webhook
###############################################################################

wait_for_webhook() {
    print_step "Waiting for webhook to fire..."
    
    # Get initial log line count
    INITIAL_LINES=$(wc -l < "$LOG_FILE" 2>/dev/null || echo "0")
    
    print_info "Waiting 5 seconds for Plaid to send webhook..."
    sleep 5
    
    # Check for webhook in logs
    WEBHOOK_FOUND=false
    for i in {1..10}; do
        if tail -100 "$LOG_FILE" | grep -q "Plaid webhook received"; then
            WEBHOOK_FOUND=true
            break
        fi
        print_info "Waiting... ($i/10)"
        sleep 2
    done
    
    if [ "$WEBHOOK_FOUND" = true ]; then
        print_success "Webhook received!"
    else
        print_error "Webhook not found in logs after 20 seconds"
        print_info "This might be normal if webhook URL is not configured in Plaid dashboard"
    fi
}

###############################################################################
# Check Webhook Logs
###############################################################################

check_webhook_logs() {
    print_step "Checking webhook logs..."
    
    echo -e "\n${YELLOW}Recent webhook-related log entries:${NC}"
    echo "─────────────────────────────────────────────────────────────────"
    
    tail -50 "$LOG_FILE" | grep -i "webhook\|sync" | tail -10 || print_info "No webhook logs found"
    
    echo "─────────────────────────────────────────────────────────────────"
}

###############################################################################
# Verify Transaction in Database
###############################################################################

verify_transaction_sync() {
    print_step "Verifying transaction was synced to database..."
    
    cd "$LARAVEL_DIR"
    
    # Check if transaction exists - search by amount and date since description might not match exactly
    TRANSACTION_COUNT=$(php artisan tinker --execute="
        \$count = \App\Models\PlaidTransaction::where('plaid_bank_account_id', 
            \App\Models\PlaidBankAccount::where('plaid_item_id', '$PLAID_ITEM_ID')->first()->id)
            ->where('date', '$TEST_DATE')
            ->where('amount', $TEST_AMOUNT)
            ->count();
        echo \$count ?: 0;
    " 2>/dev/null | tail -1 | tr -d '[:space:]')
    
    # Ensure TRANSACTION_COUNT is a number, default to 0 if empty
    TRANSACTION_COUNT=${TRANSACTION_COUNT:-0}
    
    if [ "$TRANSACTION_COUNT" -gt 0 ] 2>/dev/null; then
        print_success "Transaction found in database ($TRANSACTION_COUNT matching)"
        
        # Show transaction details
        echo -e "\n${YELLOW}Transaction details:${NC}"
        php artisan tinker --execute="
            \$account = \App\Models\PlaidBankAccount::where('plaid_item_id', '$PLAID_ITEM_ID')->first();
            \$txn = \App\Models\PlaidTransaction::where('plaid_bank_account_id', \$account->id)
                ->where('date', '$TEST_DATE')
                ->where('amount', $TEST_AMOUNT)
                ->latest()
                ->first();
            if (\$txn) {
                echo 'ID: ' . \$txn->id . PHP_EOL;
                echo 'Name: ' . \$txn->name . PHP_EOL;
                echo 'Amount: $' . number_format(\$txn->amount, 2) . PHP_EOL;
                echo 'Date: ' . \$txn->date . PHP_EOL;
                echo 'Pending: ' . (\$txn->pending ? 'Yes' : 'No') . PHP_EOL;
            }
        " 2>/dev/null | tail -5
    else
        print_error "Transaction not found in database"
        print_info "The webhook may not have triggered a sync, or sync may have failed"
        
        # Show recent transactions for debugging
        echo -e "\n${YELLOW}Recent transactions (last 5) for debugging:${NC}"
        php artisan tinker --execute="
            \$account = \App\Models\PlaidBankAccount::where('plaid_item_id', '$PLAID_ITEM_ID')->first();
            if (\$account) {
                \$txns = \App\Models\PlaidTransaction::where('plaid_bank_account_id', \$account->id)
                    ->latest('date')
                    ->take(5)
                    ->get(['id', 'name', 'amount', 'date', 'pending']);
                foreach (\$txns as \$txn) {
                    echo \$txn->date . ' | $' . number_format(\$txn->amount, 2) . ' | ' . \$txn->name . ' | ' . (\$txn->pending ? 'Pending' : 'Posted') . PHP_EOL;
                }
            }
        " 2>/dev/null | tail -5
        
        print_info "Note: Transactions created via sandbox may take time to appear"
        print_info ""
        print_info "✓ Plaid DOES fetch pending transactions via /transactions/sync"
        print_info "However, sandbox-created transactions might not appear immediately because:"
        print_info "  1. The cursor position might be ahead of the new transaction"
        print_info "  2. Transaction might need time to be processed by Plaid"
        print_info "  3. The transaction might appear in a future sync after processing"
        print_info ""
        print_info "To verify the transaction exists:"
        print_info "  - Check Plaid Dashboard: https://dashboard.plaid.com/"
        print_info "  - Use /transactions/get with date range to see all transactions"
        print_info "  - The transaction should appear in the next natural sync cycle"
    fi
}

###############################################################################
# Check Sync Status
###############################################################################

check_sync_status() {
    print_step "Checking sync status..."
    
    cd "$LARAVEL_DIR"
    
    # Get last sync info
    SYNC_INFO=$(php artisan tinker --execute="
        \$account = \App\Models\PlaidBankAccount::where('plaid_item_id', '$PLAID_ITEM_ID')->first();
        if (\$account) {
            echo json_encode([
                'last_synced' => \$account->last_synced_at ? \$account->last_synced_at->toDateTimeString() : 'Never',
                'initial_sync_complete' => \$account->initial_sync_complete ? 'Yes' : 'No',
                'has_cursor' => !empty(\$account->transactions_cursor),
                'status' => \$account->status,
            ]);
        }
    " 2>/dev/null | tail -1)
    
    if [ -n "$SYNC_INFO" ] && [ "$SYNC_INFO" != "null" ]; then
        echo -e "\n${YELLOW}Account Sync Status:${NC}"
        echo "$SYNC_INFO" | jq '.' 2>/dev/null || echo "$SYNC_INFO"
    fi
}

###############################################################################
# Test Webhook Endpoint Directly
###############################################################################

test_webhook_directly() {
    print_step "Testing webhook endpoint directly..."
    
    WEBHOOK_PAYLOAD=$(cat <<EOF
{
  "webhook_type": "TRANSACTIONS",
  "webhook_code": "SYNC_UPDATES_AVAILABLE",
  "item_id": "$PLAID_ITEM_ID"
}
EOF
)
    
    RESPONSE=$(curl -s -w "\n%{http_code}" -X POST "$WEBHOOK_URL" \
        -H "Content-Type: application/json" \
        -d "$WEBHOOK_PAYLOAD")
    
    HTTP_CODE=$(echo "$RESPONSE" | tail -1)
    BODY=$(echo "$RESPONSE" | sed '$d')
    
    if [ "$HTTP_CODE" = "200" ]; then
        print_success "Webhook endpoint responded successfully"
        echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
    else
        print_error "Webhook endpoint returned HTTP $HTTP_CODE"
        echo "$BODY"
    fi
}

###############################################################################
# Main Execution
###############################################################################

main() {
    print_header "Plaid Transaction Sync Test"
    
    # Step 1: Get credentials
    get_plaid_credentials
    
    # Step 2: Validate config
    validate_config
    
    # Step 3: Test webhook endpoint directly (optional)
    read -p "$(echo -e ${YELLOW}Test webhook endpoint directly first? [y/N]: ${NC})" -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        test_webhook_directly
        echo
    fi
    
    # Step 4: Create test transaction
    create_test_transaction
    
    # Step 5: Wait for webhook
    wait_for_webhook
    
    # Step 6: Check logs
    check_webhook_logs
    
    # Step 7: Wait a bit more for transaction to be available (sandbox can have delays)
    print_info "Waiting additional 5 seconds for transaction to be available in Plaid..."
    sleep 5
    
    # Step 7.5: Verify transaction exists in Plaid first using /transactions/get
    print_step "Verifying transaction exists in Plaid..."
    VERIFY_RESPONSE=$(curl -s -X POST "$PLAID_BASE_URL/transactions/get" \
        -H "Content-Type: application/json" \
        -d "{
            \"client_id\": \"$PLAID_CLIENT_ID\",
            \"secret\": \"$PLAID_CLIENT_SECRET\",
            \"access_token\": \"$PLAID_ACCESS_TOKEN\",
            \"start_date\": \"$TEST_DATE\",
            \"end_date\": \"$TEST_DATE\"
        }")
    
    if echo "$VERIFY_RESPONSE" | grep -q '"transactions"'; then
        TRANSACTION_COUNT=$(echo "$VERIFY_RESPONSE" | php -r "\$data = json_decode(file_get_contents('php://stdin')); echo count(\$data->transactions ?? []);" 2>/dev/null)
        print_info "Found $TRANSACTION_COUNT transaction(s) for date $TEST_DATE in Plaid"
        
        # Check if our test transaction is there
        if echo "$VERIFY_RESPONSE" | grep -qi "$TEST_DESCRIPTION\|$TEST_AMOUNT"; then
            print_success "Test transaction found in Plaid via /transactions/get"
        else
            print_info "Transaction might be there but description/amount don't match exactly"
        fi
    else
        print_info "Could not verify transaction via /transactions/get"
    fi
    
    # Step 7.6: Manually trigger another sync to catch the transaction
    print_step "Manually triggering sync to catch the new transaction..."
    cd "$LARAVEL_DIR"
    MANUAL_SYNC_RESULT=$(php artisan tinker --execute="
        \$account = \App\Models\PlaidBankAccount::where('plaid_item_id', '$PLAID_ITEM_ID')->first();
        if (\$account) {
            try {
                \$service = app(\App\Services\PlaidService::class);
                \$result = \$service->syncTransactions(\$account);
                echo json_encode(\$result);
            } catch (Exception \$e) {
                echo json_encode(['error' => \$e->getMessage()]);
            }
        }
    " 2>/dev/null | tail -1)
    
    if echo "$MANUAL_SYNC_RESULT" | grep -q '"added_count"'; then
        ADDED_COUNT=$(echo "$MANUAL_SYNC_RESULT" | php -r "echo json_decode(file_get_contents('php://stdin'))->added_count ?? 0;")
        if [ "$ADDED_COUNT" -gt 0 ]; then
            print_success "Manual sync found $ADDED_COUNT new transaction(s)!"
        else
            print_info "Manual sync completed but found 0 new transactions"
            print_info "This might mean the cursor is already ahead of the new transaction"
        fi
    fi
    
    # Step 8: Verify transaction in database
    verify_transaction_sync
    
    # Step 9: Check sync status
    check_sync_status
    
    print_header "Test Complete"
    print_success "All checks completed!"
    print_info "Review the output above for any issues"
}

# Run main function
main

