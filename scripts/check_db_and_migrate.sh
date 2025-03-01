#!/bin/bash

# Check database connectivity
echo "Checking database connectivity..."
php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1

if [ $? -eq 0 ]; then
    echo "Database connection successful."
else
    echo "Database connection failed."
    exit 1
fi

# Run migrations
echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

if [ $? -eq 0 ]; then
    echo "Migrations are up to date."
else
    echo "Failed to run migrations."
    exit 1
fi
