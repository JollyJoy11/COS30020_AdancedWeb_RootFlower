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

// Link to the previous page
if ($_SESSION['role'] == 'user') {
    $back_link = "studentworks.php";
} else if ($_SESSION['role'] == 'admin'){
	$back_link = "manage_studentwork.php";
}

// Get student work details
$id = (int) $_GET['id'];
$query = "SELECT s.*, w.workshop_title, u.profile_image, COUNT(c.id) AS comment_count FROM studentworks_table s JOIN workshop_table w ON s.workshop_id = w.id JOIN user_table u ON s.email = u.email LEFT JOIN studentworkcomments_table c ON c.studentwork_id = s.id WHERE s.trash='no' AND s.id = $id GROUP BY s.id LIMIT 1";

if ($result = mysqli_query($conn, $query)){
	$row = mysqli_fetch_assoc($result);
	$publisher_email = $row['email'];
	$publisher = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
	$workshop = htmlspecialchars($row['workshop_title']);
	$media = array_map('trim', explode(',', $row['workshop_media']));
	$caption = $row['caption'];
	$date = date("d M Y", strtotime($row['upload_time']));
	$status = $row['approve_status'];
	$likes = $row['likes'];
	$comment_count = $row['comment_count'];
	$profile_image = $row['profile_image'];
}

// Check if user has liked the student work
$hasLiked = false;
$query = "SELECT 1 FROM studentworklikes_table WHERE studentwork_id = $id AND email = '{$_SESSION['user']}' LIMIT 1";
$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) > 0) {
    $hasLiked = true;
}

if (isset($_POST['like']) && !$hasLiked) {
	$insertLikeQuery = "INSERT INTO studentworklikes_table (studentwork_id, email) VALUES ($id, '{$_SESSION['user']}')";
	mysqli_query($conn, $insertLikeQuery);
	generateUserNotification($conn, $_SESSION['name'], $publisher_email, 'like', $id, 'studentworks_table');
	$hasLiked = true;
	$likes += 1;
	header("Location: studentwork_detail.php?id=$id");
	exit;

} else if (isset($_POST['like']) && $hasLiked) {
	$deleteLikeQuery = "DELETE FROM studentworklikes_table WHERE studentwork_id = $id AND email = '{$_SESSION['user']}'";
	mysqli_query($conn, $deleteLikeQuery);
	$hasLiked = false;
	$likes -= 1;
	header("Location: studentwork_detail.php?id=$id");
	exit;
}

// Handle new comment submission
if (isset($_POST['submit_comment'])) {
	$comment = trim($_POST['comment']);
	if (!empty($comment)) {
		$comment = mysqli_real_escape_string($conn, $comment);
		$insertCommentQuery = "INSERT INTO studentworkcomments_table (studentwork_id, email, comment_text) VALUES ($id, '{$_SESSION['user']}', '$comment')";
		mysqli_query($conn, $insertCommentQuery);
		$comment_count += 1;
		generateUserNotification($conn, $_SESSION['name'], $publisher_email, 'comment', $id, 'studentworks_table');

		header("Location: studentwork_detail.php?id=$id");
		exit;
	}
} else if (isset($_POST['comment_id'])) {
	$comment_id = (int) $_POST['comment_id'];
	$deleteCommentQuery = "DELETE FROM studentworkcomments_table WHERE id = $comment_id";
	mysqli_query($conn, $deleteCommentQuery);
	$comment_count -= 1;

	header("Location: studentwork_detail.php?id=$id");
	exit;
}

// Fetch comments
$comments = [];
$comments_query = "SELECT c.id, c.email, c.comment_text, c.upload_time, u.first_name, u.last_name FROM studentworkcomments_table c JOIN user_table u ON c.email = u.email WHERE c.studentwork_id = '$id' ORDER BY c.upload_time DESC";

$comments_result = mysqli_query($conn, $comments_query);

if (mysqli_num_rows($comments_result) > 0) {
	while ($c_row = mysqli_fetch_assoc($comments_result)) {
		$comments[] = $c_row;
	}
}

// Handle caption edit submission (Admin only)
if (isset($_POST['edit_caption'])) {
    $newCaption = sanitise_input($_POST['caption_text']);
    
    if (!empty($newCaption)) { 
        $escapedCaption = mysqli_real_escape_string($conn, $newCaption);
        $updateCaptionQuery = "UPDATE studentworks_table SET caption = '$escapedCaption' WHERE id = $id";

        if (mysqli_query($conn, $updateCaptionQuery)) {
            $caption = $newCaption; 

			$alert['success'] = "The caption has been updated successfully.";
			$_SESSION['alert'] = $alert;
        }
    } 
}

mysqli_close($conn);
?>
<!DOCTYPE html>

<html lang="en">
<!-- Description: Student Work Details -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 4/10/2025 -->
<!-- Validated: OK 6/10/2025 -->

<head>
    <title><?php echo $workshop; ?> by <?php echo $publisher; ?> | Root Flower</title>
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

<body class="fs-5 text-secondary">
	<?php 
		if ($_SESSION['role'] == 'user'){
			include "include/header.php"; 
		} else {
			include "include/header_admin.php"; 
		}
	?>

    <article class="p-4 p-md-5 w-100">
		<div class="position-relative d-flex align-items-center pb-3">
			<a href="<?php echo $back_link; ?>" class="text-secondary text-decoration-none position-absolute start-0 fs-3">
				<i class="bi bi-arrow-left-circle"></i>
			</a>
			<h1 class="mx-auto"><?php echo $publisher; ?>'s Creation Details</h1>
		</div>

		<div class="border rounded shadow overflow-hidden d-flex flex-column flex-lg-row" id="student-detail">
			<!-- Left -->
			<div class="h-100 d-flex justify-content-center align-items-center border-end position-relative" id="media-container">
				<!-- Left: Media carousel -->
				<div id="mediaCarousel" class="carousel slide h-100">
					<div class="carousel-inner h-100">
						<?php
						$isFirst = true;
						foreach ($media as $file) {
							echo "<div class='carousel-item " . ($isFirst ? "active" : "") . " h-100'>
								<div class='h-100 d-flex justify-content-center align-items-center'>";

							if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
								echo "<img src='studentworks/$file' alt='$publisher's $workshop Creation' class='img-fluid'>";
							} elseif (preg_match('/\.mp4$/i', $file)) {
								echo "<video class='d-block' controls playsinline muted autoplay>
										<source src='studentworks/$file' type='video/mp4'>
									</video>";
							}
							echo "</div></div>";
							$isFirst = false;
						}
						?>
					</div>
				</div>

				<!-- Carousel controls -->
				<?php if (count($media) > 1): ?>
					<button class="carousel-control-prev fs-3 text-secondary h-25 my-auto" type="button" data-bs-target="#mediaCarousel" data-bs-slide="prev">
						<i class='bi bi-caret-left-fill' aria-hidden="true"></i>
					</button>
					<button class="carousel-control-next fs-3 text-secondary h-25 my-auto" type="button" data-bs-target="#mediaCarousel" data-bs-slide="next">
						<i class='bi bi-caret-right-fill' aria-hidden="true"></i>
					</button>
				<?php endif; ?>

				<!-- Indicator -->
				<div id="carousel-indicator" class="position-absolute top-0 end-0 mt-2 me-3 px-2 py-1 bg-dark bg-opacity-50 text-white rounded small">
					1 / <?php echo count($media); ?>
				</div>

				<!-- Bottom gradient and overlay text -->
				<div class="img-gradient position-absolute h-25 bottom-0 start-0 end-0 pe-none"></div>
				<div class='overlay-text position-absolute text-light w-100 px-4 d-flex align-items-center justify-content-between z-2'>
					<div class="d-flex gap-2 flex-row">
						<img src="profile_images/<?php echo $profile_image == null ? 'default.png' : $profile_image; ?>" width="32" alt="<?php echo $publisher; ?> Image" class="rounded-circle shadow-sm border border-dark">
						<p class='fw-bold fs-5 m-0'><?php echo $publisher; ?></p>
					</div>
					<div class="d-flex align-items-center gap-3 mb-1">
						<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $id; ?>" method="post" class="d-inline">
							<input type="hidden" name="studentwork_id" value="<?php echo $id; ?>">
							<button type="submit" class="bg-transparent p-0 m-0 text-light fs-5 border-0 <?php echo ($status == "pending" || $status == "rejected") ? 'pe-none' : ''; ?>" name="like" <?php echo ($status == "pending" || $status == "rejected") ? "disabled" : ""; ?>>
								<i class="bi bi-heart<?php echo $hasLiked ? '-fill text-danger' : ''; ?>"></i> 
							</button>
							<?php echo $likes; ?>
						</form>
						<div><i class="bi bi-chat"></i> <?php echo $comment_count; ?></div>
					</div>
				</div>
			</div>
			
			<!-- Right -->
			<div class="p-4 w-100 h-100 d-flex flex-column flex-grow-1">
				<?php if ($_SESSION['role'] === 'admin'): ?>
					<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $id; ?>" method="POST" class="w-100">
						<div class="mb-2">
							<textarea class="form-control" name="caption_text" placeholder="Edit caption" maxlength="255"><?php echo htmlspecialchars($caption); ?></textarea>
						</div>
						<button type="submit" class="btn btn-primary btn-sm" name="edit_caption">Save Caption</button>
					</form>
				<?php else: ?>
					<p class="m-0 pe-5 caption"><?php echo $caption; ?></p>
				<?php endif; ?>

				<p class="fs-6 pt-1 workshop-ln">Created during <strong><em><?php echo $workshop; ?> Workshop</em></strong></p>
				<p class="fs-6">Uploaded on <?php echo $date; ?></p>
				<hr>
				<!-- Comments container -->
				<div class="flex-grow-1 d-flex flex-column justify-content-center align-items-center" id="comments-display">
					<?php if ($status == 'pending'): ?>
						<div class="text-center">
							<p class="m-0"><strong>Review in progress</strong></p>
							<p class="m-0">We’ll notify you once it has been reviewed.</p>
						</div>
					<?php elseif ($status == 'rejected'): ?>
						<div class="text-center">
							<p class="m-0"><strong>Submission Rejected</strong></p>
							<p class="m-0">This creation did not meet the review requirements.</p>
						</div>
					<?php elseif (empty($comments)): ?>
						<div class="text-center">
							<p class="m-0"><strong>No comments yet</strong></p>
							<p class="m-0">Be the first to comment.</p>
						</div>
					<?php else: ?>
						<div class="w-100 h-100 overflow-auto" id="comments-list">
							<?php foreach ($comments as $comment): ?>
								<div class="comment-item mb-2">
									<p class="m-0">
										<strong><?php echo htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']); ?></strong> 
										<span class="text-muted small">on <?php echo date("d M Y", strtotime($comment['upload_time'])); ?></span>
										
										<?php if ($comment['email'] === $_SESSION['user'] || $_SESSION['role'] === 'admin'): ?>
											<button type="button" class="btn p-0 mt-1 ms-2 border-0 text-secondary delete-btn d-none" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal<?php echo $comment['id']; ?>">
												<i class="bi bi-trash"></i>
											</button>
										<?php endif; ?>
									</p>
									<p class="m-0"><?php echo $comment['comment_text']; ?></p>
								</div>

								<!-- Delete Confirmation Modal -->
								<div class="modal fade" id="confirmDeleteModal<?php echo $comment['id']; ?>" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
									<div class="modal-dialog modal-dialog-centered mx-auto">
										<div class="modal-content">
											<div class="modal-header">
												<h2>Confirm Deletion</h2>
												<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
											</div>
											<div class="modal-body">
												Are you sure you want to delete this comment? This action cannot be undone.
											</div>
											<div class="modal-footer">
												<button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
												<form id="deleteCommentForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $id; ?>" class="d-inline">
													<input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
													<button type="submit" class="btn btn-danger text-light border-danger" name="delete_comment">Delete</button>
												</form>
											</div>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<?php if ($status == 'approved'): ?>
					<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $id; ?>" name="comment-form" method="post" class="w-100 mt-auto">
						<div class="input-group">
							<input type="text" placeholder="Add comment..." class="form-control" name="comment">
							<button type="submit" class="btn btn-primary py-0" name="submit_comment"><i class="bi bi-arrow-up"></i></button>
						</div>
					</form>
				<?php endif; ?>
			</div>
		</div>
    </article>

	<?php 
		if ($_SESSION['role'] == 'user'){
			include "include/footer.php"; 
		} 
	?>

<script src="js/main.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>
</html>