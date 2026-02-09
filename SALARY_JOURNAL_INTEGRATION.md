# Salary Payment Journal Entry Integration

## What Was Fixed

### Problem
Salary payments were being recorded in the `salary_payments` table but **NOT creating journal entries**. This meant:
- ❌ Salary expenses didn't appear in the Expense Report
- ❌ Salary expenses didn't appear in the Profit & Loss Report
- ❌ No double-entry accounting for salary payments

### Solution
Updated `SalaryPaymentController::store()` to create journal entries when a salary is paid.

## Journal Entry Logic

When a salary payment is created, the system now creates:

**Journal Header:**
- Voucher No: `SAL-YYYYMMDD-0001` (auto-generated)
- Type: Payment
- Entry Date: Payment date from form
- Description: "Salary payment for [Employee Name] - MM/YYYY"

**Journal Entries (Double-Entry):**

1. **Debit Entry** (Expense increases)
   - Account: Salary (Expense account)
   - Debit: Paid amount
   - Credit: 0
   - Description: "Salary expense - [Employee Name]"

2. **Credit Entry** (Asset decreases)
   - Account: Cash/Bank (selected payment account)
   - Debit: 0
   - Credit: Paid amount
   - Description: "Payment from [Account Name]"

## Requirements

### 1. Salary Account Must Exist
You **MUST** have a "Salary" account in your Chart of Accounts with:
- **Name:** Salary
- **Type:** Expense
- **Code:** 5001 (or any expense code)

**To create it:**
1. Go to Chart of Accounts page
2. Click "Add Chart Account"
3. Fill in the details above
4. Save

### 2. Test the Integration

**Create a test salary payment:**
1. Go to Salary Payments page
2. Click "Create Salary Payment"
3. Fill in:
   - Employee: Select any employee
   - Month/Year: Current month
   - Paid Amount: 50000
   - Payment Date: Today
   - Payment Account: Select Cash or Bank account
4. Submit

**Verify it appears:**
1. ✅ Go to Expense Report
2. ✅ Select "Salary" from expense type dropdown
3. ✅ You should see the salary payment listed
4. ✅ Go to Profit & Loss Report
5. ✅ Under "Debit Voucher" section, you should see the salary expense

## Files Modified

1. **SalaryPaymentController.php**
   - Added journal entry creation in `store()` method
   - Added Journal and JournalEntry model imports

## Benefits

✅ Complete double-entry accounting for salary payments
✅ Salary expenses now appear in all financial reports
✅ Proper audit trail with journal voucher numbers
✅ Consistent with other payment types (supplier payments, vouchers, etc.)

## Notes

- If the "Salary" account doesn't exist, the journal entry will NOT be created (but the salary payment record will still be saved)
- You can create multiple salary expense accounts (e.g., "Salary - Sales", "Salary - Admin") and modify the code to select the appropriate one
- Old salary payments (created before this fix) will NOT have journal entries - only new ones will
