SELECT 
    column_name, 
    case
        when data_type = 'character varying' then CONCAT('VARCHAR(', character_maximum_length ,')')
        when data_type = 'integer' then 'INT'
    end as data_type 
FROM information_schema.columns 
WHERE table_name = '{tableName}' and column_name = '{columnName}';