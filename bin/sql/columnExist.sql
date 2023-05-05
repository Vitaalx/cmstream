SELECT column_name, data_type 
FROM information_schema.columns 
WHERE table_name = '{tableName}' and column_name = '{columnName}';