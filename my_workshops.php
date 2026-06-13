<?php 
include "include/session.php"; 
include "include/workshops_info.php";
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

// Workshop details
$query = "SELECT id, workshop_title, no_of_seats, date, time, approve_status, edit_status, pending_seats FROM workshop_table WHERE email='{$_SESSION['user']}' AND trash='no'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0){
    while ($row = mysqli_fetch_assoc($result)){
        $id = $row['id'];
        $workshop = $row['workshop_title'];
        $seats = $row['no_of_seats'];
        $dates = $row['date'];
        $times = $row['time'];
        $status = $row['approve_status'];
        $editStatus = $row['edit_status'];
        $pendingSeats = $row['pending_seats'];

        $displayStatus = $status;
        $displaySeats = $seats;
        
        if ($editStatus === 'pending') {
            $displayStatus = 'Pending Change'; 
            $displaySeats = $pendingSeats;
        }

        foreach ($workshops as $w){
            if (trim($w['title']) !== trim($workshop)) {
                continue; 
            }

            $venue = null;
            $userDates = array_map('trim', explode(', ', $dates));

            if ($w['title'] === "Hobby Class") {
                foreach ($w['schedule'] as $s) {
                    if (in_array($s['date'], $userDates)) {
                        $venue = $s['venue'];
                        break;
                    }
                }
            } elseif ($w['title'] === "Handtied Bouquet") {
                $userDates = array_map('trim', explode(',', $dates));

                foreach ($w['schedule'] as $s) {
                    foreach ($s['days'] as $d) {
                        if (in_array($d['date'], $userDates)) {
                            $venue = $s['venue'];
                            break 2; 
                        }
                    }
                }
            } else {
                $monthYear = date("F Y", strtotime($userDates[0]));

                if (isset($w['batches'][$monthYear])) {
                    $batch = $w['batches'][$monthYear];
                    $venue = $batch['venue'];
                }
            }

            $firstDate = min(array_map('strtotime', $userDates));
            $latestDate = max(array_map('strtotime', $userDates));
            $today = strtotime(date("Y-m-d"));
            $passed = $latestDate < $today;

            $editDeadline = strtotime('-3 days', $firstDate);
            $editAllowed = $today < $editDeadline;

            $myWorkshops[] = [
                "id" => $id,
                "title" => $w['title'],
                "venue" => $venue,
                "price" => $w['price'],
                "image" => $w['image'],
                "seats" => $displaySeats,
                "date"  => $dates,
                "time"  => $times,
                "status" => $displayStatus,
                "passed" => $passed,
                "edit_allowed" => $editAllowed
            ];

            break;
        }
    }
}

// Sort workshops by the earliest date in descending order
if (!empty($myWorkshops)) {
    usort($myWorkshops, function($a, $b) {
        $datesA = preg_split('/,\s*/', $a['date']);
        $datesB = preg_split('/,\s*/', $b['date']);

        $firstA = strtotime(trim($datesA[0] ?? ''));
        $firstB = strtotime(trim($datesB[0] ?? ''));

        return $firstB <=> $firstA; 
    });
}

// Edit seat request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seat_num'])){
    $newSeats = $_POST['seat_num'];
    $workshopID = $_POST['workshop_id'];

    $query = "SELECT no_of_seats, approve_status, edit_status FROM workshop_table WHERE id=$workshopID AND trash='no'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_assoc($result);
        $oldSeats = $row['no_of_seats'];
        $status = $row['approve_status'];
        $editStatus = $row['edit_status'];

        if ($editStatus == "pending"){
            $alert['danger'] = "You already have a pending seat change request. Please wait for the admin's response before submitting another change.";
            $_SESSION['alert'] = $alert;
        } else if ($newSeats != $oldSeats){
            if ($status != "pending"){
                $update_query = "UPDATE workshop_table SET pending_seats = $newSeats, edit_status = 'pending' WHERE id = $workshopID";
            } else {
                $update_query = "UPDATE workshop_table SET no_of_seats = $newSeats, approve_status='pending' WHERE id = $workshopID";
            }

            if (mysqli_query($conn, $update_query)){
                generateAdminNotification($conn, $_SESSION['user'], 'seat_pending'); 
                $alert['success'] = "Number of seats is changed and now pending to be approved.";
				$_SESSION['alert'] = $alert;
            }
        }
    }

    header("Location: my_workshops.php");
    exit;
}

mysqli_close($conn);
?>
<!DOCTYPE html>

<html lang="en">
<!-- Description: Student Works -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 5/10/2025 -->
<!-- Validated: OK 6/10/2025 -->

<head>
    <title>My Workshops | Root Flower</title>
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

    <article class="p-5">
        <div class="pb-3">
            <h1>My Workshops</h1>
            <?php if (empty($myWorkshops)): ?>
                <p class="small pb-3">You haven't joined any workshops yet. Explore and enroll to start your journey.</p>
                <a href="workshops.php" class="btn btn-primary">Explore Workshops</a>
            <?php else: ?>
                <!-- Display registered workshops -->
                <div class="d-flex gap-3 overflow-auto flex-nowrap w-100 scroll-container"  id="myworkshops">
                    <?php foreach($myWorkshops as $m): ?>
                        <div class="card mb-3 overflow-hidden">
                            <?php if ($m['passed']): ?>
                                <div class="bg-secondary opacity-25 position-absolute w-100 h-100 start-0 z-1">
                                </div>
                                <div class="position-absolute top-0 start-0 bg-white px-3 py-1 m-2 rounded fs-6 z-2">
                                    <i class="bi bi-clock-history"></i>
                                </div>
                            <?php endif; ?>

                            <div class="row h-100 align-items-start">
                                <div class="col-12 col-md-5">
                                    <img src="<?= $m["image"] ?>" class="img-fluid <?php echo $m['passed'] ? 'opacity-50' : ''; ?>" alt="<?= $m["title"] ?>">
                                </div>
                            
                                <div class="col-12 col-md-7 h-100">
                                    <div class="card-body py-md-4 text-secondary <?php echo $m['passed'] ? 'opacity-50' : ''; ?>">
                                        <h3 class="card-title pb-2"><?= $m["title"] ?></h3>

                                        <div class="status-<?= $m["status"] ?> position-absolute top-0 end-0 border border-secondary px-3 m-3 rounded text-uppercase fs-6 bg-white">
                                            <?= $m["status"] ?>
                                        </div> 

                                        <?php 
                                        $datesArr = array_map('trim', explode(',', $m["date"]));
                                        $timesArr = array_map('trim', explode(',', $m["time"]));
                                        ?>

                                        <table class="table table-sm mb-2 table-striped">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Date</th>
                                                    <th scope="col">Time</th>
                                                </tr>
                                            </thead>
                                            <tbody class="fs-6">
                                                <?php foreach ($datesArr as $i => $d): ?>
                                                    <tr>
                                                        <td class="p-0"><?= $d ?></td>
                                                        <td class="p-0"><?= $timesArr[$i] ?? '' ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>

                                        <p class="card-text m-0"><strong>Venue:&ensp;</strong><?= $m["venue"] ?></p>
                                        <p class="card-text m-0"><strong>Price:&ensp;</strong><?= $m["price"] ?></p>
                                        <div class="card-text m-0 d-flex align-items-center">
                                            <strong>Number of Seat(s):&ensp;</strong>
                                            <?php if (isset($_GET['edit']) && $_GET['session'] == $m['id']): ?>
                                                <form action="<?php echo ($_SERVER["PHP_SELF"]); ?>" name="editseat" method="POST" class="d-inline">
                                                    <input type="hidden" name="workshop_id" value="<?= $m['id'] ?>">
                                                    <div class="input-group d-inline-flex w-auto">
                                                        <input type="number" name="seat_num" class="form-control" value="<?= $m["seats"] ?>" min="1" max="20">
                                                        <button type="submit" class="btn btn-primary py-0 px-1"><i class="bi bi-check-lg"></i></button>
                                                    </div>
                                                </form>
                                            <?php else: ?>
                                                <?= $m["seats"] ?>
                                                <?php if (!$m['passed'] && $m['edit_allowed']): ?>
                                                    <a href="?edit&session=<?= $m["id"] ?>" class="text-secondary ps-3"><i class="bi bi-pencil-fill"></i></a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if ($m['passed'] && $m['status'] == "approved"): ?>
                                        <a href='upload_studentwork.php?workshop=<?= urlencode($m['id']) ?>' class='btn btn-primary btn-sm z-2 position-absolute bottom-0 end-0 m-3'>Share Your Creation</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <hr>
        <!-- Display Studentwork Creations -->
        <section class="pt-3">
            <h2>My Creations</h2>
            <?php 
            include "include/db_connect.php";

            $query = "SELECT id, workshop_media, approve_status, upload_time FROM studentworks_table WHERE email='{$_SESSION['user']}' AND trash='no' ORDER BY upload_time DESC";
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0){
                echo "<div class='d-flex gap-3 overflow-auto flex-nowrap w-100 scroll-container' id='myWorks'>";
                while ($row = mysqli_fetch_assoc($result)){
                    $id = $row['id'];
                    $uploadTime = date("d M Y", strtotime($row['upload_time']));
                    $approveStatus = $row['approve_status'];
                    $media = array_map('trim', explode(',', $row['workshop_media']));
                    $totalMedia = count($media);

                    echo "<div id='card$id' class='card overflow-hidden flex-shrink-0 mb-2'>
                            <a href='studentwork_detail.php?id=$id' class='h-100 w-100 rounded'>
                                <div id='carousel-{$id}' class='carousel slide w-100 h-100' data-bs-ride='carousel'>
                                    <div class='carousel-indicators position-absolute top-0 justify-content-end me-2'>";
                                    for ($i = 0; $i < $totalMedia; $i++) {
                                        $active = ($i === 0) ? 'active' : '';
                                        // Add a dot for each image
                                        echo "<button type='button' data-bs-target='#carousel-{$id}' data-bs-slide-to='{$i}' class='{$active} rounded-circle' aria-current='true' aria-label='Slide {$i}'></button>";
                                    }
                                echo "</div>
                                    <div class='carousel-inner w-100 h-100'>";
                                    $isFirst = true;

                                    foreach ($media as $currentMedia) {
                                        $activeClass = $isFirst ? 'active' : '';
                                        echo "<div class='carousel-item {$activeClass} w-100 h-100'>";

                                        if (preg_match('/\.mp4$/i', $currentMedia)) {
                                            echo "<div class='position-relative w-100 h-100'>
                                                    <video preload='metadata' muted class='card-img-top d-block h-100 object-fit-cover'>
                                                        <source src='studentworks/$currentMedia#t=7.5' type='video/mp4'>
                                                    </video>
                                                    <i class='bi bi-play-btn text-light position-absolute top-0 start-0 ms-2 fs-3 z-3'></i>
                                                </div>";
                                        } else {
                                            echo "
                                                    <img src='studentworks/$currentMedia' alt='Workshop Media' class='card-img-top h-100 object-fit-cover'>";
                                        }

                                        echo "</div>"; 
                                        $isFirst = false;
                                    }

                                    echo "</div>";
                                    if ($totalMedia > 1){
                                        echo "
                                        <button class='carousel-control-prev z-2' type='button' data-bs-target='#carousel-{$id}' data-bs-slide='prev'>
                                            <i class='bi bi-caret-left-fill fs-4'></i>
                                            <span class='visually-hidden'>Previous</span>
                                        </button>
                                        <button class='carousel-control-next z-2' type='button' data-bs-target='#carousel-{$id}' data-bs-slide='next'>
                                            <i class='bi bi-caret-right-fill fs-4'></i>
                                            <span class='visually-hidden'>Next</span>
                                        </button>";
                                    }
                            echo "</div>
                            </a>

                            <div class='img-gradient position-absolute h-50 bottom-0 start-0 end-0'></div>

                            <div class='overlay-text position-absolute text-light d-flex w-100 justify-content-between align-items-end px-3'>
                                <div class='text-light border border-light px-3 rounded text-uppercase fs-6'>
                                    $approveStatus
                                </div>
                                <p class='m-0'>$uploadTime</p>
                            </div>
                        </div>";
                }
                echo "</div>";
            } else {
                echo "<p class='small pb-3'>Your shared creations will appear here once you contribute to the community.</p>";
            }

            mysqli_close($conn);
            ?>
        </section>
    </article>

	<?php include "include/footer.php"; ?>

<script src="js/main.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>
</html>