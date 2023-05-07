SELECT table_name
FROM information_schema.tables 
WHERE table_schema not in ('information_schema', 'pg_catalog');