<?php 
include "include/session.php"; 

if (isset($_SESSION['user']) && $_SESSION['role'] === 'user') {
	header('Location:main_menu.php');
	exit;
} else if (isset($_SESSION['user']) && $_SESSION['role'] === 'admin') {
	header('Location:main_menu_admin.php');
	exit;
}

if (basename($_SERVER['PHP_SELF']) === 'registration.php') {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') { // refresh clears the form
        if (empty($_SESSION['keepForm'])) {
            unset($_SESSION['formData']);
            unset($_SESSION['errors']);
        }
		unset($_SESSION['keepForm']);
    }
} else {
    unset($_SESSION['formData']);
    unset($_SESSION['errors']);
}
?>

<!DOCTYPE html>

<html lang="en">
<!-- Description: User Registration -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 10/9/2025 -->
<!-- Validated: OK 6/10/2025 -->

<head>
    <title>Register Now | Root Flower</title>
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
			
		<div class="m-5 text-left shadow-lg text-secondary rounded z-1 container p-0 overflow-hidden" id="form-card">
			<div class="row g-0">
				<!-- Image Carousel -->
				<div class="col-lg-6 d-none d-lg-block" id="carousel-container">
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
				<div class="col-lg-6 px-5 py-4 m-auto d-flex flex-column justify-content-center" id="form-container">
					<h1>Create Account</h1>

					<form name="registration_form" method="POST" action="process_register.php" novalidate="novalidate" class="py-3">
						<div class="row">
							<div class="form-group col-md-6 mb-2">
								<label for="firstName">First Name</label>
								<input type="text" class="form-control 
								<?php echo isset($_SESSION['formData']['firstName']) ? (isset($_SESSION['errors']['firstName']) ? 'is-invalid' : 'is-valid') : ''; ?>" 
								id="firstName" name="firstName" 
								value="<?php echo $_SESSION['formData']['firstName'] ?? ''; ?>">
							</div>
							<div class="form-group col-md-6 mb-2">
								<label for="lastName">Last Name</label>
								<input type="text" class="form-control <?php echo isset($_SESSION['formData']['lastName']) ? (isset($_SESSION['errors']['lastName']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="lastName" name="lastName" value="<?php echo $_SESSION['formData']['lastName'] ?? ''; ?>">
							</div>
						</div>

						<?php if (isset($_SESSION['errors']['firstName']) || isset($_SESSION['errors']['lastName'])): ?>
							<div class="small text-danger mb-2">
								<?php 
									echo ($_SESSION['errors']['firstName'] ?? '') . 
									(!empty($_SESSION['errors']['firstName']) && !empty($_SESSION['errors']['lastName']) ? '<br>' : ''); 

									if (($_SESSION['errors']['firstName'] ?? null) != ($_SESSION['errors']['lastName'] ?? null)) {
										echo ($_SESSION['errors']['lastName'] ?? '');
									} 
								?>
							</div>
						<?php endif; ?>
						
						<div class="row">
							<div class="form-group col-md-6 mb-2">
								<label for="dob">Date of Birth</label>
								<input type="date" class="form-control <?php echo isset($_SESSION['formData']['dob']) ? (isset($_SESSION['errors']['dob']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="dob" name="dob" value="<?php echo $_SESSION['formData']['dob'] ?? ''; ?>">
								<div class="invalid-feedback">
									<?php echo $_SESSION['errors']['dob'] ?? ''; ?>
								</div>
							</div>

							<div class="form-group col-md-6">
								<label class="form-label d-block">Gender</label>

								<div class="form-check form-check-inline">
									<input class="form-check-input mt-0" type="radio" id="female" name="gender" value="Female" <?php 
										if (
											(isset($_SESSION['formData']['gender']) && $_SESSION['formData']['gender'] === 'Female') || !isset($_SESSION['formData']['gender']) 
										) echo 'checked'; 
									?>>
									<label class="form-check-label" for="female">Female</label>
								</div>

								<div class="form-check form-check-inline">
									<input class="form-check-input mt-0" type="radio" id="male" name="gender" value="Male"
									<?php 
										if (isset($_SESSION['formData']['gender']) && $_SESSION['formData']['gender'] === 'Male') 
											echo 'checked'; 
									?>>
									<label class="form-check-label" for="male">Male</label>
								</div>
							</div>
						</div>
						
						<div class="form-group mb-2">
							<label for="email">Email</label>
							<input type="text" class="form-control <?php echo isset($_SESSION['formData']['email']) ? ((isset($_SESSION['errors']['email']) || isset($_SESSION['errors']['duplicate'])) ? 'is-invalid' : 'is-valid') : ''; ?>" id="email" name="email" placeholder="e.g. abc@gmail.com" value="<?php echo $_SESSION['formData']['email'] ?? ''; ?>">
							<div class="invalid-feedback">
								<?php 
									if (isset($_SESSION['errors']['email'])) {
										echo $_SESSION['errors']['email'];
									} elseif (isset($_SESSION['errors']['duplicate'])) {
										echo $_SESSION['errors']['duplicate'];
									}
								?>
							</div>
						</div>

						<div class="form-group col-md-6 mb-2">
							<label for="hometown">Hometown</label>
							<input type="text" class="form-control <?php echo isset($_SESSION['formData']['hometown']) ? (isset($_SESSION['errors']['hometown']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="hometown" name="hometown" placeholder="e.g. Kuching, Sarawak" value="<?php echo $_SESSION['formData']['hometown'] ?? ''; ?>">
							<div class="invalid-feedback">
								<?php echo $_SESSION['errors']['hometown'] ?? ''; ?>
							</div>
						</div>

						<div class="form-group col-11 col-md-6 mb-2 position-relative">
							<label for="password">Password&emsp;<span data-bs-toggle="tooltip" data-bs-placement="right" title="Password must be at least 8 characters, include a number and a symbol."><i class="bi bi-info-circle"></i></span></label>
							<input type="checkbox" id="showPassword" class="d-none">
							<input type="text" class="form-control password-field <?php echo isset($_SESSION['errors']['password']) ? 'is-invalid' : ''; ?>" id="password" name="password">

							<label for="showPassword" class="position-absolute toggle-password">
								<i class="bi bi-eye-slash"></i>
								<i class="bi bi-eye d-none"></i>
							</label>
							<div class="invalid-feedback">
								<?php echo $_SESSION['errors']['password'] ?? ''; ?>
							</div>
						</div>

						<div class="form-group col-11 col-md-6 mb-2 position-relative">
							<label for="cf-password">Confirm Password</label>
							<input type="checkbox" id="showPassword1" class="d-none">
							<input type="text" class="form-control password-field" id="cf-password" name="cf-password">

							<label for="showPassword1" class="position-absolute toggle-password">
								<i class="bi bi-eye-slash"></i>
								<i class="bi bi-eye d-none"></i>
							</label>
							<div class="invalid-feedback">
								<?php echo $_SESSION['errors']['cf_password'] ?? ''; ?>
							</div>
						</div>

						<button type="submit" class="btn btn-primary mt-2 me-2" name="register">Register</button>
						<button type="submit" class="btn mt-2" name="reset">Clear</button>
					</form>

					<p class="text-center">Already have an account? <a href="login.php#main-form" class="text-decoration-none">&nbsp;Sign In</a></p>
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