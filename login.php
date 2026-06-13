<?php 
include "include/session.php"; 
include "include/db_connect.php";

if (isset($_SESSION['user']) && $_SESSION['role'] === 'user') {
	header('Location:main_menu.php');
	exit;
} else if (isset($_SESSION['user']) && $_SESSION['role'] === 'admin') {
	header('Location:main_menu_admin.php');
	exit;
}

if (isset($_POST['login'])) {
	$email = sanitise_input($_POST['email']);
	$password = trim($_POST['password']);

	$errors = [];

	if (empty($email) || empty($password)) {
		$errors['login'] = "* Please fill in the fields.";
	} else {
		$query = "SELECT password, type FROM account_table WHERE email = '$email' AND trash='no'";
		$result = mysqli_query($conn, $query);

		if (mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			$savedPassword = $row['password'];
			$role = $row['type'];

			if (password_verify($password, $savedPassword)) {
				$_SESSION['user'] = $email;
				mysqli_query($conn, "UPDATE account_table SET login_time = NOW() WHERE email = '$email'");

				if (isset($_POST['remember'])) {
					generateRememberMeToken($email, $conn);
				}

				if ($role == 'user'){
					$query = "SELECT first_name, last_name, gender, profile_image FROM user_table WHERE email = '$email' AND trash='no'";
					$result = mysqli_query($conn, $query);

					if (mysqli_num_rows($result) > 0) {
						// Set sessions if no errors
						$row = mysqli_fetch_assoc($result);
						$_SESSION['name'] = $row['first_name'];
						$_SESSION['lname'] = $row['last_name'];
						$_SESSION['gender'] = $row['gender'];
						$_SESSION['profile'] = $row['profile_image'];
						$_SESSION['role'] = 'user';
					}

					if (isset($_SESSION['redirect'])) {
						$redirectUrl = $_SESSION['redirect'];
						unset($_SESSION['redirect']);
						header("Location: " . $redirectUrl);
					} else {
						header("Location: main_menu.php");
					}
				} else {
					header("Location: main_menu_admin.php");
					$_SESSION['role'] = 'admin';
				}

				exit();
			} else {
				$errors['login'] = "* Incorrect username or password.";
			}
		} else {
			$errors['login'] = "* Incorrect username or password.";
		}
	}

	mysqli_close($conn);
}
?>

<!DOCTYPE html>

<html lang="en">
<!-- Description: User Login -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 12/9/2025 -->
<!-- Validated: OK 6/10/2025 -->

<head>
    <title>Sign In | Root Flower</title>
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

<body class="text-secondary fs-5">
	<?php include "include/nologin_nav.php"; ?>
	
    <article class="position-relative d-flex justify-content-center align-items-center min-vh-100" id="main-form">
		<div id="gradient-overlay" class="position-absolute w-100 h-100 start-0 z-0"></div>
			
		<div class="m-5 text-left shadow text-secondary rounded z-1 container p-0 overflow-hidden" id="form-card">
			<div class="row g-0">
				<!-- Image Carousel -->
				<div class="col-md-6 d-none d-md-block" id="carousel-container">
					<div id="formCarousel" class="carousel slide carousel-fade h-100" data-bs-ride="carousel" data-bs-interval="5000" data-bs-pause="false">
						<div class="carousel-indicators">
							<button type="button" data-bs-target="#formCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
							<button type="button" data-bs-target="#formCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
							<button type="button" data-bs-target="#formCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
						</div>

						<div class="carousel-inner h-100">
							<?php
								$product_folder = "img/products"; 

								$all_files = glob($product_folder . "/*.{jpg,jpeg,png}", GLOB_BRACE);

								$products = array_filter($all_files, function($file) {
									return !str_contains($file, "_alt");
								});

								if ($products) {
									$random_products = array_rand($products, 3);

									$isFirst = true;
									foreach ($random_products as $index) {
										$activeClass = $isFirst ? "active" : "";
										echo "
										<div class='carousel-item $activeClass h-100'>
											<div class='d-flex justify-content-center align-items-center h-100'>
												<img src='" . $products[$index] . "' alt='Flower Product' class='img-fluid shadow'>
											</div>
										</div>";
										$isFirst = false;
									}
								} else {
									echo "No images found in folder.";
								}
								?>
						</div>
					</div>
				</div> 

				<!-- Form -->
				<div class="col-md-6 m-auto py-3 d-flex flex-column justify-content-center align-items-center" id="form-container">
					<h1>Welcome Back</h1>

					<form name="login_form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" novalidate="novalidate" class="py-3 w-75">
						<div class="small text-danger mb-2">
							<?php echo $errors['login'] ?? ''; ?>
						</div>
						<div class="form-group mb-2">
							<label for="email">Email</label>
							<input type="email" class="form-control" id="email" name="email" placeholder="e.g. abc@gmail.com">
						</div>

						<div class="form-group mb-2 col-11 position-relative">
							<label for="password">Password</label>
							<input type="checkbox" id="showPassword" class="d-none">
							<input type="text" class="form-control password-field" id="password" name="password">

							<label for="showPassword" class="position-absolute toggle-password">
								<i class="bi bi-eye-slash"></i>
								<i class="bi bi-eye d-none"></i>
							</label>
						</div>

						<div class="mb-2 d-flex flex-wrap justify-content-between small align-items-center">
							<div class="form-check pe-3">
								<input class="form-check-input" type="checkbox" id="remember" name="remember">
								<label class="form-check-label" for="remember">Remember Me</label>
							</div>
							<a href="forgot_password.php" class="text-decoration-none ms-auto">Forgot password?</a>
						</div>

						<button type="submit" class="btn btn-primary mt-2 me-2 w-100" name="login">Log In</button>
					</form>

					<p class="text-center">Don't have an account? <a href="registration.php#main-form" class="text-decoration-none"><br>Sign Up Now</a></p>
				</div>
			</div>
		</div>
    </article>
	
	<?php include "include/nologin_footer.php"; ?>

<script src="js/main.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>
</html>