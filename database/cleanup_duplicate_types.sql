-- Clean up duplicate Chart of Account Types
-- This script removes duplicates and keeps only singular forms

-- First, let's see what we have
SELECT id, name, created_at FROM chart_of_account_types ORDER BY name;

-- Delete any remaining plural forms (if migration didn't catch them all)
DELETE FROM chart_of_account_types WHERE name IN ('Assets', 'Liabilities', 'Expenses', 'Revenues', 'Equities', 'Income');

-- Verify only singular forms remain
SELECT id, name FROM chart_of_account_types ORDER BY name;
