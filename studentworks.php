<?php 
include "include/session.php"; 
include "include/db_connect.php";

$notification_data = displayNotification($conn, $_SESSION['user']);
$unread_notifications = $notification_data['notifications'];
$unread_count = $notification_data['count'];
?>
<!DOCTYPE html>

<html lang="en">
<!-- Description: Student Works -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 4/10/2025 -->
<!-- Validated: OK 6/10/2025 -->

<!-- Please Refresh Ctrl + F5 If Video Overlap (Masonry Framework Loaded before video) -->
<head>
    <title>Student Creations | Root Flower</title>
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

	<a href="upload_studentwork.php" class="btn btn-primary position-fixed z-3 bottom-0 end-0 mb-5 me-0 pb-0 ps-3 fs-2 shadow d-flex align-items-center" id="upload-btn" title="Upload Your Creation">
		<i class="bi bi-cloud-upload-fill"></i> <span class="ms-3 fs-5 fw-normal pb-1">Upload Creation</span>
	</a>

    <article class="p-4 p-md-5 w-100">
		<h1 class="text-center pb-5">Student Creations</h1>

		<!-- Masonry display -->
		<div class="row" data-masonry='{"percentPosition": true }'>
			<?php
			$query = "SELECT s.id, s.first_name, s.last_name, s.workshop_id, s.workshop_media, s.likes, w.workshop_title, COUNT(c.id) AS comment_count FROM studentworks_table s JOIN workshop_table w ON s.workshop_id = w.id LEFT JOIN studentworkcomments_table c ON c.studentwork_id = s.id AND c.trash = 'no' WHERE s.trash='no' AND s.approve_status = 'approved' GROUP BY s.id ORDER BY s.id DESC";
			$result = mysqli_query($conn, $query);

			if (mysqli_num_rows($result) > 0) {
				while ($row = mysqli_fetch_assoc($result)) {
					$studentwork_id = $row['id'];
					$publisher = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
					$workshop_title = htmlspecialchars($row['workshop_title']);
					$media = array_map('trim', explode(',', $row['workshop_media']))[0];
					$likes = $row['likes'];
					$comment_count = $row['comment_count'];

					$hasLiked = false;

					$check_like_query = "SELECT 1 FROM studentworklikes_table WHERE studentwork_id = $studentwork_id AND email = '{$_SESSION['user']}' LIMIT 1";
					$like_result = mysqli_query($conn, $check_like_query);
					
					if (mysqli_num_rows($like_result) > 0) {
						$hasLiked = true;
					}

					$heart_class = $hasLiked ? 'bi-heart-fill text-danger' : 'bi-heart';

					echo 
					"<div class='col-sm-6 col-md-4 col-lg-3 mb-4'>
						<div class='card overflow-hidden student-card'>
							<a href='studentwork_detail.php?id=" . $studentwork_id . "'>";
					
						if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $media)) {
							echo "<img src='studentworks/$media' class='card-img-top' alt='$publisher's $workshop_title Creation'>";
						} elseif (preg_match('/\.mp4$/i', $media)) {
							echo "<video autoplay muted loop playsinline class='card-img-top d-block'>
									<source src='studentworks/$media' type='video/mp4'>
								</video>";
						}

								echo 
								"<div class='img-gradient position-absolute h-50 bottom-0 start-0 end-0 d-none'></div>
								<div class='overlay-text position-absolute text-light d-none w-100 justify-content-between align-items-end px-3'>
									<div>
										<p class='text-uppercase small m-0'>$workshop_title</p>
										<p class='fw-bold fs-5 m-0'>$publisher</p>
									</div>
									<div class='d-flex align-items-center gap-2'>
										<div><i class='bi $heart_class'></i> $likes</div>
										<div><i class='bi bi-chat'></i> $comment_count</div>
									</div>
								</div>
							</a>
					 	</div>
					</div>";
				}
			}

			mysqli_close($conn);
			?>
		</div>
    </article>

	<?php include "include/footer.php"; ?>

<script src="js/main.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js" integrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous" async></script>
<script src="https://unpkg.com/imagesloaded@5/imagesloaded.pkgd.js"></script> <!-- Load Masonry after image is loaded-->
</body>
</html>