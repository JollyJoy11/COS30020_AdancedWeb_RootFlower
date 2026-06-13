<?php 
include "include/session.php"; 
include "include/db_connect.php";

if (isset($_SESSION['user'])){
    $notification_data = displayNotification($conn, $_SESSION['user']);
    $unread_notifications = $notification_data['notifications'];
    $unread_count = $notification_data['count'];
}

$token = $_GET['token'] ?? '';
$valid = false;
$user = null;

// Validate token
if (!empty($token)) {
    $query = "SELECT email, reset_expiry FROM account_table WHERE reset_token = '$token'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (strtotime($user['reset_expiry']) >= time()) {
            $valid = true;
        }
    }
}

$error_emptypassword = false;
$error_npassword = false;
$error_cfpassword = false;

// Validate reset password
if ($valid && isset($_POST['reset_password'])){
    $newPassword = $_POST['newPassword'];
	$confirmPassword = $_POST['confirmPassword'];

    if (empty($newPassword) || empty($confirmPassword)) {
        $error_emptypassword = true;
    } else {
        if (!preg_match("/^(?=.*[0-9])(?=.*[!@#$%^&*]).{8,}$/", $newPassword)) {
            $error_npassword = true;
        } 

        if ($newPassword !== $confirmPassword) {
            $error_cfpassword = true;
        } 

        if (!$error_emptypassword && !$error_npassword && !$error_cfpassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $update_query = "UPDATE account_table SET password = '$hashedPassword', reset_token = NULL, reset_expiry = NULL WHERE email = '{$user['email']}'";
            mysqli_query($conn, $update_query);

            $to = $user['email'];
            $subject = "Your Password Has Been Reset - Root Flower"; 
            $message = "Hi,\n\nYour password has been successfully reset on Root Flower. If you did not perform this action, please contact our support immediately.\n\nThanks,\nRoot Flower Team"; 

            if (sendEmail($to, $subject, $message)){ 
                unset($_SESSION['user']);
                header('Location: login.php#main-form');
                exit;
            } else {
                echo "Error: Unable to send confirmation email.";
            }
        }	
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>

<html lang="en">
<!-- Description: Reset Password -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 4/10/2025 -->
<!-- Validated: OK 6/10/2025 -->

<head>
    <title>Reset Password | Root Flower</title>
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
        <?php if ($valid): ?>
            <form name="reset_password_form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?token=' . urlencode($token); ?>" class="py-4 px-5 rounded shadow w-50" id="form-card" novalidate>
                <h1>Reset Password</h1>
                <div class="small text-danger <?php echo $error_emptypassword ? "" : "d-none"; ?>">* Please fill in all the required fill</div>
                <div class="mb-3 me-4 position-relative">
                    <label for="newPassword" class="form-label">New Password&emsp;<span data-bs-toggle="tooltip" data-bs-placement="right" title="Password must be at least 8 characters, include a number and a symbol."><i class="bi bi-info-circle"></i></span></label>
                    <input type="checkbox" id="showNewPassword" class="d-none">
                    <input type="text" name="newPassword" id="newPassword" class="form-control password-field <?php echo $error_npassword ? 'is-invalid' : ''; ?>">

                    <label for="showNewPassword" class="position-absolute toggle-password">
                        <i class="bi bi-eye-slash"></i>
                        <i class="bi bi-eye d-none"></i>
                    </label>
                    <div class="invalid-feedback">
                        * Must be at least 8 characters, contain a number and a symbol
                    </div>
                </div>

                <div class="mb-3 me-4 position-relative">
                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                    <input type="checkbox" id="showConfirmPassword" class="d-none">
                    <input type="text" name="confirmPassword" id="confirmPassword" class="form-control password-field <?php echo $error_cfpassword ? 'is-invalid' : ''; ?>">

                    <label for="showConfirmPassword" class="position-absolute toggle-password">
                        <i class="bi bi-eye-slash"></i>
                        <i class="bi bi-eye d-none"></i>
                    </label>
                    <div class="invalid-feedback">
                        * Passwords do not match
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" name="reset_password">Reset Password</button>
            </form>
        <?php else: ?>
            <div class="py-4 px-5 rounded shadow w-50 reset_bg">
                <h1>Reset Password</h1>
                <p>This reset link is invalid or has expired.</p>
                <a href="forgot_password.php" class="btn">Request for New Link</a>
            </div>
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