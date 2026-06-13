<?php 
include "include/session.php"; 
include "include/db_connect.php";

if (isset($_SESSION['user'])){
    $notification_data = displayNotification($conn, $_SESSION['user']);
    $unread_notifications = $notification_data['notifications'];
    $unread_count = $notification_data['count'];
}

if (isset($_POST['forgot'])) {
    $email = sanitise_input($_POST['forgot_email']);
    $errors = '';

    if (empty($email)) {
		$errors = "* Please fill in your email.";
	} else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors = "* Invalid email format.";
    } else {
        $query = "SELECT email FROM account_table WHERE email = '$email'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            $token = bin2hex(random_bytes(16)); 
            $expiry = date("Y-m-d H:i:s", time() + (5 * 60)); // 5 minutes expiry

            $updateQuery = "UPDATE account_table SET reset_token = '$token', reset_expiry = '$expiry' WHERE email = '$email'";
            mysqli_query($conn, $updateQuery);

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];   // e.g. localhost
            $path = dirname($_SERVER['REQUEST_URI']);  // auto-detect the folder

            $resetLink = $protocol . "://" . $host . $path . "/reset_password.php?token=$token"; // relative link

            $to = $email; 
            $subject = "Password Reset Request - Root Flower"; 
            $message = "Hi, \n\nWe received a request to reset your password. If this was you, click the link below to reset your password:\n\n$resetLink\n\nThis link will expire in 10 minutes.\n\nIf you did not request a password reset, please ignore this email.\n\nThanks,\nRoot Flower Team"; 

            if (sendEmail($to, $subject, $message)) { 
                header("Location: forgot_password.php?status=success");
                exit;
            } else {
                echo "Error: Unable to send confirmation email.";
            }

        } else {
            $errors = "* Email not found.";
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>

<html lang="en">
<!-- Description: Forgot Password -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 3/10/2025 -->
<!-- Validated: OK 6/10/2025 -->

<head>
    <title>Forgot Password | Root Flower</title>
	<meta charset="utf-8"/>
	<meta name="author" content="Joanne Chin Jia Xuan"/>
	<meta name="description" content="Root Flower is a creative florist hub offering fresh floral products, inspiring workshops, and a platform for students to showcase their floral artistry. Discover, learn, and create with us.">
	<meta name="keywords" content="Root Flower, florist, kuching florist, flower, flower bouquet, florist workshop"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<link rel="icon" type="image/x-icon" href="img/favicon.ico"/> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous"> <!-- Bootstrap link-->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css"> <!-- Bootstrap icon link-->
	<link rel="stylesheet" type="text/css" href="style/style.css"/>
</head>

<body class="fs-5">
	<?php 
    include isset($_SESSION['user']) ? "include/header.php" : "include/nologin_nav.php"; 
    ?>

    <article class="min-vh-100 d-flex justify-content-center align-items-center" id="form_bg">
        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="py-4 px-5 rounded shadow reset_bg">
                <h1>Check Your Email</h1>
                <p>A reset link was sent to your email.</p>
                <a href="<?php echo isset($_SESSION['user']) ? "main_menu.php" : "index.php" ; ?>" class="btn">Return to Home</a>
            </div>
        <?php else: ?>
            <form name="forgot_password_form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="py-4 px-5 rounded shadow" id="form-card" novalidate>
                <h1>Forgot Your Password?</h1>
                <div class="form-group mb-4">
                    <label for="forgot_email" class="form-label">Enter your email:</label>
                    <input type="email" name="forgot_email" id="forgot_email" placeholder="e.g. abc@gmail.com" class="form-control <?php echo isset($errors) ? "is-invalid" : ""; ?>">
                    <div class="invalid-feedback">
                        <?php echo $errors; ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mb-2" name="forgot">Send Reset Link</button>
                <a href="<?php echo isset($_SESSION['user']) ? "update_profile.php" : "login.php#main-form" ; ?>" class="btn mb-2">Cancel</a>
            </form>
        <?php endif; ?>
    </article>

	<?php 
    include isset($_SESSION['user']) ? "include/footer.php" : "include/nologin_footer.php"; 
    ?>

<script src="js/main.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>
</html>