-- Remove Salinity and Turbidity Parameters from Water Monitoring System
-- Execute this script to revert back to the original 3 parameters

-- Remove the salinity and turbidity columns from sensor_data table
ALTER TABLE sensor_data 
DROP COLUMN salinity,
DROP COLUMN turbidity;

-- Optional: Remove any sample data that might have been added for these parameters
-- (This step is automatically handled when columns are dropped)

-- Verify the table structure after removal
DESCRIBE sensor_data;

-- The table should now only have:
-- - id (Primary Key)
-- - temperature (DECIMAL 5,2)
-- - ph (DECIMAL 4,2) 
-- - dissolved_oxygen (DECIMAL 5,2)
-- - timestamp (TIMESTAMP)
-- - status (VARCHAR 20)
