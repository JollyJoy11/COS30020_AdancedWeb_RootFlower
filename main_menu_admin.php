<?php 
include "include/session.php"; 
include "include/db_connect.php";

if (isset($_SESSION['user']) && $_SESSION['role'] !== 'admin'){
	header('Location:main_menu.php');
	exit;
} 

$notification_data = displayNotification($conn, $_SESSION['user']);
$unread_notifications = $notification_data['notifications'];
$unread_count = $notification_data['count'];

// User Count
$userQuery = "SELECT COUNT(*) AS total_users FROM account_table WHERE trash = 'no' AND type = 'user'";
$userResult = mysqli_query($conn, $userQuery);
$userCount = mysqli_fetch_assoc($userResult)['total_users'];

// Active StudentWork Count
$studentworkQuery = "SELECT COUNT(*) AS total_studentworks FROM studentworks_table WHERE trash = 'no' AND approve_status = 'approved'";
$studentworkResult = mysqli_query($conn, $studentworkQuery);
$studentworkCount = mysqli_fetch_assoc($studentworkResult)['total_studentworks'];

// Pending StudentWork Count
$pendingStudentworkQuery = "SELECT COUNT(*) AS total_pendingstudentworks FROM studentworks_table WHERE trash = 'no' AND approve_status = 'pending'";
$pendingStudentworkResult = mysqli_query($conn, $pendingStudentworkQuery);
$pendingStudentworkCount = mysqli_fetch_assoc($pendingStudentworkResult)['total_pendingstudentworks'];

// Total Workshop Count
$workshopQuery = "SELECT COUNT(*) AS total_registration FROM workshop_table WHERE trash = 'no'";
$workshopResult = mysqli_query($conn, $workshopQuery);
$workshopCount = mysqli_fetch_assoc($workshopResult)['total_registration'];

// Pending Workshop Count
$pendingWorkshopQuery = "SELECT COUNT(*) AS total_pendingregistration FROM workshop_table WHERE trash = 'no' AND approve_status = 'pending'";
$pendingWorkshopResult = mysqli_query($conn, $pendingWorkshopQuery);
$pendingWorkshopCount = mysqli_fetch_assoc($pendingWorkshopResult)['total_pendingregistration'];

// Total AR Usage Count
$arQuery = "SELECT COUNT(*) AS total_ar FROM ar_table";
$arResult = mysqli_query($conn, $arQuery);
$arCount = mysqli_fetch_assoc($arResult)['total_ar'];

mysqli_close($conn);
?>
<!DOCTYPE html>

<html lang="en">
<!-- Description: Admin Main Menu -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 27/10/2025 -->
<!-- Validated: OK 6/10/2025 -->

<head>
    <title>Admin Dashboard | Root Flower</title>
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

<body class="fs-5" id="admin-menu">
	<?php include "include/header_admin.php"; ?>

    <article class="text-center p-5 pt-4 d-flex align-items-center justify-content-center flex-column text-dark">
		<h1>Admin Dashboard</h1>
		<p>Manage Your Floral World</p>

		<div class="d-flex gap-3 align-items-center justify-content-center flex-wrap pt-4">
			<div class="admin-wrapper position-relative card overflow-hidden shadow">
				<img src="img/products/product4.jpg" class="card-img" alt="Manage Users">

				<h2 class="title-label text-white ps-5 pe-4 py-2 fs-5 position-absolute z-2"><span class="d-inline-block"><i class='bi bi-people'></i>&ensp;<em>Users</em></span></h2>
				<div class="overlay-content position-absolute z-2 w-100 text-center lh-1">
					<p class="fs-1"><?php echo $userCount; ?></p>
					<p class="fw-bold">Total Registered Accounts</p>
				</div>
				<a href="manage_accounts.php" class="btn btn-primary mx-auto mb-4 d-block position-absolute bottom-0 z-2 start-0 end-0">Manage Users →</a>
			</div>
			
			<div class="admin-wrapper position-relative card overflow-hidden shadow">
				<img src="img/products/product6.jpg" class="card-img" alt="Manage Studentworks">

				<h2 class="title-label text-white ps-5 pe-4 py-2 fs-5 position-absolute z-2 d-flex align-items-center">
					<span class="d-inline-block">
						<i class='bi bi-brush'></i>&ensp;<em>Student Works</em>
					</span>
					<?php if ($pendingStudentworkCount > 0): ?>
						&ensp;<span class="badge rounded-pill bg-danger">
							<?php echo $pendingStudentworkCount; ?>
						</span>
					<?php endif; ?>
				</h2>
				<div class="overlay-content position-absolute z-2 w-100 text-center lh-1">
					<p class="fs-1"><?php echo $studentworkCount; ?></p>
					<p class="fw-bold">Published Floral Artworks</p>
				</div>
				<a href="manage_studentwork.php" class="btn btn-primary mx-auto mb-4 d-block position-absolute bottom-0 z-2 start-0 end-0">Manage Student Works →</a>
			</div>
			
			<div class="admin-wrapper position-relative card overflow-hidden shadow">
				<img src="img/workshop-card.jpg" class="card-img" alt="Manage Workshops">

				<h2 class="title-label text-white ps-5 pe-4 py-2 fs-5 position-absolute z-2">
					<span class="d-inline-block"><i class='bi bi-flower1'></i>&ensp;<em>Workshops</em></span>
					<?php if ($pendingWorkshopCount > 0): ?>
						&ensp;<span class="badge rounded-pill bg-danger">
							<?php echo $pendingWorkshopCount; ?>
						</span>
					<?php endif; ?>
				</h2>
				<div class="overlay-content position-absolute z-2 w-100 text-center lh-1">
					<p class="fs-1"><?php echo $workshopCount; ?></p>
					<p class="fw-bold">Total Sign-Ups</p>
				</div>
				<a href="manage_workshop_reg.php" class="btn btn-primary mx-auto mb-4 d-block position-absolute bottom-0 z-2 start-0 end-0">Manage Workshops →</a>
			</div>
			
			<div class="admin-wrapper position-relative card overflow-hidden shadow">
				<img src="img/products/product12.jpg" class="card-img" alt="Manage Products">

				<h2 class="title-label text-white ps-5 pe-4 py-2 fs-5 position-absolute z-2"><span class="d-inline-block"><i class="bi bi-box"></i>&ensp;<em>AR Creations</em></span></h2>
				<div class="overlay-content position-absolute z-2 w-100 text-center lh-1">
					<p class="fs-1"><?php echo $arCount; ?></p>
					<p class="fw-bold">Total Creations</p>
				</div>
				<a href="manage_ar.php" class="btn btn-primary mx-auto mb-4 d-block position-absolute bottom-0 z-2 start-0 end-0">Manage AR Creations →</a>
			</div>
		</div>
    </article>

	<footer class="text-dark ps-4 ps-lg-5 py-2 fs-6">
		<p>&copy; 2025 Root Flower</p>
	</footer>
	
<script src="js/main.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>
</html>