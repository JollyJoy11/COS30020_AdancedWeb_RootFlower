<?php 
include "include/db_connect.php";
include "include/session.php"; 

$notification_data = displayNotification($conn, $_SESSION['user']);
$unread_notifications = $notification_data['notifications'];
$unread_count = $notification_data['count'];

if (isset($_POST['contribute-btn'])){
    $scientificName = sanitise_input($_POST['scientific_name']);
    $commonName =  ucwords(strtolower(sanitise_input($_POST['common_name'])));
    $plantImage = $_FILES['plant_image'];
    $descPdf = $_FILES['desc_pdf'];

    $errors = [];

    if (empty($scientificName)) {
        $errors['scientific_name'] = "* Scientific name is required.";
    } else if (!preg_match("/^[A-Za-z\s\-]+$/", $scientificName)){
        $errors['scientific_name'] = "* Scientific name can contain only letters and white spaces.";
    }

    if (empty($commonName)) {
        $errors['common_name'] = "* Common name is required.";
    } else if (!preg_match("/^[A-Za-z\s]+$/", $commonName)){
        $errors['common_name'] = "* Common name can contain only letters and white spaces.";
    }

    if ($plantImage['error'] !== UPLOAD_ERR_OK) {
        $errors['plant_image'] = "* Plant image is required.";
    } else {
        // file size limit (5MB)
        if ($plantImage['size'] > 5 * 1024 * 1024) {
            $errors['plant_image'] = "* Image must be less than 5MB.";
        }

        // allow only jpg, jpeg
        $allowedImgTypes = ['image/jpeg', 'image/jpg'];

        if (!in_array($plantImage['type'], $allowedImgTypes)) {
            $errors['plant_image'] = "* Only JPG images are allowed.";
        }
    }

    if ($descPdf['error'] !== UPLOAD_ERR_OK) {
        $errors['desc_pdf'] = "* Description file is required.";
    } else {
        // file size limit (7MB)
        if ($descPdf['size'] > 7 * 1024 * 1024) {
            $errors['desc_pdf'] = "* PDF must be less than 7MB.";
        }

        // only PDF
        if ($descPdf['type'] !== 'application/pdf') {
            $errors['desc_pdf'] = "* Only PDF files are allowed.";
        }
    }

    if (empty($errors)) {
        // Reformat the scientific name
        $nameParts = explode(' ', $scientificName);
        $genus = ucfirst(strtolower($nameParts[0]));
        $species = isset($nameParts[1]) ? strtolower($nameParts[1]) : '';
        $scientificName = trim($genus . ' ' . $species);

        $imgDir = __DIR__ . '/img/flower/'; 
        if (!is_dir($imgDir)) {
            mkdir($imgDir, 0777, true); 
        }

        // Generate unique image filename to prevent overwriting
        $ext = pathinfo($plantImage['name'], PATHINFO_EXTENSION);
        $newImageName = 'flower_' . time() . '.' . $ext;
        $plantImagePath = $imgDir . $newImageName;

        move_uploaded_file($plantImage['tmp_name'], $plantImagePath);

        // Description extraction from pdf
        $uploadedPdfPath = $descPdf['tmp_name'];
        $descriptionText = extractPdfText($uploadedPdfPath);

        $generatedPdf = generateFlowerPdf($commonName, $scientificName, $plantImagePath, $descriptionText);

        $sql = "INSERT INTO flower_table (Common_Name, Scientific_Name, plants_image, description) VALUES ('$commonName', '$scientificName', '$newImageName', '$generatedPdf')";
        mysqli_query($conn, $sql);
    }
}

$select = "SELECT * FROM flower_table WHERE trash='no'";
$result = mysqli_query($conn, $select);

mysqli_close($conn);
?>
<!DOCTYPE html>

<html lang="en">
<!-- Description: Flower -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 2/12/2025 -->
<!-- Validated: OK 6/12/2025 -->

<head>
    <title>Contribute Flower Information | Root Flower</title>
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

    <article class="pb-5 text-secondary">
        <div id="workshop-bg" class="container-fluid p-5">
            <!-- Flower Contribution Form -->
            <?php if (!isset($_POST['contribute-btn']) || !empty($errors)): ?>
                <form name="contribute" method="POST" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" novalidate class="z-1 p-5 rounded shadow border position-relative bg-light bg-opacity-50 mx-lg-5">
                    <h1 class="lh-1 text-center">Contribute Your Flower</h1>
                    <p class="pb-2 text-center">Share the wondrous world of flowers with us</p>
                    <div class="row">
                        <div class="col-lg-3">
                            <img src="img/flower-ill.png" height="50" alt="Flower Illustration">
                        </div>

                        <div class="col-lg-9">
                            <div class="form-group mb-2">
                                <label for="scientific_name">Scientific Name</label>
                                <input type="text" name="scientific_name" id="scientific_name" class="form-control <?php echo isset($_POST['contribute-btn']) ? (isset($errors['scientific_name']) ? 'is-invalid' : 'is-valid') : ''; ?>" placeholder="e.g. Rosa damascena" value="<?php echo isset($scientificName) ? $scientificName : ''; ?>">
                                <div class="invalid-feedback">
                                    <?php echo $errors['scientific_name']; ?>
                                </div>
                            </div>

                            <div class="form-group mb-2">
                                <label for="common_name">Common Name</label>
                                <input type="text" name="common_name" id="common_name" class="form-control <?php echo isset($_POST['contribute-btn']) ? (isset($errors['common_name']) ? 'is-invalid' : 'is-valid') : ''; ?>" placeholder="e.g. Rose" value="<?php echo isset($commonName) ? $commonName : ''; ?>">
                                <div class="invalid-feedback">
                                    <?php echo $errors['common_name']; ?>
                                </div>
                            </div>

                            <div class="form-group mb-2">
                                <label for="plant_image">Flower Image</label>
                                <input type="file" name="plant_image" id="plant_image" class="form-control pt-1 <?php echo isset($_POST['contribute-btn']) ? (isset($errors['plant_image']) ? 'is-invalid' : 'is-valid') : ''; ?>" accept="image/*">
                                <div class="invalid-feedback">
                                    <?php echo $errors['plant_image']; ?>
                                </div>
                            </div>

                            <div class="form-group mb-2">
                                <label for="desc_pdf">Description File (Max 7MB, pdf)</label>
                                <input type="file" name="desc_pdf" id="desc_pdf" class="form-control pt-1 <?php echo isset($_POST['contribute-btn']) ? (isset($errors['desc_pdf']) ? 'is-invalid' : 'is-valid') : ''; ?>" accept="application/pdf">
                                <div class="invalid-feedback">
                                    <?php echo $errors['desc_pdf']; ?>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary mt-5 ms-auto d-flex" name="contribute-btn">Contribute</button>
                        </div>
                    </div>
                </form>
            <!-- Confirmation -->
            <?php else: ?>
                <div class="z-1 p-5 rounded shadow border position-relative bg-light bg-opacity-50 mx-lg-5">
                    <h1 class="lh-1 text-center">Thank You for Sharing</h1>
                    <p class="pb-2 text-center">Your shared bloom now becomes part of our garden</p>
                    <div class="row">
                        <div class="col-lg-5 d-flex align-items-center pb-4 pb-lg-0 mx-auto" id="flower-container">
                            <img src="img/flower/<?php echo $newImageName; ?>" alt="Flower Illustration" id="flower-display" class="rounded d-block mx-auto h-auto w-auto">
                        </div>

                        <div class="col-lg-7">
                            <p class="mb-1"><strong>Scientific Name:</strong> <em class="d-block d-sm-inline"><?php echo htmlspecialchars($scientificName); ?></em></p>
                            <p class="mb-1"><strong>Common Name:</strong> <span class="d-block d-sm-inline"><?php echo htmlspecialchars($commonName); ?></span></p>

                            <p class="mb-0"><strong>Description:</strong></p>
                            <p>
                                <?php echo nl2br(htmlspecialchars($descriptionText)); ?>
                            </p>

                            <div>
                                <a href="flower_description/<?php echo $generatedPdf; ?>" class="btn btn-primary mt-2 me-sm-2 mx-auto d-block d-sm-inline" download>
                                    Download Generated PDF
                                </a>
                                <a href="flower.php" class="btn mt-2 d-block d-sm-inline mx-auto">Contribute More</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Contributed Flower Grid Display -->
        <div class="text-center pb-3">
            <h2 class="">Our Community Flower Garden</h2>
            <p class="small">Browse the beautiful blooms shared by our community</p>
        </div>

        <div class="grid d-flex gap-2 flex-wrap mx-auto">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="card grid-item overflow-hidden border-0">
                        <img src="img/flower/<?= $row['plants_image']; ?>" alt="<?= $row['Common_Name']; ?>" class="h-100 w-auto object-fit-cover">

                        <div class="img-gradient position-absolute h-50 bottom-0 start-0 end-0"></div>
                        <div class="overlay-text position-absolute text-light w-100 d-flex justify-content-between align-items-end px-3">
                            <div>
                                <p class="fst-italic small m-0 lh-1"><?= $row['Scientific_Name']; ?></p>
                                <p class="fw-bold fs-5 m-0"><?= $row['Common_Name']; ?></p>
                            </div>
                            <a href="flower_description/<?= $row['description']; ?>" class="text-decoration-none text-light" download>
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </article>

	<?php include "include/footer.php"; ?>

<script src="js/main.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js" integrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous" async></script>
</body>
</html>