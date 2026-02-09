-- Standardize Chart of Account Type Names to Singular
-- Run this SQL directly in your database to fix the inconsistency

-- Update plural to singular
UPDATE chart_of_account_types SET name = 'Asset' WHERE name = 'Assets';
UPDATE chart_of_account_types SET name = 'Liability' WHERE name = 'Liabilities';
UPDATE chart_of_account_types SET name = 'Equity' WHERE name = 'Equities';
UPDATE chart_of_account_types SET name = 'Revenue' WHERE name IN ('Revenues', 'Income');
UPDATE chart_of_account_types SET name = 'Expense' WHERE name = 'Expenses';

-- Verify the changes
SELECT * FROM chart_of_account_types;
