<?php
$servername = getenv('MYSQLHOST')     ?: 'localhost';
$username   = getenv('MYSQLUSER')     ?: 'root';
$password   = getenv('MYSQLPASSWORD') ?: '';
$dbname     = getenv('MYSQLDATABASE') ?: 'RootFlower';
$port       = getenv('MYSQLPORT')     ?: 3306;

$conn = mysqli_connect($servername, $username, $password, $dbname, (int)$port);
mysqli_set_charset($conn, "utf8mb4");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
