<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "RootFlower";

$conn = mysqli_connect($servername, $username, $password, $dbname);
// Set charset to support emojis and full Unicode
mysqli_set_charset($conn, "utf8mb4");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
