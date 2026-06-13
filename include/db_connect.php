<?php
if (getenv('MYSQL_PUBLIC_URL')) {
    $url        = parse_url(getenv('MYSQL_PUBLIC_URL'));
    $servername = $url['host'];
    $username   = $url['user'];
    $password   = $url['pass'];
    $dbname     = ltrim($url['path'], '/');
    $port       = (int)$url['port'];
} else {
    $servername = 'localhost';
    $username   = 'root';
    $password   = '';
    $dbname     = 'RootFlower';
    $port       = 3306;
}

$conn = mysqli_connect($servername, $username, $password, $dbname, $port);
mysqli_set_charset($conn, "utf8mb4");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
