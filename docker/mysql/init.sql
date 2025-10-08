-- Initialization script for Pokemon API Database
-- This script runs automatically when the MySQL container starts for the first time

SET sql_mode = 'NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';

-- Create database if not exists (docker-compose already creates it)
-- CREATE DATABASE IF NOT EXISTS pokemon_api;

-- Grant privileges to the user
GRANT ALL PRIVILEGES ON pokemon_api.* TO 'pokemon_user'@'%';
FLUSH PRIVILEGES;

-- Optimize MySQL for Laravel
SET GLOBAL sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';

-- Create timezone tables for better Laravel timezone support
-- This is optional but recommended
-- mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root mysql