-- Create test database
CREATE DATABASE IF NOT EXISTS test_db;

-- Grant privileges to user
GRANT ALL PRIVILEGES ON test_db.* TO 'user'@'%';
GRANT ALL PRIVILEGES ON db.* TO 'user'@'%';
FLUSH PRIVILEGES;