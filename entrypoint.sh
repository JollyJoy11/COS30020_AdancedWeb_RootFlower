#!/bin/bash
set -e

echo "Waiting for MySQL to be ready..."
until php -r "
if (getenv('MYSQL_PUBLIC_URL')) {
    \$url  = parse_url(getenv('MYSQL_PUBLIC_URL'));
    \$host = \$url['host'];
    \$user = \$url['user'];
    \$pass = \$url['pass'];
    \$db   = ltrim(\$url['path'], '/');
    \$port = (int)\$url['port'];
} else {
    \$host = 'localhost'; \$user = 'root'; \$pass = ''; \$db = 'RootFlower'; \$port = 3306;
}
\$conn = @mysqli_connect(\$host, \$user, \$pass, \$db, \$port);
exit(\$conn ? 0 : 1);
" 2>/dev/null; do
    echo "MySQL not ready yet, retrying in 2s..."
    sleep 2
done

echo "MySQL ready. Running database setup..."
php /var/www/html/include/database.php
echo "Database setup complete. Starting Apache..."

exec apache2-foreground
