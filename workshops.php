<?php 
include "include/session.php"; 

$notification_data = displayNotification($conn, $_SESSION['user']);
$unread_notifications = $notification_data['notifications'];
$unread_count = $notification_data['count'];

if ($_SESSION['role'] !== 'user'){
	header('Location:main_menu_admin.php');
	exit;
} 

include "include/workshops_info.php";
$today = date("Y-m-d");
?>
<!DOCTYPE html>

<html lang="en">
<!-- Description: Workshops -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 21/9/2025 -->
<!-- Validated: OK 6/10/2025 -->

<head>
    <title>Workshops | Root Flower</title>
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
	<?php include "include/header.php"; ?>
	
    <article class="pb-5">
		<!-- Display the most upcoming hobby workshop countdown -->
		<div id="workshop-bg" class="container-fluid p-5">
			<div class="row align-items-center">
				<?php 
				$workshop = $workshops[0]; 
				$upcoming = null;
				$sessionIndex = 0;

				foreach ($workshop['schedule'] as $idx => $session) {
					if ($session['date'] >= $today) {
						$upcoming = $session;
						$sessionIndex = $idx;
						break;
					}
				}

				$times = explode(" - ", $upcoming['time']); 
				?>

				<!-- Image & date grid -->
				<div class="col-lg-6 col-md-12 mb-4 mb-lg-0">
					<div class="d-flex mx-xl-5">
						<figure class="rounded overflow-hidden shadow-lg me-4">
							<img src="img/workshop-display.jpeg" alt="Floral Workshop">
						</figure>
						<div class="mt-5">
							<div id="colour-block" class="rounded my-3 text-light px-3 py-1 py-sm-2"><strong><?php echo date("d M Y", strtotime($upcoming['date'])); ?></strong></div>
							<figure class="rounded overflow-hidden shadow-lg">
								<img src="img/workshop-basket.jpg" alt="Flower Basket">
							</figure>
						</div>
					</div>
				</div>
				<script>
					window.workshopDate = new Date("<?php echo date("Y-m-d", strtotime($upcoming['date'])); ?> <?php echo date("H:i:s", strtotime($times[0])); ?>").getTime();
				</script>

				<div class="col-lg-6 col-md-12">
					<p class="text-uppercase small mb-0">Featured Workshop</p>
					<h1 class="text-uppercase">Hobby Class</h1>
					<p class="text-dark">A hands-on floral workshop you'll love. Reserve your spot now and be part of our upcoming floral class. Don't miss this chance to learn and create before time runs out.</p>
					<!-- Workshop Countdown -->
					<div class="timebox d-flex align-items-center text-center gap-4 gap-sm-5">
						<div class="time">
							<h3 id="days" class="mb-0">00</h3>
							<p class="m-0">Days</p>
						</div>
						<div class="time">
							<h3 id="hours" class="mb-0">00</h3>
							<p class="m-0">Hours</p>
						</div>
						<div class="time">
							<h3 id="minutes" class="mb-0">00</h3>
							<p class="m-0">Minutes</p>
						</div>
						<div class="time">
							<h3 id="seconds" class="mb-0">00</h3>
							<p class="m-0">Seconds</p>
						</div>
					</div>
					
					<a href="workshop_reg.php?id=1&session=<?php echo "$sessionIndex"; ?>" class="btn btn-primary mt-4">Register Now</a>
				</div>
			</div>
		</div>

		<section class="text-center" id="workshop_characteristic">
			<h2>Why Join Our Workshops?</h2>
			<div class="d-flex flex-column flex-md-row justify-content-center gap-2 gap-lg-5 py-5">
				<p class="mx-5"><i class="bi bi-flower1 fs-1"></i><br>Hands-On Learning</p>
				<p class="mx-5"><i class="bi bi-gift fs-1"></i><br>Take-Home Creations</p>
				<p class="mx-5"><i class="bi bi-people fs-1"></i><br>Small Class Sizes</p>
				<p class="mx-5"><i class="bi bi-mortarboard fs-1"></i><br>Guided by Experts</p>
			</div>
		</section>

		<!-- Upcoming workshops -->
		<section class="px-5">
			<h2 class="text-center">Upcoming Workshops</h2>
			<p class="text-center">August 2025 - January 2026</p>

			<?php foreach ($workshops as $workshop): ?>
				<div class="d-flex flex-xl-row flex-column my-5 m-lg-5 gap-3 gap-xl-5 align-items-center">
					<figure class="workshop-img border shadow rounded overflow-hidden flex-shrink-0">
						<img src=<?= $workshop['image']; ?> alt=<?= $workshop['title']; ?>>
					</figure>

					<?php if ($workshop['title'] === "Hobby Class"): ?>
						<div>
							<?php
							$closestIndex = null;

							// To find the most upcoming schedule 
							foreach ($workshop['schedule'] as $i => $s) {
								if ($s['date'] >= $today) {
									$closestIndex = $i;
									break; 
								}
							}

							if ($closestIndex === null) {
								$closestIndex = 0;
							}
							?>

							<!-- Nav pills for Available Date -->
							<ul class="nav nav-pills small" id="hobbyClassTab" role="tablist">
								<?php foreach ($workshop['schedule'] as $index => $session): ?>
									<li class="nav-item" role="presentation">
										<button class="nav-link me-3 mb-3 py-1 px-2 text-light <?= $index === $closestIndex ? 'active' : '' ?>" id="session-<?= $index ?>-tab" data-bs-toggle="tab" data-bs-target="#session-<?= $index ?>" type="button" role="tab"><?= date("d M Y", strtotime($session['date'])); ?></button>
									</li>
								<?php endforeach; ?>
							</ul>

							<h3 class="text-uppercase"><?= $workshop['title']; ?></h3>
							<p><?= $workshop['description']; ?></p>

							<div class="tab-content mt-3">
								<?php foreach ($workshop['schedule'] as $index => $session): ?>
									<div class="tab-pane <?= $index === $closestIndex ? 'show active' : '' ?>" id="session-<?= $index ?>" role="tabpanel">
										<p class="m-0"><strong>Best For:</strong>&emsp;<?= $workshop['level']; ?></p>
										<p class="m-0"><strong>Time:</strong>&emsp;<?= $session['time']; ?></p>
										<p class="m-0"><strong>Content:</strong>&emsp;<?= $session['content']; ?></p>
										<p class="m-0"><strong>Venue:</strong>&emsp;<?= $session['venue']; ?></p>
										<p class="m-0"><strong>Price:</strong>&emsp;<?= $workshop['price']; ?></p>

										<!-- Display the button based on whether the month is upcoming -->
										<?php if ($today <= $session['date']): ?>
											<a href="workshop_reg.php?id=<?= $workshop['id']; ?>&session=<?= $index ?>" class="btn btn-primary mt-3">Book Your Seat</a>
										<?php else: ?>
											<p class="btn btn-disable mt-3 m-0 pe-none">No Longer Available</p>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					
					<?php elseif ($workshop['title'] === "Handtied Bouquet"): ?>
						<div>
							<?php
							$closestIndex = null;

							// To find the most upcoming schedule 
							foreach ($workshop['schedule'] as $i => $s) {
								if ($s['days'][0]['date'] >= $today) {
									$closestIndex = $i;
									break; 
								}
							}

							if ($closestIndex === null) {
								$closestIndex = 0;
							}
							?>

							<!-- Nav pills for Available Date -->
							<ul class="nav nav-pills small" id="handtiedTab" role="tablist">
								<?php foreach ($workshop['schedule'] as $index => $session): ?>
									<li class="nav-item" role="presentation">
										<button class="nav-link me-3 mb-3 py-1 px-2 text-light <?= $index === $closestIndex ? 'active' : '' ?>" id="handtied-<?= $index ?>-tab" data-bs-toggle="tab" data-bs-target="#handtied-<?= $index ?>" type="button" role="tab"><?= date("d", strtotime($session['days'][0]['date'])) . " - " . date("d M Y", strtotime($session['days'][1]['date'])); ?></button>
									</li>
								<?php endforeach; ?>
							</ul>

							<h3 class="text-uppercase"><?= $workshop['title']; ?></h3>
							<p><?= $workshop['description']; ?></p>

							<div class="tab-content mt-3">
								<?php foreach ($workshop['schedule'] as $index => $session): ?>
									<div class="tab-pane <?= $index === $closestIndex ? 'show active' : '' ?>" id="handtied-<?= $index ?>" role="tabpanel">
										<p class="m-0"><strong>Best For:</strong>&emsp;<?= $workshop['level']; ?></p>
										<p class="m-0"><strong>Classes:</strong>&emsp;<?= $workshop['classes']; ?></p>
										<p class="m-0"><strong>Venue:</strong>&emsp;<?= $session['venue']; ?></p>
										<p class="m-0"><strong>Price:</strong>&emsp;<?= $workshop['price']; ?></p>

										<div class="table-responsive mt-3">
											<table class="table table-bordered table-striped workshop-table">
												<thead class="table-light">
													<tr>
														<th>Day</th>
														<th>Time</th>
														<th>Bouquets</th>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td class="text-center">1</td>
														<td><?= $session['days'][0]['time']; ?></td>
														<td><?= $session['days'][0]['content']; ?></td>
													</tr>
													<tr>
														<td class="text-center">2</td>
														<td><?= $session['days'][1]['time']; ?></td>
														<td><?= $session['days'][1]['content']; ?></td>
													</tr>
												</tbody>
											</table>
										</div>

										<!-- Display the button based on whether it has passed -->
										<?php if ($today <= $session['days'][0]['date']): ?>
											<a href="workshop_reg.php?id=<?= $workshop['id']; ?>&session=<?= $index ?>" class="btn btn-primary mt-3">Book Your Seat</a>
										<?php else: ?>
											<p class="btn btn-disable mt-3 m-0 pe-none">No Longer Available</p>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</div>
						</div>

					<?php else: ?>
						<div>
							<?php
							$closestIndex = 0;
							$currentMonth = strtotime("first day of " . date("F Y", strtotime($today)));
							$idx = 0;

							foreach ($workshop['batches'] as $monthName => $s) {
								if (strtotime("first day of " . $monthName) > $currentMonth) {
									$closestIndex = $idx;
									break;
								}
								$idx++;
							}

							if ($closestIndex === null) {
								$closestIndex = 0;
							}
							?>

							<!-- Nav pills for Batch -->
							<ul class="nav nav-pills small" id="floristTab<?= $workshop['id'] ?>" role="tablist">
								<?php $i = 0; ?>
								<?php foreach ($workshop['batches'] as $monthName => $month): ?>
									<li class="nav-item" role="presentation">
										<button class="nav-link me-3 mb-3 py-1 px-2 text-light <?= $i === $closestIndex ? 'active' : '' ?>" id="session-<?= $workshop['id'] ?>-<?= $i ?>-tab" data-bs-toggle="tab" data-bs-target="#session-<?= $workshop['id'] ?>-<?= $i ?>" type="button" role="tab"><?= $monthName; ?></button>
									</li>
									<?php $i++; ?>
								<?php endforeach; ?>
							</ul>

							<h3 class="text-uppercase"><?= $workshop['title']; ?></h3>
							<p><?= $workshop['description']; ?></p>

							<div class="tab-content mt-3">
							<?php $i = 0; ?>
							<?php foreach ($workshop['batches'] as $monthName => $month): ?>
								<div class="tab-pane <?= $i === $closestIndex ? 'show active' : '' ?>" 
									id="session-<?= $workshop['id'] ?>-<?= $i ?>" role="tabpanel">
									
									<p class="m-0"><strong>Best For:</strong>&emsp;<?= $workshop['level']; ?></p>
									<p class="m-0"><strong>Classes:</strong>&emsp;<?= $workshop['classes']; ?></p>
									<p class="m-0"><strong>Time:</strong>&emsp;<?= $workshop['time']; ?></p>
									<p class="m-0"><strong>Venue:</strong>&emsp;<?= $month['venue']; ?></p>
									<p class="m-0"><strong>Price:</strong>&emsp;<?= $workshop['price']; ?></p>

									<div class="table-responsive mt-3">
										<table class="table table-bordered table-striped workshop-table">
											<thead>
												<tr>
													<th>Day</th>
													<th>Dates Available</th>
													<th>Bouquets</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($month["days"] as $day => $info): ?>
												<tr>
													<td class="text-center"><?= $day; ?></td>
													<td>
														<?php foreach ($info['dates'] as $date): ?>
														<span class="badge badge-pink text-secondary"><?= date("d/m", strtotime($date)); ?></span>
														<?php endforeach; ?>
													</td>
													<td><?= $info['content']; ?></td>
												</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									</div>

									<!-- Display the button based on whether the month is upcoming -->
									<?php if (strtotime("first day of " . $monthName) > strtotime("first day of " . date("F Y", strtotime($today)))): ?>
										<a href="workshop_reg.php?id=<?= $workshop['id']; ?>&session=<?= $monthName ?>" class="btn btn-primary mt-3">Book Your Seat</a>
									<?php else: ?>
										<p class="btn btn-disable mt-3 m-0 pe-none">No Longer Available</p>
									<?php endif; ?>
								</div>
								<?php $i++; ?>
							<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>

				<?php if ($workshop !== end($workshops)): ?>
					<hr> <!-- To seperate the workshops -->
				<?php endif; ?>
			<?php endforeach; ?>
		</section>
		
		<!-- Scroll container for previous student works -->
		<section id="student-scroll">
			<h2 class="text-center pb-4">From Our Past Sessions</h2>
			<div class="px-5 ms-lg-5">
				<div class="d-flex flex-column flex-lg-row align-items-center gap-4">
					<div class="py-lg-5 d-flex flex-column w-50 text-center text-lg-start pe-lg-1" id="scroll-text">
						<h3 class="text-uppercase">Discover <br><span class="fs-5">Our Students' Creations</span></h3>
						<p class="small">Browse the amazing projects and artworks crafted by our students in past workshops.</p>
						<a href="studentworks.php" class="btn mx-auto mx-lg-0">Explore Now</a>
					</div>

					<div class="d-flex gap-3 overflow-auto flex-nowrap pb-2 w-100 scroll-container">
						<?php
						include "include/db_connect.php";
						$query = "SELECT s.first_name, s.last_name, s.workshop_id, s.workshop_media, w.workshop_title FROM studentworks_table s JOIN workshop_table w ON s.workshop_id = w.id";
						$result = mysqli_query($conn, $query);
						$image_pool = [];

						if (mysqli_num_rows($result) > 0) {
							while ($row = mysqli_fetch_assoc($result)) {
								$media = array_map('trim', explode(',', $row['workshop_media']));
								foreach ($media as $file) {
									if (preg_match('/\.(jpg|jpeg|png)$/i', $file)) {
										$image_pool[] = [
											'publisher' => $row['first_name'] . ' ' . $row['last_name'],
											'media' => $file,
											'workshop' => $row['workshop_title']
										];
									}
								}
							}
						}

						shuffle($image_pool);
						$random_images = array_slice($image_pool, 0, 8);

						foreach($random_images as $item){
							$publisher = $item['publisher'];
							$media = $item['media'];
							$workshop = $item['workshop'];
							
							echo 
							"<div class='card flex-shrink-0 overflow-hidden'>
								<img src='studentworks/$media' class='card-img-top' alt='$publisher's $workshop Creation'>
								<div class='img-gradient position-absolute h-50 bottom-0 start-0 end-0'></div>
								<div class='overlay-text position-absolute text-light w-100 px-3'>
									<p class='text-uppercase small m-0'>$publisher</p>
									<p class='fw-bold fs-5 m-0'>Workshop: $workshop</p>
								</div>
							</div>
							";
						}

						mysqli_close($conn);
						?>
					</div>
				</div>
			</div>
		</section>
    </article>
	
	<?php include "include/footer.php"; ?>

<script src="js/main.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>
</html>