<?php
    session_start();
    include_once "include/db_connect.php";
    include "include/functions.php";
    clearRememberMe($_SESSION['user'] ?? null, $conn);
    session_destroy();
    header("Location: index.php");
    exit();
?>