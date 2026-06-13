<?php 
include "include/session.php"; 
include "include/db_connect.php";

if ($_SESSION['role'] !== 'user'){
	header('Location:main_menu_admin.php');
	exit;
} 

$notification_data = displayNotification($conn, $_SESSION['user']);
$unread_notifications = $notification_data['notifications'];
$unread_count = $notification_data['count'];

$categoryNames = [
    'bouquet' => 'Bouquets',
    'basket' => 'Flower Baskets',
    'stand' => 'Flower Stands',
    'special' => 'Specials',
    'anniversary' => 'Anniversary',
    'graduation' => 'Graduation',
    'wedding' => 'Wedding',
    'cny' => 'Chinese New Year'
];

$selectedCategory = $_GET['product'] ?? '';
?>

<!DOCTYPE html>

<html lang="en">
<!-- Description: Products -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 16/9/2025 -->
<!-- Validated: OK 6/10/2025 -->

<head>
    <title>Shop Our Flowers | Root Flower</title>
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

    <div id="delivery-banner" class="alert alert-dismissible fade show py-2 px-4 text-center small mb-0 border-0 border-bottom" style="background-color:#f5ede8;border-radius:0;">
        <i class="bi bi-truck me-1"></i> Enjoy <strong>free delivery</strong> on orders above <strong>RM 300</strong>. Standard delivery is RM 20.
        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        if (sessionStorage.getItem('deliveryBannerDismissed')) {
            document.getElementById('delivery-banner').remove();
        }
        document.getElementById('delivery-banner')?.addEventListener('closed.bs.alert', () => {
            sessionStorage.setItem('deliveryBannerDismissed', '1');
        });
    </script>

    <article class="text-secondary p-4 p-md-5 row w-100 g-3">
		<!-- Large Screen Filter -->
		<aside class="fs-6 col-2 pt-5 mt-5 sticky-top d-none d-md-block z-0">
			<p><a href="products.php" class="text-decoration-none">All</a></p>
			<h4 class="text-uppercase fs-5">By Category</h4>
			<ul class="list-unstyled mb-4">
				<li><a href='?product=bouquet' class='text-decoration-none <?php echo $selectedCategory === 'bouquet' ? 'active' : ''; ?>'>Bouquets</a></li>
				<li><a href='?product=basket' class='text-decoration-none <?php echo $selectedCategory === 'basket' ? 'active' : ''; ?>'>Flower Baskets</a></li>
				<li><a href='?product=stand' class='text-decoration-none <?php echo $selectedCategory === 'stand' ? 'active' : ''; ?>'>Flower Stands</a></li>
				<li><a href='?product=special' class='text-decoration-none <?php echo $selectedCategory === 'special' ? 'active' : ''; ?>'>Specials</a></li>
			</ul>
			<hr>
			<h4 class="text-uppercase fs-5">By Occasions</h4>
			<ul class="list-unstyled">
				<li><a href='?product=anniversary' class='text-decoration-none <?php echo $selectedCategory === 'anniversary' ? 'active' : ''; ?>'>Anniversary</a></li>
				<li><a href='?product=graduation' class='text-decoration-none <?php echo $selectedCategory === 'graduation' ? 'active' : ''; ?>'>Graduation</a></li>
				<li><a href='?product=wedding' class='text-decoration-none <?php echo $selectedCategory === 'wedding' ? 'active' : ''; ?>'>Wedding</a></li>
				<li><a href='?product=cny' class='text-decoration-none <?php echo $selectedCategory === 'cny' ? 'active' : ''; ?>'>Chinese New Year</a></li>
			</ul>
			<a href="ar_flowerarrangement.php" class="btn btn-primary"><i class="bi bi-box"></i>&ensp;Design Your Own</a>
		</aside>

		<div class="col-12 col-md-10 ps-md-5">
			<div class="d-flex justify-content-between">
				<h1>Products<?php echo $selectedCategory ? "<span class='fs-4'>&ensp;/&ensp;".($categoryNames[$selectedCategory] ?? '')."</span>" : ""; ?></h1>
				<!-- Small Screen Filter Dropdown Button -->
				<button class="btn d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterDropdown" aria-expanded="false" aria-controls="filterDropdown">
					<i class="bi bi-funnel"></i>
				</button>
			</div>

			<!-- Small Screen Filter -->
			<div class="collapse fs-6" id="filterDropdown">
				<p><a href="products.php" class="text-decoration-none">All</a></p>
				<h4 class="text-uppercase fs-5">By Category</h4>
				<ul class="list-unstyled mb-4">
					<li><a href='?product=bouquet' class='text-decoration-none <?php echo $selectedCategory === 'bouquet' ? 'active' : ''; ?>'>Bouquets</a></li>
					<li><a href='?product=basket' class='text-decoration-none <?php echo $selectedCategory === 'basket' ? 'active' : ''; ?>'>Flower Baskets</a></li>
					<li><a href='?product=stand' class='text-decoration-none <?php echo $selectedCategory === 'stand' ? 'active' : ''; ?>'>Flower Stands</a></li>
					<li><a href='?product=special' class='text-decoration-none <?php echo $selectedCategory === 'special' ? 'active' : ''; ?>'>Specials</a></li>
				</ul>
				<hr>
				<h4 class="text-uppercase fs-5">By Occasions</h4>
				<ul class="list-unstyled">
					<li><a href='?product=anniversary' class='text-decoration-none <?php echo $selectedCategory === 'anniversary' ? 'active' : ''; ?>'>Anniversary</a></li>
					<li><a href='?product=graduation' class='text-decoration-none <?php echo $selectedCategory === 'graduation' ? 'active' : ''; ?>'>Graduation</a></li>
					<li><a href='?product=wedding' class='text-decoration-none <?php echo $selectedCategory === 'wedding' ? 'active' : ''; ?>'>Wedding</a></li>
					<li><a href='?product=cny' class='text-decoration-none <?php echo $selectedCategory === 'cny' ? 'active' : ''; ?>'>Chinese New Year</a></li>
				</ul>
			</div>

			<!-- Product grid display -->
			<div class="row" id="product-page">
				<?php 
					$query = "SELECT * FROM products_table WHERE trash='no'";
					$result = mysqli_query($conn, $query);

					if (mysqli_num_rows($result) > 0) {
						while ($row = mysqli_fetch_assoc($result)){
							$name = $row['product_name'];
							$price = $row['price'];
							$category = $row['category'];
							$occasion = $row['occasion'];
							$image = $row['product_image'];
							$rating = $row['rating'];
							$reviews = $row['reviews'];

							if ($selectedCategory && $category !== $selectedCategory && $occasion !== $selectedCategory) {
								continue; 
							}

							// Generate hover image filename
							$hoverImg = str_replace(".jpg", "_alt.jpg", $image);

							$stars = "";
							$fullStars = floor($rating);
							$halfStar = ($rating - $fullStars >= 0.5) ? 1 : 0;
							$emptyStars = 5 - $fullStars - $halfStar;

							for ($i = 0; $i < $fullStars; $i++) {
								$stars .= "<i class='bi bi-star-fill'></i>";
							}
							if ($halfStar) {
								$stars .= "<i class='bi bi-star-half'></i>";
							}
							for ($i = 0; $i < $emptyStars; $i++) {
								$stars .= "<i class='bi bi-star'></i>";
							}
							$stars .= " <span class='small text-muted'>(" . number_format($rating, 1) . "/5)</span>";

							echo 
							"<div class='col-12 col-sm-6 col-lg-4 g-4'>
								<div class='card d-flex flex-column h-100 rounded-0 border-0'>
									<div class='product-img position-relative overflow-hidden'>
										<img src='$image' class='card-img-top main-img rounded-0' alt='$name'>
										" . (file_exists($hoverImg) 
											? "<img src='$hoverImg' class='card-img-top hover-img position-absolute top-0 start-0 w-100 h-100 rounded-0' alt='$name'>" 
											: ""
										) . "
									</div>

									<div class='card-body d-flex flex-column'>
										<h3 class='card-title fs-5'>$name</h3>
										<p class='fw-bold m-0 small'>RM $price</p>
										
										<div class='mb-2 small text-warning'>
											$stars
											<span class='small text-muted'>($reviews reviews)</span>
										</div>";

									echo "<div class='mb-3'>";
										if (!empty($category) && isset($categoryNames[$category])) {
											echo "<span class='badge badge-pink text-secondary'>".$categoryNames[$category]."</span> ";
										}
										if (!empty($occasion) && isset($categoryNames[$occasion])) {
											echo "<span class='badge badge-green text-secondary'>".$categoryNames[$occasion]."</span>";
										}

									echo "</div>
										
										<form method='POST' action='add_to_cart.php' class='mt-auto w-100'>
											<input type='hidden' name='product_id' value='{$row['id']}'>
											<input type='hidden' name='redirect' value='products.php" . ($selectedCategory ? "?product=$selectedCategory" : "") . "'>
											<button type='submit' class='btn btn-primary w-100'>Add to Basket</button>
										</form>
									</div>
								</div>
							</div>";
						}
					} 

					mysqli_close($conn);
				?>
			</div>
		</div>
    </article>

	<?php include "include/footer.php"; ?>

<script src="js/main.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>
</html>