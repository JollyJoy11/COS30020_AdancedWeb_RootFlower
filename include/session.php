<?php
    ini_set('session.cookie_lifetime', 0); //Logout when the browser is closed
    ini_set('session.gc_maxlifetime', 3600); //Logout when the user is inactive for an hour

    session_start();
    include "include/db_connect.php";
    include "include/functions.php";
    $public_pages = ['index.php', 'login.php', 'registration.php', 'about.php', 'profile.php', 'forgot_password.php', 'reset_password.php'];

    // Auto-login with remember-me token
    if (!isset($_SESSION['user'])) {
        $email = validateRememberMeToken($conn);
        if ($email) {
            $_SESSION['user'] = $email;
            header("Location: main_menu.php");
            exit;
        } else if (!in_array(basename($_SERVER['PHP_SELF']), $public_pages)) {
            $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
            header('Location: login.php#main-form');
            exit;
        }
    } 
?>