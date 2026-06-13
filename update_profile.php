<?php 
include "include/session.php"; 
include "include/db_connect.php";

if (isset($_GET['mark_read'])) {
    $noti_id = (int)$_GET['mark_read'];

    if ($noti_id > 0) {
        $sql = "UPDATE notification_table SET is_read = 1 WHERE id = $noti_id AND email = '{$_SESSION['user']}'";
        mysqli_query($conn, $sql);
    }
}

$notification_data = displayNotification($conn, $_SESSION['user']);
$unread_notifications = $notification_data['notifications'];
$unread_count = $notification_data['count'];

$query = "SELECT * FROM user_table WHERE email = '{$_SESSION['user']}'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
	$row = mysqli_fetch_assoc($result);
	$firstName = $row['first_name'];
	$lastName = $row['last_name'];
	$gender = $row['gender'];
	$DOB = $row['dob'];
	$profile = $row['profile_image'];
	$hometown = $row['hometown'];

	$error_cpassword = false;
	$error_emptypassword = false;
	$error_npassword = false;
	$error_cfpassword = false;

	if ($profile == NULL){
		$_SESSION['profile'] = NULL;
		$_SESSION['gender'] = $gender;
	}

	if (isset($_POST['update'])) { // Update profile
		$edit_firstName = ucwords(strtolower(sanitise_input($_POST['edit_firstName'])));
		$edit_lastName = ucwords(strtolower(sanitise_input($_POST['edit_lastName'])));
		$edit_dob = sanitise_input($_POST['edit_dob']);
		$edit_gender = $_POST['edit_gender'];
		$edit_hometown = ucwords(strtolower(sanitise_input($_POST['edit_hometown'])));
		$errors = [];

		// First Name
		if(empty($edit_firstName)) {
			$errors['firstName'] = "* First name is required.";
		} else if (!preg_match("/^[A-Za-z\s]+$/", $edit_firstName)) {
			$errors['firstName'] = "* Name can contain only letters and white spaces.";
		}

		// Last Name
		if (empty($edit_lastName)) {
			$errors['lastName'] = "* Last name is required.";
		} else if (!preg_match("/^[A-Za-z\s]+$/", $edit_lastName)) {
			$errors['lastName'] = "* Name can contain only letters and white spaces.";
		}

		// Date of Birth
		if (empty($edit_dob)) {
			$errors['dob'] = "* Date of birth is required.";
		} else if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $edit_dob) || strtotime($edit_dob) > time()) {
			$errors['dob'] = "* Invalid date of birth.";
		}

		// Hometown
		if (empty($edit_hometown)) {
			$errors['hometown'] = "* Hometown is required.";
		}

		if (empty($errors)){
			$update_query = "UPDATE user_table SET first_name = '$edit_firstName', last_name = '$edit_lastName', dob = '$edit_dob', gender = '$edit_gender', hometown = '$edit_hometown' WHERE email = '{$_SESSION['user']}'";

			if (mysqli_query($conn, $update_query)) {
				$_SESSION['name'] = $edit_firstName;
				$_SESSION['lname'] = $edit_lastName;
				$_SESSION['gender'] = $edit_gender;

				$alert['success'] = "Profile updated successfully.";
				$_SESSION['alert'] = $alert;

				header("Location: update_profile.php");
				exit;
			} else {
				$alert['danger'] = "Failed to update profile. Try again.";
				$_SESSION['alert'] = $alert;
			}
		} 

	} else if (isset($_POST['update_password'])){ // Change password
		$currentPassword = $_POST['currentPassword'];
		$newPassword = $_POST['newPassword'];
		$confirmPassword = $_POST['confirmPassword'];

		if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)){
			$error_emptypassword = true;
		} else {
			$error_emptypassword = false;

			$query = "SELECT password FROM account_table WHERE email = '{$_SESSION['user']}'";
			$result = mysqli_query($conn, $query);
			$row = mysqli_fetch_assoc($result);
			$hashedPassword = $row['password'];

			if (!password_verify($currentPassword, $hashedPassword)) {
				$error_cpassword = true;
			} else {
				$error_cpassword = false;
			}

			if (!preg_match("/^(?=.*[0-9])(?=.*[!@#$%^&*]).{8,}$/", $newPassword)) {
				$error_npassword = true;
			} else {
				$error_npassword = false;
			}

			if ($newPassword !== $confirmPassword) {
				$error_cfpassword = true;
			} else {
				$error_cfpassword = false;
			}

			if (!$error_cpassword && !$error_emptypassword && !$error_npassword && !$error_cfpassword) {
				$newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

				$updateQuery = "UPDATE account_table SET password = '$newHashedPassword' WHERE email = '{$_SESSION['user']}'";

				if (mysqli_query($conn, $updateQuery)) {
					$alert['success'] = "Password updated successfully.";
					$_SESSION['alert'] = $alert;
				} else {
					$alert['danger'] = "Failed to update password. Try again.";
					$_SESSION['alert'] = $alert;
				}

				header("Location: update_profile.php");
				exit;
			}
		}

		$hasPasswordError = $error_cpassword || $error_emptypassword || $error_npassword || $error_cfpassword;
	} else if (isset($_POST['update_profile'])) { // Change profile pic
		if (empty($_POST['profile']) && empty($_POST['cropped_profile'])) {
			$alert['danger'] = "No image selected.";
			$_SESSION['alert'] = $alert;
		} else {
			$data = $_POST['cropped_profile']; // Submitted cropped image

			$data = str_replace('data:image/png;base64,', '', $data);
			$data = str_replace(' ', '+', $data);
			$decoded = base64_decode($data);

			$maxFileSize = 5 * 1024 * 1024; // 5 MB in bytes
			$decodedSize = strlen($decoded);

			if ($decodedSize > $maxFileSize) {
				$alert['danger'] = "Profile image size exceeds the maximum limit of 5 MB.";
				$_SESSION['alert'] = $alert;
			} else {
				$newFileName = uniqid('profile_') . '.png';
				$uploadPath = 'profile_images/' . $newFileName;

				$get_old_image_query = "SELECT profile_image FROM user_table WHERE email = '{$_SESSION['user']}'";
				$old_image_result = mysqli_query($conn, $get_old_image_query);
				$old_image_name = '';

				if (mysqli_num_rows($old_image_result) > 0) {
					$row = mysqli_fetch_assoc($old_image_result);
					$old_image_name = $row['profile_image'];
				}

				if (file_put_contents($uploadPath, $decoded)) {
					$updateProfile = "UPDATE user_table SET profile_image = '$newFileName' WHERE email = '{$_SESSION['user']}'";

					if (mysqli_query($conn, $updateProfile)){
						if (!empty($old_image_name)) {
							$old_image_path = 'profile_images/' . $old_image_name;
							if (file_exists($old_image_path)) {
								unlink($old_image_path); 
							}
						}

						$_SESSION['profile'] = $newFileName;
						$alert['success'] = "Profile image updated successfully.";
						$_SESSION['alert'] = $alert;

						header("Location: update_profile.php");
						exit;
					}
				} else {
					$alert['danger'] = "Failed to save cropped image.";
					$_SESSION['alert'] = $alert;
				}
			}
		}
	} else if (isset($_POST['reset'])) {
		$error_cpassword = false;
		$error_emptypassword = false;
		$error_npassword = false;
		$error_cfpassword = false;
	}
}

mysqli_close($conn);
?>
<!DOCTYPE html>

<html lang="en">
<!-- Description: Update Profile -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 16/9/2025 -->
<!-- Validated: OK 6/10/2025 -->

<head>
    <title>My Account | Root Flower</title>
	<meta charset="utf-8"/>
	<meta name="author" content="Joanne Chin Jia Xuan"/>
	<meta name="description" content="Root Flower is a creative florist hub offering fresh floral products, inspiring workshops, and a platform for students to showcase their floral artistry. Discover, learn, and create with us.">
	<meta name="keywords" content="Root Flower, florist, kuching florist, flower, flower bouquet, florist workshop"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<link rel="icon" type="image/x-icon" href="img/favicon.ico"/> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous"> <!-- Bootstrap link-->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css"> <!-- Bootstrap icon link-->
	<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="style/style.css"/>
</head>

<body class="fs-5">
	<?php include "include/header.php"; ?>

    <article class="text-secondary" id="update-profile">
		<div class="w-100" id="profile-bg"></div>

		<div class="container pb-5 row mx-auto" id="profile-section">
			<div class="text-center col-md-3">
				<figure class='rounded-circle overflow-hidden border shadow mx-auto mb-2 position-relative' id="profile-img" data-bs-toggle="modal" data-bs-target="#uploadProfileModal">
					<?php 
					if (!empty($profile)){
						echo "<img src='profile_images/{$profile}' alt='{$firstName} Profile Picture' class='img-fluid'>";
					} else {
						$defaultImage = ($gender == 'Female') ? 'girl.png' : 'boy.png';
						echo "<img src='profile_images/$defaultImage' alt='Profile Image' class='img-fluid'>";
					}
					?>
					<div id="profile-overlay" class="d-flex justify-content-center align-items-center position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 opacity-0 rounded-circle">
						<i class="bi bi-camera-fill text-light fs-1"></i>
					</div>
				</figure>
			</div>

			<div class="pt-5 col-md-9 px-md-5" id="personal_detail">
				<h1>My Profile &nbsp;<?php echo ((isset($_GET['action']) && $_GET['action'] === 'edit') || !empty($errors)) ? '' : '<a href="?action=edit" class="fs-4 text-secondary text-decoration-none"><i class="bi bi-pencil-square"></i></a>'; ?></h1>

				<!-- Edit profile form -->
				<form name="edit_profile" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" novalidate="novalidate">
					<div class="table-responsive">
						<table class="my-3">
							<tr>
								<th class="pe-3"><label for="firstName">Name</label></th>
								<th><label for="dob">Date of Birth</label></th>
							</tr>

							<tr>
								<td class="pe-3 pb-2 pb-sm-3">
									<input type="text" id="firstName" name="edit_firstName" placeholder="First Name" value="<?php echo $firstName; ?>" <?php echo ((isset($_GET['action']) && $_GET['action'] === 'edit') || !empty($errors)) ? '' : 'disabled'; ?> class="border-0">
									<input type="text" id="lastName" name="edit_lastName" placeholder="Last Name" value="<?php echo $lastName; ?>" <?php echo ((isset($_GET['action']) && $_GET['action'] === 'edit') || !empty($errors)) ? '' : 'disabled'; ?> class="border-0">

									<?php if (isset($errors['firstName']) || isset($errors['lastName'])): ?>
										<br>
										<div class="small text-danger mb-2">
											<?php 
												echo ($errors['firstName'] ?? '') . 
												(!empty($errors['firstName']) && !empty($errors['lastName']) ? '<br>' : ''); 

												if (($errors['firstName'] ?? null) != ($errors['lastName'] ?? null)) {
													echo ($errors['lastName'] ?? '');
												} 
											?>
										</div>
									<?php endif; ?>
								</td>
								<td class="pb-2 pb-sm-3 align-top">
									<input type="date" id="dob" name="edit_dob" value="<?php echo date('Y-m-d', strtotime($DOB)); ?>" <?php echo ((isset($_GET['action']) && $_GET['action'] === 'edit') || !empty($errors)) ? '' : 'disabled'; ?> class="border-0 w-75">

									<?php if (isset($errors['dob'])): ?>
										<br>
										<div class="small text-danger mb-2">
											<?php 
												echo $errors['dob']; 
											?>
										</div>
									<?php endif; ?>
								</td>
							</tr>

							<tr>
								<th class="pe-3">Email</th>
								<th>Gender</th>
							</tr>

							<tr>
								<td class="pe-3 pb-2 pb-sm-3 align-top"><?php echo $_SESSION['user']; ?>&ensp;<i class="bi bi-lock-fill"></i></td>
								<td class="pb-2 pb-sm-3 align-top">
									<?php if ((isset($_GET['action']) && $_GET['action'] === 'edit') || !empty($errors)): ?>
										<div class="form-check form-check-inline">
											<input class="form-check-input mt-0" type="radio" id="female" name="edit_gender" value="Female" <?php echo ($gender === 'Female') ? 'checked' : ''; ?>>
											<label class="form-check-label" for="female">Female</label>
										</div>
										&emsp;
										<div class="form-check form-check-inline">
											<input class="form-check-input mt-0" type="radio" id="male" name="edit_gender" value="Male" <?php echo ($gender === 'Male') ? 'checked' : ''; ?>>
											<label class="form-check-label" for="male">Male</label>
										</div>
									<?php else: ?>
										<?php echo $gender; ?>
									<?php endif; ?>
								</td>
							</tr>

							<tr>
								<th class="pe-3"><label for="hometown">Hometown</label></th>
								<th>Password</th>
							</tr>

							<tr>
								<td class="pe-3 pb-2 pb-sm-3 align-top">
									<input type="text" id="hometown" name="edit_hometown" placeholder="e.g. Kuching, Sarawak" value="<?php echo $hometown; ?>" <?php echo ((isset($_GET['action']) && $_GET['action'] === 'edit') || !empty($errors)) ? '' : 'disabled'; ?> class="border-0">

									<?php if (isset($errors['hometown'])): ?>
										<br>
										<div class="small text-danger mb-2">
											<?php 
												echo $errors['hometown']; 
											?>
										</div>
									<?php endif; ?>
								</td>
								<td class="pb-2 pb-sm-3">
									<?php echo str_repeat("•", 8); ?>&emsp;
									<button type="button" class="fs-6 text-decoration-none border-0 bg-transparent p-0" id="" data-bs-toggle="modal" data-bs-target="#changePasswordModal"><i class="bi bi-pencil-fill"></i></button>
								</td>
							</tr>

							<?php if ((isset($_GET['action']) && $_GET['action'] === 'edit') || !empty($errors)): ?>
							<tr>
								<td>
									<button type="submit" class="btn btn-primary mt-2 me-2" name="update">Update</button>
									<a href="main_menu.php" class="btn mt-2" name="reset">Cancel</a>
								</td>
								<td></td>
							</tr>
							<?php endif; ?>
						</table>
					</div>
				</form>
			</div>
		</div>

		<!-- Change Password Modal -->
		<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
			<div class="modal-dialog modal-dialog-centered mx-auto">
				<div class="modal-content p-2">
					<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" novalidate>
						<div class="modal-header align-items-start">
							<div>
								<h2 class="modal-title">Change Password</h2>
								<div class="small text-danger <?php echo $error_emptypassword ? "" : "d-none"; ?>">* Please fill in all required fields</div>
							</div>
							<button type="submit" name="reset" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
					
						<div class="modal-body">
							<div class="mb-3 me-4 position-relative">
								<label for="currentPassword" class="form-label">Current Password</label>
								<input type="checkbox" id="showCurrentPassword" class="d-none">
								<input type="text" name="currentPassword" id="currentPassword" class="form-control password-field <?php echo $error_cpassword ? 'is-invalid' : ''; ?>">

								<label for="showCurrentPassword" class="position-absolute toggle-password">
									<i class="bi bi-eye-slash"></i>
									<i class="bi bi-eye d-none"></i>
								</label>
								<div class="invalid-feedback">
									* Incorrect Password
								</div>
							</div>

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
						</div>

						<div class="modal-footer d-flex justify-content-between">
							<a href="forgot_password.php" class="text-decoration-none fs-6">Forgot password?</a>
							<div>
								<button type="submit" name="reset" class="btn" data-bs-dismiss="modal">Cancel</button>
								<button type="submit" class="btn btn-primary" name="update_password">Update Password</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>

		<!-- Change Profile Image Modal -->
		<div class="modal fade" id="uploadProfileModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
			<div class="modal-dialog modal-dialog-centered mx-auto">
				<div class="modal-content p-2">
					<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" enctype="multipart/form-data">
						<div class="modal-header">
							<h2 class="modal-title">Upload Profile Picture</h2>
							<button type="submit" name="reset" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>

						<div class="modal-body">
							<p class="small text-muted">Upload a new photo.<br>(Max 5MB, JPG/JPEG/PNG only)</p>
							<input class="form-control form-control-sm" type="file" name="profile" id="profileInput" accept="image/*">
							<!-- Crop Image -->
							<input type="hidden" name="cropped_profile" id="cropped_profile">
							<div class="mt-3 text-center">
								<img id="cropPreview" class="d-none">
							</div>
						</div>

						<div class="modal-footer">
							<button type="submit" name="reset" class="btn" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-primary" name="update_profile">Upload & Save</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		<!-- To display the modal directly after reload if there is any error -->
		<script> 
			document.addEventListener("DOMContentLoaded", function() { 
				<?php if (isset($hasPasswordError) && $hasPasswordError): ?> 
					var changePasswordModal = new bootstrap.Modal(document.getElementById('changePasswordModal')); 
					changePasswordModal.show(); 
				<?php endif; ?> 
			}); 
		</script>
    </article>

	<?php include "include/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="js/main.js"></script> 
</body>
</html>