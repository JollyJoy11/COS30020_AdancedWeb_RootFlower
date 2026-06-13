<?php
session_start();
include 'include/functions.php';
include "include/db_connect.php";

if (isset($_POST['register'])) {
    $firstName = ucwords(strtolower(sanitise_input($_POST['firstName'])));
    $lastName = ucwords(strtolower(sanitise_input($_POST['lastName'])));
    $dob = sanitise_input($_POST['dob']);
    $gender = $_POST['gender'];
    $email = sanitise_input($_POST['email']);
    $hometown = ucwords(strtolower(sanitise_input($_POST['hometown'])));
    $password = sanitise_input($_POST['password']);
    $cf_password = sanitise_input($_POST['cf-password']);

    $errors = [];

    // First Name
    if (empty($firstName)) {
        $errors['firstName'] = "* First name is required.";
    } else if (!preg_match("/^[A-Za-z\s]+$/", $firstName)) {
        $errors['firstName'] = "* Name can contain only letters and white spaces.";
    }

    // Last Name
    if (empty($lastName)) {
        $errors['lastName'] = "* Last name is required.";
    } else if (!preg_match("/^[A-Za-z\s]+$/", $lastName)) {
        $errors['lastName'] = "* Name can contain only letters and white spaces.";
    }

    // Date of Birth
    if (empty($dob)) {
        $errors['dob'] = "* Date of birth is required.";
    } else {
        $date = DateTime::createFromFormat('Y-m-d', $dob);
        $dateErrors = DateTime::getLastErrors();

        $hasDateErrors = is_array($dateErrors) && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0);
        if (!$date || $hasDateErrors || $date > new DateTime()) {
            $errors['dob'] = "* Invalid date of birth.";
        }
    } 

    // Email
    if (empty($email)) {
        $errors['email'] = "* Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "* Invalid email format.";
    }

    // Hometown
    if (empty($hometown)) {
        $errors['hometown'] = "* Hometown is required.";
    }

    // Password
    if (empty($password)) {
        $errors['password'] = "* Password is required.";
    } elseif (!preg_match("/^(?=.*[0-9])(?=.*[!@#$%^&*]).{8,}$/", $password)) {
        $errors['password'] = "* Must be at least 8 characters, contain a number and a symbol.";
    } elseif ($password !== $cf_password) {
        $errors['password'] = "* Passwords do not match.";
    }

    // Check for duplicate email
    $query = "SELECT * FROM user_table WHERE email = '$email' AND trash='no'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $errors['duplicate'] = "* Email has already been registered.";
    }
    
    // save user data into database
    if (empty($errors)) {
        $user_query = "INSERT INTO user_table (email, first_name, last_name, dob, gender, hometown) VALUES ('$email', '$firstName', '$lastName', '$dob', '$gender', '$hometown')";
        $account_query = "INSERT INTO account_table (email, password) VALUES ('$email', '" . password_hash($password, PASSWORD_DEFAULT) . "')";

        if (mysqli_query($conn, $user_query) && mysqli_query($conn, $account_query)) {
            $to = $email; 
            $subject = "Welcome to Root Flower"; 
            $message = "Hi $firstName, \n\nWelcome to Root Flower! We're so happy to have you join our community.\n\nWith your new account, you can explore beautiful flower arrangements, discover tips to brighten your space, and enjoy exclusive member updates.\n\nIf you have any questions or need assistance, we're always here to help.\n\nThanks for joining us,\nRoot Flower Team"; 

            if (sendEmail($to, $subject, $message)){
                generateAdminNotification($conn, $_SESSION['user'], 'new_user'); 
                unset($_SESSION['errors']);
                unset($_SESSION['formData']);
                header('Location: login.php#main-form');
                exit();
            } else {
                echo "Error: Unable to send confirmation email.";
            }
        } 

        mysqli_close($conn);
    } else {
        $_SESSION['errors'] = $errors;
        $_SESSION['formData'] = $_POST;
        $_SESSION['keepForm'] = true;
        header('Location: registration.php#main-form');
    }
} else if (isset($_POST['reset'])) {
    unset($_SESSION['errors']);
    unset($_SESSION['formData']);
    header('Location: registration.php#main-form');
} else {
    echo "No data submitted.";
} 
?>