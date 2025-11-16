-- Update districts to "Outside Dhaka" for delivery charge purposes
-- Run this SQL query on your live database

UPDATE cities 
SET state = 'Outside Dhaka' 
WHERE name IN (
    'Gazipur',
    'Narsingdi',
    'Narayanganj',
    'Tangail',
    'Kishoreganj',
    'Manikganj',
    'Munshiganj',
    'Rajbari',
    'Faridpur',
    'Gopalganj',
    'Madaripur',
    'Shariatpur'
)
AND state = 'Dhaka'
AND country = 'Bangladesh';

