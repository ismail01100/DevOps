#!/bin/bash

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
while ! mysql -h db -u root -prootpassword -e "SELECT 1" >/dev/null 2>&1; do
    sleep 1
done

# Create test database if it doesn't exist and drop all existing tables
mysql -h db -u root -prootpassword -e "
    DROP DATABASE IF EXISTS test_db;
    CREATE DATABASE test_db;
    GRANT ALL PRIVILEGES ON test_db.* TO 'user'@'%';
    FLUSH PRIVILEGES;"

# Import schema to test database
mysql -h db -u user -ppassword test_db < /var/www/html/sql/portefeuille.sql

# Install Composer dependencies if they're not already installed
if [ ! -d "vendor" ]; then
    composer install
fi

# Run PHPUnit tests
./vendor/bin/phpunit --colors=always 