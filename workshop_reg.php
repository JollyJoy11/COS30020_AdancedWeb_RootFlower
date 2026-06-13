<?php 
include "include/session.php"; 
include "include/workshops_info.php";
include "include/db_connect.php";

$notification_data = displayNotification($conn, $_SESSION['user']);
$unread_notifications = $notification_data['notifications'];
$unread_count = $notification_data['count'];

$today = date("Y-m-d");

$workshopId = $_GET['id'];
$session = $_GET['session'];

$selectedWorkshop = null;
foreach ($workshops as $w) {
    if ($w['id'] == $workshopId) {
        $selectedWorkshop = $w;
        break;
    }
}

$time = $selectedWorkshop['schedule'][$session]['time'] ?? '';

if (isset($_POST['workshop_register'])){
	$firstName = ucwords(strtolower(sanitise_input($_POST['firstName'])));
    $lastName = ucwords(strtolower(sanitise_input($_POST['lastName'])));
	$contact = sanitise_input($_POST['contact']);
	$email = sanitise_input($_POST['email']);
	$workshopTitle = sanitise_input($_POST['title']);
	$seat = $_POST['seat'];

	if (isset($selectedWorkshop['batches'][$session])){ // Check whether this is the florist to be workshop
		$date1 = $_POST['day1-date'] ?? '';
		$date2 = $_POST['day2-date'] ?? '';
		$date3 = $_POST['day3-date'] ?? '';
		$date4 = $_POST['day4-date'] ?? '';

		$time1 = $_POST['day1-time'] ?? '';
		$time2 = $_POST['day2-time'] ?? '';
		$time3 = $_POST['day3-time'] ?? '';
		$time4 = $_POST['day4-time'] ?? '';
	} else if (isset($selectedWorkshop['schedule'][$session]['days'])){ // Check whether this is the handtied bouquet workshop
		$date1 = $_POST['day1-date'];
		$date2 = $_POST['day2-date'];

		$time1 = $_POST['day1-time'];
		$time2 = $_POST['day2-time'];
	} else {
		$date = $_POST['date'];
		$time = $_POST['time'];
	}

	$errors = [];

	// First Name
    if (empty($firstName)) {
        $errors['firstName'] = "* First name is required.";
    } else if (!preg_match("/^[A-Za-z\s]+$/", $firstName)) {
        $errors['firstName'] = "* Name can contain only letters and white spaces.";
    }

    // Last Name
    if (empty($lastName)) {
        $errors['lastName'] = "* Last name is required.";
    } else if (!preg_match("/^[A-Za-z\s]+$/", $lastName)) {
        $errors['lastName'] = "* Name can contain only letters and white spaces.";
    }

	// Contact No
    if (empty($contact)) {
        $errors['contact'] = "* Contact number is required.";
    } else if (!preg_match("/^0\d\d-\d{7,8}$/", $contact)) {
        $errors['contact'] = "* Invalid contact number format.";
    }

	// Email
    if (empty($email)) {
        $errors['email'] = "* Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "* Invalid email format.";
    }

	// Number of Seat
    if (empty($seat)) {
        $errors['seat'] = "* Number of seat is required.";
    }

	// Check whether dates are selected for all days for florist to be workshop
	if (isset($selectedWorkshop['batches'][$session])){
		if (empty($date1) || empty($date2) || empty($date3) || empty($date4)){
			$errors['date'] = "* All date selection is required.";
		}
		
		if (empty($time1) || empty($time2) || empty($time3) || empty($time4)){
			$errors['time'] = "* All time selection is required.";
		}
	}

	if (isset($selectedWorkshop['batches'][$session])){
		$date = implode(", ", [$date1, $date2, $date3, $date4]);
		$time = implode(", ", [$time1, $time2, $time3, $time4]);
	} else if (isset($selectedWorkshop['schedule'][$session]['days'])){
		$date = implode(", ", [$date1, $date2]);
		$time = implode(", ", [$time1, $time2]);
	} 

	$query = "SELECT date, time, approve_status FROM workshop_table WHERE email = '$email' AND workshop_title = '$workshopTitle' AND trash='no'";
	$result = mysqli_query($conn, $query);

	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)){
			$savedDate = $row['date'];
			$savedTime = $row['time'];

			if (isset($selectedWorkshop['batches'][$session])) {
				$savedStatus = $row['approve_status'];

				$firstDate = explode(', ', $savedDate)[0]; 
				$savedMonth = date("F Y", strtotime($firstDate));

				if (($savedMonth === $session && $savedStatus != 'rejected') || ($savedDate === $date && $savedTime === $time)) {
					$errors['duplicate'] = "* You have already registered this workshop session.";
					break;
				}
			} else if (isset($selectedWorkshop['schedule'][$session]['days'])){
				$firstDate = explode(', ', $savedDate)[0];
				if ($firstDate === $date1) {
					$errors['duplicate'] = "* You have already registered this workshop.";
					break;
				}
			} else {
				if ($savedDate === $date) {
					$errors['duplicate'] = "* You have already registered this workshop.";
					break;
				}
			}
		}
    }

	if (empty($errors)){
		$insert_query = "INSERT INTO workshop_table (email, first_name, last_name, workshop_title, date, time, no_of_seats, contact_number) VALUES ('$email', '$firstName', '$lastName', '$workshopTitle', '$date', '$time', '$seat', '$contact')";
		$insert_result = mysqli_query($conn, $insert_query);

		if ($insert_result){
			// Send Workshop Detail Email upon Successful Registration
			if (isset($selectedWorkshop['batches'][$session])) {
				$venue = $selectedWorkshop['batches'][$session]['venue'];
			} elseif (isset($selectedWorkshop['schedule'][$session]['venue'])) {
				$venue = $selectedWorkshop['schedule'][$session]['venue'];
			} else {
				$venue = $selectedWorkshop['venue'] ?? 'TBA'; 
			}

			$to = $email; 
            $subject = "Workshop Registration Confirmation - $workshopTitle"; 
            $message = "Hi $firstName, \n\nThank you for registering the $workshopTitle workshop. Your registration has been received and is currently pending for approval. Here are the details of your registration:\n\nWorkshop Title: $workshopTitle\nDate: $date\nTime: $time\nVenue: " . $venue . "\nSeat(s) Reserved: $seat\n\nOur team will review your registration shortly. You’ll receive a confirmation email once your registration has been approved. \n\nPlease note that you may edit the number of seats in your registration up to 3 days before the workshop date.\n\nIf you have any questions in the meantime, please feel free to reach out to us.\n\nBest regards,\nRoot Flower Team"; 

            if (sendEmail($to, $subject, $message)){ 
				generateAdminNotification($conn, $_SESSION['user'], 'workshop_pending');
                unset($errors);
                header('Location: workshops.php');
				$alert['success'] = "$workshopTitle registered successfully.";
				$_SESSION['alert'] = $alert;
                exit();
            } else {
                echo "Error: Unable to send confirmation email.";
            }
		} else {
			echo "Error: Unable to open file for writing.";
		}
	}
} 

mysqli_close($conn);
?>
<!DOCTYPE html>

<html lang="en">
<!-- Description: Workshop Registration -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 28/9/2025 -->
<!-- Validated: OK 6/10/2025 -->

<head>
    <title>Join Our Workshop | Root Flower</title>
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

    <article class="d-flex justify-content-center align-items-center min-vh-100" id="form_bg">
		<form name="workshop_reg" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=$workshopId&session=$session";?>" novalidate class="z-1 p-5 rounded shadow m-md-5" id="form-card">
			<h1 class="pb-3">Workshop Registration - <?php echo $selectedWorkshop['title']; ?></h1>

			<div class="row">
				<div class="form-group col-md-6 mb-2">
					<label for="firstName">First Name</label>
					<input type="text" class="form-control <?php echo isset($_POST['workshop_register']) ? (isset($errors['firstName']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="firstName" name="firstName" value="<?php echo isset($_POST['firstName']) ? $firstName : $_SESSION['name'] ?? ''; ?>">
				</div>
				<div class="form-group col-md-6 mb-2">
					<label for="lastName">Last Name</label>
					<input type="text" class="form-control <?php echo isset($_POST['workshop_register']) ? (isset($errors['lastName']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="lastName" name="lastName" value="<?php echo isset($_POST['lastName']) ? $lastName : $_SESSION['lname'] ?? ''; ?>">
				</div>
			</div>

			<?php if (isset($errors['firstName']) || isset($errors['lastName'])): ?>
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

			<div class="row">
				<div class="form-group col-md-6 mb-2">
					<label for="contact">Contact No</label>
					<input type="text" class="form-control <?php echo isset($_POST['workshop_register']) ? (isset($errors['contact']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="contact" name="contact" placeholder="e.g. 012-3456789" value="<?php echo isset($_POST['contact']) ? $contact : ''; ?>">
					<div class="invalid-feedback">
						<?php echo $errors['contact'] ?? ''; ?>
					</div>
				</div>

				<div class="form-group col-md-6">
					<label for="email">Email</label>
					<input type="text" class="form-control <?php echo isset($_POST['workshop_register']) ? ((isset($errors['email']) || isset($errors['duplicate'])) ? 'is-invalid' : 'is-valid') : ''; ?>" id="email" name="email" placeholder="e.g. abc@gmail.com" value="<?php echo isset($_POST['email']) ? $email : $_SESSION['user'] ?? ''; ?>">
					<div class="invalid-feedback">
						<?php 
							if (isset($errors['email'])) {
								echo $errors['email'];
							} elseif (isset($errors['duplicate'])) {
								echo $errors['duplicate'];
							}
						?>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="form-group col-md-6 mb-2">
					Workshop Title
					<input type="text" class="form-control" id="title" name="title" value="<?php echo $selectedWorkshop['title']; ?>" readonly>
				</div>
				<div class="form-group col-md-6 mb-2">
					<label for="seat">Number of Seat(s)</label>
					<input type="number" class="form-control w-50 <?php echo isset($_POST['workshop_register']) ? (isset($errors['seat']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="seat" name="seat" value="<?php echo isset($_POST['seat']) ? $seat : '1'; ?>" min="1" max="20">
				</div>
			</div>

			<div class="row mb-4">
				<div class="form-group col-md-6 mb-2">
					Date

					<?php if (isset($errors['date'])): ?>
						<div class="small text-danger mb-2">
							<?php echo $errors['date']; ?>
						</div>
					<?php endif; ?>

					<?php if (isset($selectedWorkshop['batches'][$session])): ?>
						<!-- Florist To Be -->
						<?php foreach ($selectedWorkshop['batches'][$session]['days'] as $dayNumber => $info): ?>
							<div class="form-group d-flex">
								<label for="day<?= $dayNumber ?>-date" class="w-50 mb-2">&emsp;Day <?= $dayNumber ?></label>
								<select id="day<?= $dayNumber ?>-date" name="day<?= $dayNumber ?>-date" class="form-select py-0 mb-2">
									<option value="" <?php echo (isset($_POST['workshop_register']) && isset($_POST["day$dayNumber-date"])) ? '' : 'selected'; ?> disabled>-- Select a date --</option>
									<?php foreach ($info['dates'] as $date): ?>
										<option value="<?= $date ?>" <?php echo (isset($_POST['workshop_register']) && isset($_POST["day$dayNumber-date"]) && $_POST["day$dayNumber-date"] == $date) ? 'selected' : ''; ?>>
											<?= date("d M Y", strtotime($date)) ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						<?php endforeach; ?>

					<?php elseif (isset($selectedWorkshop['schedule'][$session]['days'])): ?>
						<!-- Handtied bouquet -->
						<?php foreach ($selectedWorkshop['schedule'][$session]['days'] as $i => $day): ?>
							<div class="form-group d-flex">
								<label for="day<?= $i+1 ?>-date" class="w-25 mb-2">&emsp;Day <?= $i+1 ?></label>
								<input type="date" class="form-control w-75 mb-2" id="day<?= $i+1 ?>-date" name="day<?= $i+1 ?>-date" value="<?= $day['date'] ?>" readonly>
							</div>
						<?php endforeach; ?>
					<?php else: ?>
						<!-- Hobby Class -->
						<input type="date" class="form-control w-75" id="date" name="date" value="<?php echo $selectedWorkshop['schedule'][$session]['date']; ?>" readonly>
					<?php endif; ?>
				</div>

				<div class="form-group col-md-6 mb-2">
					Time

					<?php if (isset($errors['time'])): ?>
						<div class="small text-danger mb-2">
							<?php echo $errors['time']; ?>
						</div>
					<?php endif; ?>

					<?php if (isset($selectedWorkshop['batches'][$session])): ?>
						<!-- Florist To Be -->
						<?php foreach ($selectedWorkshop['batches'][$session]['days'] as $dayNumber => $info): ?>
							<div class="form-group d-flex">
								<label for="day<?= $dayNumber ?>-date" class="w-50 mb-2 d-md-none">&emsp;Day <?= $dayNumber ?></label>
								<select id="day<?= $dayNumber ?>-time" name="day<?= $dayNumber ?>-time" class="form-select py-0 mb-2">
									<option value="" <?php echo (isset($_POST['workshop_register']) && isset($_POST["day$dayNumber-time"])) ? '' : 'selected'; ?> disabled>-- Select a time slot --</option>
									<?php
										$time_option = explode(" / ", $selectedWorkshop['time']);

										for ($t = 0; $t < count($time_option); $t++){
											$selected = (isset($_POST['workshop_register']) && isset($_POST["day$dayNumber-time"]) && $_POST["day$dayNumber-time"] == $time_option[$t]) ? 'selected' : '';

											echo 
											"<option value='". $time_option[$t] ."' $selected>" .
												$time_option[$t]
											. "</option>";
										}
									?>
								</select>
							</div>
						<?php endforeach; ?>
					<?php elseif (isset($selectedWorkshop['schedule'][$session]['days'])): ?>
						<!-- Handtied bouquet -->
						<?php foreach ($selectedWorkshop['schedule'][$session]['days'] as $i => $day): ?>
							<input type="text" class="form-control mb-2 w-50" id="day<?= $i + 1 ?>-time" name="day<?= $i + 1 ?>-time" value="<?= $day['time'] ?>" readonly>
						<?php endforeach; ?>
					<?php else: ?>
						<!-- Hobby Class -->
						<input type="text" class="form-control w-50" id="time" name="time" value="<?php echo $selectedWorkshop['schedule'][$session]['time']; ?>" readonly>
					<?php endif; ?>
				</div>
			</div>

			<button type="submit" class="btn btn-primary mt-2 me-2" name="workshop_register">Register</button>
			<a href="workshops.php" class="btn mt-2">Back</a>
		</form>
    </article>

	<?php include "include/footer.php"; ?>

<script src="js/main.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>
</html>