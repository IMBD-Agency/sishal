-- Ensure Salary Expense Account Exists
-- This script checks if a Salary account exists and creates it if missing

-- First, check if Salary account exists
SELECT id, name, code FROM chart_of_accounts WHERE name = 'Salary';

-- If it doesn't exist, you need to create it manually via the Chart of Accounts page:
-- 1. Go to Chart of Accounts
-- 2. Click "Add Chart Account"
-- 3. Fill in:
--    - Name: Salary
--    - Code: 5001 (or next available expense code)
--    - Type: Expense
--    - Sub-Type: (select appropriate sub-type)
--    - Parent: (select appropriate parent)

-- OR run this SQL to create it (adjust IDs based on your database):
/*
INSERT INTO chart_of_accounts (name, code, type_id, sub_type_id, parent_id, created_by, created_at, updated_at)
SELECT 
    'Salary',
    '5001',
    (SELECT id FROM chart_of_account_types WHERE name = 'Expense' LIMIT 1),
    (SELECT id FROM chart_of_account_sub_types WHERE name LIKE '%Operating%' OR name LIKE '%Expense%' LIMIT 1),
    (SELECT id FROM chart_of_account_parents WHERE type_id = (SELECT id FROM chart_of_account_types WHERE name = 'Expense' LIMIT 1) LIMIT 1),
    1,
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM chart_of_accounts WHERE name = 'Salary');
*/
