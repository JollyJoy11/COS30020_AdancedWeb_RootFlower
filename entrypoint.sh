#!/bin/bash
set -e

echo "Waiting for MySQL to be ready..."
until php -r "
\$conn = @mysqli_connect(
    getenv('MYSQLHOST') ?: 'localhost',
    getenv('MYSQLUSER') ?: 'root',
    getenv('MYSQLPASSWORD') ?: '',
    getenv('MYSQLDATABASE') ?: 'RootFlower',
    (int)(getenv('MYSQLPORT') ?: 3306)
);
exit(\$conn ? 0 : 1);
" 2>/dev/null; do
    echo "MySQL not ready yet, retrying in 2s..."
    sleep 2
done

echo "MySQL ready. Running database setup..."
php /var/www/html/include/database.php
echo "Database setup complete. Starting Apache..."

exec apache2-foreground
