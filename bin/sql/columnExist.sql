SELECT 
    column_name,
    conname,
    case
        when pg_constraint.contype = 'u' then true
        else false
    end as is_unique,
    case
        when is_nullable = 'YES' then false
        when is_nullable = 'NO' then true
    end as is_not_nullable,
    case
        when data_type = 'character varying' then CONCAT('VARCHAR(', character_maximum_length ,')')
        when data_type = 'integer' then 'INT'
        when data_type = 'date' then 'DATE'
        when data_type = 'text' then 'TEXT'
        when data_type = 'boolean' then 'BOOLEAN'
        when data_type = 'numeric' then 'FLOAT'
        else data_type
    end as data_type
FROM information_schema.columns
LEFT JOIN pg_constraint on pg_constraint.conrelid = '{tableName}'::regclass::oid AND pg_constraint.conname LIKE '%\_{columnName}\_%'
WHERE table_name = '{tableName}' and column_name = '{columnName}';