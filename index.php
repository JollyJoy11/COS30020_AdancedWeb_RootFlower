<?php 
include "include/database.php";
include "include/session.php"; 

if (isset($_SESSION['user']) && $_SESSION['role'] === 'user'){
	header('Location:main_menu.php');
	exit;
} else if (isset($_SESSION['user']) && $_SESSION['role'] === 'admin'){
    header('Location:main_menu_admin.php');
    exit;
}
?>
<!DOCTYPE html>

<html lang="en">
<!-- Description: Homepage -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 7/9/2025 -->
<!-- Validated: OK 6/10/2025 -->

<head>
    <title>Root Flower</title>
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

    <article>
        <div id="hero" class="position-relative w-100 d-flex flex-column justify-content-center">
            <div id="gradient-overlay" class="position-absolute w-100 h-100 start-0 z-0"></div>

            <div id="hero-text" class="z-1">
                <h1>Bringing Flowers into Your Everyday Life</h1>
                <p id="intro" class="py-4 w-100">Welcome to Root Flowers<br>We're a cozy little florist in Kuching bringing you hand-tied bouquets, dreamy arrangements, and floral workshops made with love. Whether you're here to shop, learn, or simply be inspired, we've got something blooming just for you. </p>
                <a href="main_menu.php" class="btn border-2">Explore More&nbsp;&#10230;</a>
            </div>
        </div>

        <!-- Randomly display image from the product folder -->
        <section class="py-4 text-center">
            <div class="container">
                <h2 class="pt-3">Products</h2>
                <p>Fresh Blooms, Handcrafted with Love.</p>
                <div class="row px-5 py-3 g-4">
                    <?php
                        $product_folder = "img/products"; 

                        $all_files = glob($product_folder . "/*.{jpg,jpeg,png}", GLOB_BRACE);

                        $products = array_filter($all_files, function($file) {
                            return !str_contains($file, "_alt");
                        });

                        if ($products) {
                            $random_products = array_rand($products, 6);

                            foreach ($random_products as $index) {
                                echo "
                                <div class='col-12 col-sm-6 col-lg-4 animated-item'>
                                    <img src='" . $products[$index] . "' alt='Flower Product' class='img-fluid rounded shadow-sm'>
                                </div>";
                            }
                        } 
                    ?>
                </div>

                <a href="products.php" class="btn animated-item">Shop Now</a>
            </div>
        </section>

        <!-- Display reviews on the workshop -->
        <section class="mt-5 text-center position-relative" id="workshop-index">
            <div class="mx-auto animated-item position-relative z-1">
                <h2 class="my-5 pt-5">Workshop</h2>
                <p>Hands-on Learning, Blooming Ideas.</p>
                <div class="d-flex flex-column flex-lg-row px-5 justify-content-center align-items-center">
                    <video autoplay muted loop class="rounded shadow ms-lg-4 w-100 d-block">
                        <source src="img/workshop_vid.mp4" type="video/mp4">
                    </video>

                    <div class="d-flex flex-column text-start ps-lg-4 ms-lg-5 py-4 gap-4">
                        <div class="card shadow border-0 review w-75">
                            <div class="card-body">
                                <div class="d-flex">
                                    <i class="bi bi-quote fs-1"></i>
                                    <p class="card-text pt-2">I never thought I could arrange flowers like a pro — this workshop made it possible! </p> 
                                </div>
                                <p class="small text-end pe-3 pt-2 mb-0"><strong>~ Lara Croft, Interior Designer</strong></p>
                            </div>
                        </div>

                        <div class="card shadow border-0 review w-75">
                            <div class="card-body">
                                <div class="d-flex">
                                    <i class="bi bi-quote fs-1"></i>
                                    <p class="card-text pt-2">The skills I learned here helped me create arrangements for my own events. </p> 
                                </div>
                                <p class="small text-end pe-3 pt-2 mb-0"><strong>~ Natalie Lee, Event Planner</strong></p>
                            </div>
                        </div>

                        <div class="card shadow border-0 review w-75">
                            <div class="card-body">
                                <div class="d-flex">
                                    <i class="bi bi-quote fs-1"></i>
                                    <p class="card-text pt-2">Practical tips I can now use for home décor and gifting. </p> 
                                </div>
                                <p class="small text-end pe-3 pt-2 mb-0"><strong>~ Angela North, University Student</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="text-end m-0 pb-5"><a href="workshops.php" class="btn border-2 text-dark">See Upcoming Workshops &ensp;<i class="bi bi-arrow-right-circle"></i></a></p>
            </div>

            <div class="position-absolute w-100 z-0 workshop-bg"></div>
        </section>
    </article>
    
    <?php include "include/nologin_footer.php"; ?>

<script src="js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>
</html>