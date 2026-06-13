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

# Fix MPM conflict at runtime — remove all mpm symlinks, keep only prefork
rm -f /etc/apache2/mods-enabled/mpm_*.load \
      /etc/apache2/mods-enabled/mpm_*.conf
ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

exec apache2-foreground
