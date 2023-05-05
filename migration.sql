CREATE TABLE [if not exists] post ( id SERIAL PRIMARY KEY, title VARCHAR(120), author_id INT );
ALTER TABLE user ALTER COLUMN id [SET DATA] TYPE SERIAL PRIMARY KEY;
ALTER TABLE user ADD COLUMN firstname VARCHAR(60) constraint;
ALTER TABLE user ADD COLUMN lastname VARCHAR(120) constraint;
ALTER TABLE user ADD COLUMN country VARCHAR(2) constraint;
ALTER TABLE user DROP COLUMN test;
DROP TABLE test_table;
