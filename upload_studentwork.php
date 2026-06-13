<?php
include "include/session.php";
include "include/db_connect.php";

$notification_data = displayNotification($conn, $_SESSION['user']);
$unread_notifications = $notification_data['notifications'];
$unread_count = $notification_data['count'];

$selectedWorkshop = $_GET['workshop'] ?? '';

// Link to the previous page
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['studentwork_back'] = $_SERVER['HTTP_REFERER'] ?? 'studentworks.php';
}

$back_link = $_SESSION['studentwork_back'];

// Workshop attended
$query = "SELECT id, workshop_title, date FROM workshop_table WHERE email = '{$_SESSION['user']}' AND approve_status = 'approved' AND trash='no'";
$result = mysqli_query($conn, $query);
$workshops = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $dates = array_map('trim', explode(',', $row['date']));
        $date = end($dates);

        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            $workshops[] = $row;
        }
    }
}

if (isset($_POST['upload_creation'])) {
    $firstName = ucwords(strtolower(sanitise_input($_POST['firstName'])));
	$lastName = ucwords(strtolower(sanitise_input($_POST['lastName'])));
    $selectedWorkshop = $_POST['title'] ?? '';
    $caption = sanitise_input($_POST['caption']);
    $mediaFiles = [
        $_FILES['studentwork_media1'],
        $_FILES['studentwork_media2'],
        $_FILES['studentwork_media3'],
        $_FILES['studentwork_media4']
    ];
    $errors =[];

    // First Name
    if (empty($firstName)){
        $errors['firstName'] = "* First name is required.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $firstName)) {
        $errors['firstName'] = "* Name can contain only letters and white spaces.";
    }

    // Last Name
    if (empty($lastName)){
        $errors['lastName'] = "* Last name is required.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $lastName)) {
        $errors['lastName'] = "* Name can contain only letters and white spaces.";
    }

    // Workshop
    if (empty($selectedWorkshop)) {
        $errors['title'] = "* Please select a workshop.";
    } else {
        $checkMaxQuery = "SELECT COUNT(*) as total FROM studentworks_table WHERE workshop_id = $selectedWorkshop AND email = '{$_SESSION['user']}' AND (approve_status = 'approved' OR approve_status = 'pending') AND trash='no'";
        $result = mysqli_query($conn, $checkMaxQuery);
        $row = mysqli_fetch_assoc($result);

        if ($row['total'] >= 2) {
            $errors['title'] = "* Maximum uploads reached. You can only share 2 creations per workshop.";
        }
    }

    // Creation
    if ($mediaFiles[0]['error'] === UPLOAD_ERR_NO_FILE){
        $errors['studentwork_media'] = "* Please upload at least one creation file.";
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/avi', 'video/mov'];

    foreach ($mediaFiles as $file) {
        if ($file['error'] === UPLOAD_ERR_OK) {
            $fileSize = $file['size'];
            $fileType = mime_content_type($file['tmp_name']);
            
            if ($fileSize > 10 * 1024 * 1024) {
                $errors['studentwork_media'] = "* Each media must be less than 10MB.";
            }

            if (!in_array($fileType, $allowedTypes)) {
                $errors['studentwork_media'] = "* Only image and video files are allowed.";
            }
        }
    }
    
    if (empty($errors)) {
        $uploadedPaths = [];

        foreach ($mediaFiles as $file) {
            if ($file['error'] === UPLOAD_ERR_OK) {
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $newFileName = uniqid('work_', true) . '.' . $extension;
                $uploadPath = 'studentworks/' . $newFileName;

                move_uploaded_file($file['tmp_name'], $uploadPath);

                $uploadedPaths[] = $newFileName;
            }
        }

        $media = implode(',', $uploadedPaths);
        
        $insertQuery = "INSERT INTO studentworks_table (email, first_name, last_name, workshop_id, workshop_media, caption) VALUES ('{$_SESSION['user']}', '$firstName', '$lastName', $selectedWorkshop, '$media', '$caption')";

        if (mysqli_query($conn, $insertQuery)) {
            generateAdminNotification($conn, $_SESSION['user'], 'studentwork_pending');
            header("Location: studentworks.php");
            $alert['success'] = "Your creation has been uploaded successfully. Please wait for it to be reviewed.";
			$_SESSION['alert'] = $alert;
            exit();
        } else {
            $errors['database'] = "* Error uploading your creation. Please try again.";
        }
    }
}
mysqli_close($conn);
?>
<!DOCTYPE html>

<html lang="en">
<!-- Description: Upload Studentwork -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 13/9/2025 -->
<!-- Validated: OK 6/10/2025 -->

<head>
    <title>Share Your Creation | Root Flower</title>
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
        <form name="studentwork_submission" method="POST" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" novalidate class="z-1 p-5 rounded shadow m-md-5" id="form-card">
            <h1 class="lh-1">Share Your Creation! <i class="bi bi-flower1 ps-4"></i></h1>
            <p class="pb-2 text-secondary">Your workshop creations deserve the spotlight—post them here!</p>
            <div class="row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="form-group col-sm-6 mb-2">
                            <label for="firstName">First Name</label>
                            <input type="text" class="form-control <?php echo isset($_POST['upload_creation']) ? (isset($errors['firstName']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="firstName" name="firstName" value="<?php echo isset($_POST['firstName']) ? $firstName : $_SESSION['name'] ?? ''; ?>">
                        </div>
                        <div class="form-group col-sm-6 mb-2">
                            <label for="lastName">Last Name</label>
                            <input type="text" class="form-control <?php echo isset($_POST['upload_creation']) ? (isset($errors['lastName']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="lastName" name="lastName" value="<?php echo isset($_POST['lastName']) ? $lastName : $_SESSION['lname'] ?? ''; ?>">
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

                    <div class="form-group col-md-12 mb-2">
                        <label for="title">Attended Workshop</label>
                        <select class="form-select py-0 <?php echo isset($_POST['upload_creation']) ? (isset($errors['title']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="title" name="title" required>
                            <option value="" disabled <?php echo empty($selectedWorkshop) ? 'selected' : ''; ?>>-- Select a workshop --</option>
                            <?php foreach ($workshops as $workshopRow): 
                                $workshopTitle = $workshopRow['workshop_title'];
                                $workshopDate = $workshopRow['date'];
                                $lastDate = end(array_map('trim', explode(',', $workshopDate)));

                                if (str_contains($workshopTitle, "Florist To Be")) {
                                    $workshopDate = date("F", strtotime($lastDate));
                                } 
                            ?>
                                <option value="<?php echo $workshopRow['id']; ?>" <?php echo (($selectedWorkshop == $workshopRow['id'])) ? 'selected' : ''; ?>>
                                    <?php echo "$workshopTitle: $workshopDate"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <div class="invalid-feedback">
                            <?php if (isset($errors['title'])) echo $errors['title']; ?>
                        </div>
                    </div>

                    <div class="form-group col-md-12">
                        <label for="caption">Caption</label>
                        <textarea class="form-control" id="caption" name="caption" rows="4" placeholder="Write a caption for your creation..." maxlength="255"><?php echo $caption ?? ''; ?></textarea>
                    </div>
                </div>

                <div class="col-md-6 position-relative">
                    <div class="form-group mb-2">
                        Workshop Creations&ensp;<span class="text-muted">(Max 10MB, Image / Video)</span>
                        <input type="file" class="form-control mb-3 pt-1 <?php echo isset($errors['studentwork_media']) ? 'is-invalid' : ''; ?>" name="studentwork_media1" accept="image/*,video/*">

                        <input type="file" class="form-control mb-3 pt-1 <?php echo isset($errors['studentwork_media']) ? 'is-invalid' : ''; ?>" name="studentwork_media2" accept="image/*,video/*">

                        <input type="file" class="form-control mb-3 pt-1 <?php echo isset($errors['studentwork_media']) ? 'is-invalid' : ''; ?>" name="studentwork_media3" accept="image/*,video/*">
                        
                        <input type="file" class="form-control pt-1 <?php echo isset($errors['studentwork_media']) ? 'is-invalid' : ''; ?>" name="studentwork_media4" accept="image/*,video/*">
                        
                        <div class="invalid-feedback">
                            <?php if (isset($errors['studentwork_media'])) echo $errors['studentwork_media']; ?>
                        </div>
                    </div>
                    
                    <div class="position-absolute end-0 pe-3 action-buttons">
                        <a href="<?php echo $back_link; ?>" class="btn me-2">Back</a>
                        <button type="submit" class="btn btn-primary" name="upload_creation">Post Creation</button>
                    </div>
                </div>
            </div>
        </form>
    </article>

    <?php include "include/footer.php"; ?>
	
<script src="js/main.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>
</html>