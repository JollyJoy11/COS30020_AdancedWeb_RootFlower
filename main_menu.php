<?php 
include "include/session.php"; 
include "include/db_connect.php";

$notification_data = displayNotification($conn, $_SESSION['user']);
$unread_notifications = $notification_data['notifications'];
$unread_count = $notification_data['count'];

if (isset($_SESSION['user']) && $_SESSION['role'] !== 'user'){
	header('Location:main_menu_admin.php');
	exit;
} 

mysqli_close($conn);
?>
<!DOCTYPE html>

<html lang="en">
<!-- Description: Main Menu -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 13/9/2025 -->
<!-- Validated: OK 6/10/2025 -->

<head>
    <title>Discover Our Floral World | Root Flower</title>
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
	<?php include "include/header.php"; ?>

    <article id="main_menu" class="text-center p-5 d-flex align-items-center justify-content-center flex-column text-secondary">
		<h1>Explore Our Floral World</h1>
		<p>Where Every Bloom Tells a Story</p>

		<div class="d-flex gap-3 align-items-center justify-content-center flex-wrap pt-5">
			<div class="img-wrapper position-relative card overflow-hidden shadow">
				<img src="img/products/product4.jpg" class="card-img" alt="Products">

				<h2 class="title-label text-white px-3 py-2 fs-5 position-absolute z-2">Products</h2>
				<div class="overlay d-flex flex-column justify-content-between text-white p-3 position-absolute pt-4 bottom-0 h-50">
					<p class="small">Explore our exquisite collection of bouquets, baskets and flower stands—crafted with care for every occasion.</p>
					<a href="products.php" class="btn btn-light btn-sm ms-auto mb-2">Shop Now →</a>
				</div>
			</div>
			
			<div class="img-wrapper position-relative card overflow-hidden shadow">
				<img src="img/workshop.jpg" class="card-img" alt="Workshops">

				<h2 class="title-label text-white px-3 py-2 fs-5 position-absolute z-2">Workshops</h2>
				<div class="overlay d-flex flex-column justify-content-between text-white p-3 position-absolute pt-4 bottom-0 h-50">
					<p class="small">Join our workshops and learn floral design with guidance from expert instructors.</p>
					<a href="workshops.php" class="btn btn-light btn-sm ms-auto mb-2">Explore Workshops →</a>
				</div>
			</div>
			
			<div class="img-wrapper position-relative card overflow-hidden shadow">
				<img src="img/products/product6.jpg" class="card-img" alt="Student Works">

				<h2 class="title-label text-white px-3 py-2 fs-5 position-absolute z-2">Student Works</h2>
				<div class="overlay d-flex flex-column justify-content-between text-white p-3 position-absolute pt-4 bottom-0 h-50">
					<p class="small">Discover student creations that showcase unique styles in floral design.</p>
					<a href="studentworks.php" class="btn btn-light btn-sm ms-auto mb-2">View Student Works →</a>
				</div>
			</div>
			
			<div class="img-wrapper position-relative card overflow-hidden shadow">
				<img src="img/products/product12.jpg" class="card-img" alt="Flower Name">

				<h2 class="title-label text-white px-3 py-2 fs-5 position-absolute z-2">Flower Name</h2>
				<div class="overlay d-flex flex-column justify-content-between text-white p-3 position-absolute pt-4 bottom-0 h-50">
					<p class="small">Snap, upload, and discover flower names, prices, and descriptions in PDF.</p>
					<a href="flower.php" class="btn btn-light btn-sm ms-auto mb-2">Discover Flowers →</a>
				</div>
			</div>
		</div>
    </article>

	<?php include "include/footer.php"; ?>
	
<script src="js/main.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>
</html>