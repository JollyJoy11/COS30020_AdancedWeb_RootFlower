<?php 
session_start();
include "include/db_connect.php";
include 'include/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $query = "SELECT newsletter FROM user_table WHERE email = '$email' AND trash='no'";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0){
            $row = mysqli_fetch_assoc($result);

            if ($row['newsletter'] == "yes"){
                $alert['info'] = "You have already subscribed to the newsletter.";
            } else {
                $update_query = "UPDATE user_table SET newsletter='yes' WHERE email='$email'";
                $result = mysqli_query($conn, $update_query);

                $to = $email; 
                $subject = "Welcome to Root Flower's Newsletter!"; 
                $message = "Hi ". $_SESSION['name'].", \n\nThank you for subscribing to Root Flower's newsletter!\n\nYou'll now receive the latest updates on:\n- New bouquets and flower arrangements\n- Upcoming workshops\n- Special promotions and discounts\n\nWe're excited to have you in our floral community!\n\nStay connected and enjoy the blooms,\nRoot Flower Team"; 

                if (sendEmail($to, $subject, $message)){
                    $alert['success'] = "You are now subscribed to the newsletter.";
                } else {
                    $alert['danger'] = "Unable to subscribe to the newsletter.";
                }
            }
        } else {
            $alert['danger'] = "This email is not registered with Root Flower.";
        }
    } else {
        $alert['danger'] = "Invalid email address.";
    }

    $_SESSION['alert'] = $alert;

    if (isset($_SESSION['redirect_to'])) {
        $redirectUrl = $_SESSION['redirect_to'];
        unset($_SESSION['redirect_to']); 
        header("Location: " . $redirectUrl); 
        exit();
    }
}

mysqli_close($conn);
?>