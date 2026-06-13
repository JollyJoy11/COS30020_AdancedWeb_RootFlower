<?php 
include "include/session.php"; 
include "include/db_connect.php";

if (isset($_SESSION['user']) && $_SESSION['role'] !== 'admin'){
	header('Location:main_menu.php');
	exit;
} 

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

// Sort
$sort_item = isset($_GET['title']) ? $_GET['title'] : 'id';
$default_sort = ($sort_item == 'login_time') ? 'DESC' : 'ASC';
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : $default_sort;
$new_sort = ($sort_order=='ASC') ? 'DESC' : 'ASC';

$items_per_page = 5; //Number display per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Search & filter
$search = isset($_GET['search']) ? sanitise_input($_GET['search']) : '';
$gender_filter = isset($_GET['gender_filter']) ? $_GET['gender_filter'] : 'all';
$newsletter_filter = isset($_GET['newsletter']) ? $_GET['newsletter'] : 'all';

// Dates range filter
$date_from = isset($_GET['date_from']) && $_GET['date_from'] !== '' ? $_GET['date_from'] : null;
$date_to = isset($_GET['date_to']) && $_GET['date_to'] !== '' ? $_GET['date_to'] : null;

// Basic sql
$sql_base = "SELECT u.*, a.login_time FROM user_table u JOIN account_table a ON a.email = u.email WHERE u.trash='no' AND a.type = 'user'";
$sql_total = "SELECT COUNT(*) FROM user_table u JOIN account_table a ON a.email = u.email WHERE u.trash='no' AND a.type = 'user'";

// Search sql
if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($conn, $search); 
    $sql_search = " AND (u.first_name LIKE '%$search_escaped%' OR u.last_name LIKE '%$search_escaped%' OR u.email LIKE '%$search_escaped%' OR u.hometown LIKE '%$search_escaped%')";
    $sql_base .= $sql_search;
    $sql_total .= $sql_search;
}

// Newsletter filter
if (!empty($newsletter_filter) && $newsletter_filter != 'all') {
    $newsletter_escaped = mysqli_real_escape_string($conn, $newsletter_filter);
    $sql_newsletter_filter = " AND u.newsletter = '$newsletter_escaped'";
    $sql_base .= $sql_newsletter_filter;
    $sql_total .= $sql_newsletter_filter;
}

// Gender filter
if (!empty($gender_filter) && $gender_filter != 'all') {
    $gender_escaped = mysqli_real_escape_string($conn, $gender_filter);
    $sql_gender_filter = " AND u.gender = '$gender_escaped'";
    $sql_base .= $sql_gender_filter;
    $sql_total .= $sql_gender_filter;
}

// Date range filter
if ($date_from !== null) {
    $date_from_escaped = mysqli_real_escape_string($conn, $date_from);
    $sql_date_from = " AND DATE(a.login_time) >= '$date_from_escaped'";
    $sql_base .= $sql_date_from;
    $sql_total .= $sql_date_from;
}

if ($date_to !== null) {
    $date_to_escaped = mysqli_real_escape_string($conn, $date_to);
    $sql_date_to = " AND DATE(a.login_time) <= '$date_to_escaped'";
    $sql_base .= $sql_date_to;
    $sql_total .= $sql_date_to;
}

// Sort sql
$sql = $sql_base . " ORDER BY $sort_item $sort_order LIMIT $offset, $items_per_page";

$result_total = mysqli_query($conn, $sql_total); //Count displayed items
$row_total = mysqli_fetch_array($result_total); 
$total_rows = $row_total[0];

$result = mysqli_query($conn, $sql);
$displayed_rows = mysqli_num_rows($result);

$start_no = $offset + 1;
$end_no = $offset + $displayed_rows;

// Url with query parameters
$base_url = "?search=" . urlencode($search) . "&title=" . urlencode($sort_item) . "&sort=" . urlencode($sort_order) . "&page=" . urlencode($page);
$link_base_url = "?search=" . urlencode($search) . "&title=" . urlencode($sort_item) . "&sort=" . urlencode($sort_order);

if (!empty($newsletter_filter) && $newsletter_filter != 'all') {
    $base_url .= "&newsletter=" . urlencode($newsletter_filter);
    $link_base_url .= "&newsletter=" . urlencode($newsletter_filter);
}

if (!empty($gender_filter) && $gender_filter != 'all') {
    $base_url .= "&gender=" . urlencode($gender_filter);
    $link_base_url .= "&gender=" . urlencode($gender_filter);
}

if ($date_from !== null) {
    $base_url .= "&date_from=" . urlencode($date_from);
    $link_base_url .= "&date_from=" . urlencode($date_from);
}
if ($date_to !== null) {
    $base_url .= "&date_to=" . urlencode($date_to);
    $link_base_url .= "&date_to=" . urlencode($date_to);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Delete single
    if (isset($_POST['action_delete'])) {
        $id = (int)$_POST['action_delete'];
        mysqli_query($conn, "UPDATE user_table SET trash='yes', trash_date = NOW() WHERE id=$id");
        
        $alert['success'] = "Records deleted successfully.";
        $_SESSION['alert'] = $alert;

        header("Location: manage_accounts.php" . $link_base_url);
        exit;
    }

    // Bulk delete
    else if (isset($_POST['action_bulk_delete'])) {
        if (isset($_POST['all_selected_ids']) && $_POST['all_selected_ids'] !== '') {
            $ids_string = $_POST['all_selected_ids'];
            
            $ids = array_map('intval', explode(",", $ids_string));
            
            $valid_ids = array_filter($ids, function($id) {
                return $id > 0;
            });
            
            if (!empty($valid_ids)) {
                $ids_sql = implode(",", $valid_ids);
                mysqli_query($conn, "UPDATE user_table SET trash='yes', trash_date = NOW() WHERE id IN ($ids_sql)");
                
                $alert['success'] = "Records deleted successfully.";
                $_SESSION['alert'] = $alert;
            }
        }

        header("Location: manage_accounts.php" . $link_base_url);
        exit;
    }

    // Register User
    else if (isset($_POST['action_register_user'])){
        $firstName = ucwords(strtolower(sanitise_input($_POST['firstName'])));
        $lastName = ucwords(strtolower(sanitise_input($_POST['lastName'])));
        $dob = sanitise_input($_POST['dob']);
        $gender = $_POST['gender'];
        $email = sanitise_input($_POST['email']);
        $hometown = ucwords(strtolower(sanitise_input($_POST['hometown'])));
        $password = sanitise_input($_POST['password']);
        $cf_password = sanitise_input($_POST['cf-password']);

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

        // Date of Birth
        if (empty($dob)) {
            $errors['dob'] = "* Date of birth is required.";
        } else {
            $date = DateTime::createFromFormat('Y-m-d', $dob);
            $dateErrors = DateTime::getLastErrors();
            
            $is_invalid = false;
            
            if ($date === false) {
                $is_invalid = true;
            }
            
            if (is_array($dateErrors)) {
                if ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0) {
                    $is_invalid = true;
                }
            }

            if ($date && $date > new DateTime()) {
                $is_invalid = true;
            }
            
            if ($is_invalid) {
                $errors['dob'] = "* Invalid date of birth.";
            }
        }

        // Email
        if (empty($email)) {
            $errors['email'] = "* Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "* Invalid email format.";
        }

        // Hometown
        if (empty($hometown)) {
            $errors['hometown'] = "* Hometown is required.";
        }

        // Password
        if (empty($password)) {
            $errors['password'] = "* Password is required.";
        } elseif (!preg_match("/^(?=.*[0-9])(?=.*[!@#$%^&*]).{8,}$/", $password)) {
            $errors['password'] = "* Must be at least 8 characters, contain a number and a symbol.";
        } elseif ($password !== $cf_password) {
            $errors['password'] = "* Passwords do not match.";
        }

        // Check for duplicate email
        $duplicate_query = "SELECT * FROM user_table WHERE email = '$email' AND trash='no'";
        $duplicate_result = mysqli_query($conn, $duplicate_query);
        if (mysqli_num_rows($duplicate_result) > 0) {
            $errors['duplicate'] = "* Email has already been registered.";
        }

        // save user data into database
        if (empty($errors)) {
            $user_query = "INSERT INTO user_table (email, first_name, last_name, dob, gender, hometown) VALUES ('$email', '$firstName', '$lastName', '$dob', '$gender', '$hometown')";
            $account_query = "INSERT INTO account_table (email, password) VALUES ('$email', '" . password_hash($password, PASSWORD_DEFAULT) . "')";

            if (mysqli_query($conn, $user_query) && mysqli_query($conn, $account_query)) {
                $to = $email; 
                $subject = "Welcome to Root Flower"; 
                $message = "Hi $firstName, \n\nWelcome to Root Flower! We're so happy to have you join our community.\n\nWith your new account, you can explore beautiful flower arrangements, discover tips to brighten your space, and enjoy exclusive member updates.\n\nIf you have any questions or need assistance, we're always here to help.\n\nThanks for joining us,\nRoot Flower Team"; 

                if (sendEmail($to, $subject, $message)) {
                    unset($errors);
                    header("Location: manage_accounts.php" . $base_url);
                    $alert['success'] = "User registered successfully.";
                    $_SESSION['alert'] = $alert;
                    exit;
                }
            } 
        } 
    }

    // Edit User
    else if (isset($_POST['action_edit_user'])) {
        $edit_id = (int)$_POST['edit_id'];
        $edit_firstName = ucwords(strtolower(sanitise_input($_POST['edit_firstName'])));
        $edit_lastName = ucwords(strtolower(sanitise_input($_POST['edit_lastName'])));
        $edit_dob = sanitise_input($_POST['edit_dob']);
        $edit_gender = $_POST['edit_gender'];
        $edit_hometown = ucwords(strtolower(sanitise_input($_POST['edit_hometown'])));
        $edit_newsletter = $_POST['edit_newsletter_status'];
        
        $edit_errors = [];

        // First Name
		if(empty($edit_firstName)) {
			$edit_errors['firstName'] = "* First name is required.";
		} else if (!preg_match("/^[A-Za-z\s]+$/", $edit_firstName)) {
			$edit_errors['firstName'] = "* Name can contain only letters and white spaces.";
		}

		// Last Name
		if (empty($edit_lastName)) {
			$edit_errors['lastName'] = "* Last name is required.";
		} else if (!preg_match("/^[A-Za-z\s]+$/", $edit_lastName)) {
			$edit_errors['lastName'] = "* Name can contain only letters and white spaces.";
		}

		// Date of Birth
		if (empty($edit_dob)) {
			$edit_errors['dob'] = "* Date of birth is required.";
		} else if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $edit_dob) || strtotime($edit_dob) > time()) {
			$edit_errors['dob'] = "* Invalid date of birth.";
		}

		// Hometown
		if (empty($edit_hometown)) {
			$edit_errors['hometown'] = "* Hometown is required.";
		}

        if (empty($edit_errors)){
            $update_fields = array(
                "first_name = '$edit_firstName'",
                "last_name = '$edit_lastName'",
                "dob = '$edit_dob'",
                "gender = '$edit_gender'",
                "hometown = '$edit_hometown'",
                "newsletter = '$edit_newsletter'"
            );
            
            if (isset($_POST['delete-profile'])){
                // Select old profile pic
                $select_profile = "SELECT profile_image FROM user_table WHERE id = $edit_id";
                $select_result = mysqli_query($conn, $select_profile);
                $oldprofile_path = mysqli_fetch_assoc($select_result)['profile_image'];

                $update_fields[] = "profile_image = NULL";
            }

            $set_clause = implode(', ', $update_fields);
    
            $update_sql = "UPDATE user_table SET $set_clause WHERE id = $edit_id";
            
            if (mysqli_query($conn, $update_sql)) {
                if (isset($_POST['delete-profile']) && $oldprofile_path !== null && file_exists("profile_images/" . $oldprofile_path)){
                    unlink("profile_images/" . $oldprofile_path);
                }

                $select = mysqli_query($conn, "SELECT email FROM user_table WHERE id=$edit_id");
                $row = mysqli_fetch_assoc($select);
                generateUserNotification($conn, '', $row['email'], 'profile_update', $edit_id, 'user_table');
                
                $alert['success'] = "User details updated successfully.";
                $_SESSION['alert'] = $alert;
                unset($edit_errors);
            } else {
                $alert['danger'] = "An error has occured.";
                $_SESSION['alert'] = $alert;
            }

            header("Location: manage_accounts.php" . $base_url);
            exit;
        } 
    }

    else if (isset($_POST['reset'])) {
        header("Location: manage_accounts.php" . $base_url);
        exit;
    }
}

// For select all records of the current sorting and filter
$sql_all_ids = str_replace('SELECT u.*, a.login_time', 'SELECT u.id', $sql_base);
$result_all_ids = mysqli_query($conn, $sql_all_ids);
$all_filtered_ids = [];

if ($result_all_ids) {
    while ($row = mysqli_fetch_assoc($result_all_ids)) {
        $all_filtered_ids[] = $row['id'];
    }
}

function sort_link($field, $display_name, $current_sort_item, $current_sort_order, $search, $date_from, $date_to, $newsletter_filter, $gender_filter) {
    // Determine the next sort order
    $next_sort_order = ($current_sort_item == $field && $current_sort_order == 'ASC') ? 'DESC' : 'ASC';
    if ($current_sort_item != $field) {
        // Set default ASC/DESC for a new column
        $next_sort_order = ($field == 'login_time') ? 'DESC' : 'ASC';
    }

    // Determine the icon to display
    $icon = '';
    $icon_class = 'text-light';

    if ($current_sort_item == $field) {
        if ($field == 'id' || $field == 'login_time') {
            $icon = '<i class="bi bi-sort-numeric-' . ($current_sort_order == 'ASC' ? 'down' : 'up') . '"></i>';
        } else {
            $icon = '<i class="bi bi-sort-alpha-' . ($current_sort_order == 'ASC' ? 'down' : 'up') . '"></i>';
        }
    } else {
        $icon_class = 'sort-text'; 
        if ($field == 'id' || $field == 'login_time') {
            $icon = '<i class="bi bi-sort-numeric-down-alt"></i>'; 
        } else {
            $icon = '<i class="bi bi-sort-alpha-down"></i>'; 
        }
    }

    // Build the URL parameters
    $url = "?title=$field&sort=$next_sort_order";
    if (!empty($search)) $url .= "&search=" . urlencode($search);
    if (!empty($newsletter_filter) && $newsletter_filter != 'all') $url .= "&newsletter=" . urlencode($newsletter_filter);
    if (!empty($gender_filter) && $gender_filter != 'all') $url .= "&gender=" . urlencode($gender_filter);
    if ($date_from !== null) $url .= "&date_from=" . urlencode($date_from);
    if ($date_to !== null) $url .= "&date_to=" . urlencode($date_to);

    return '<a href="' . $url . '" class="'.$icon_class.' text-decoration-none d-flex align-items-center justify-content-center">' . $display_name . '&nbsp;' . $icon . '</a>';
}

mysqli_close($conn);
?>
<!DOCTYPE html>

<html lang="en">
<!-- Description: Manage User -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 26/11/2025 -->
<!-- Validated: OK 6/12/2025 -->

<head>
    <title>Manage User Accounts | Root Flower</title>
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

<body class="fs-5 d-flex flex-column vh-100" id="admin-menu">
	<?php include "include/header_admin.php"; ?>

    <article class="px-5 py-3 text-dark">
		<h1 class="py-3">Manage User Accounts</h1>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex fs-6">
                <button type="button" class="me-3 btn py-1 px-2 bg-white bg-opacity-75" data-bs-toggle="modal" data-bs-target="#addUser">Add User Account <i class="bi bi-person-plus"></i></button>
                <a href="manage_accounts.php" class="me-3 btn py-1 px-2 bg-white bg-opacity-75" id="refresh"><i class="bi bi-arrow-clockwise"></i></a>
            </div>

            <form method="GET" class="d-flex">
                <div class="input-group">
                    <input type="search" class="form-control" placeholder="Search..." name="search" value="<?= htmlspecialchars($search); ?>">
                    <button class="btn btn-primary py-0 px-2" type="submit"><i class="bi bi-search"></i></button>
                </div>
                
                <input type="hidden" name="title" value="<?= htmlspecialchars($sort_item); ?>">
                <input type="hidden"    name="sort" value="<?= htmlspecialchars($sort_order); ?>">
            </form>
        </div>

        <div class="mb-2 pt-2 pb-3 px-3 border rounded bg-secondary bg-opacity-25">
            <form method="GET" class="d-flex gap-4 justify-content-between filter align-items-end">
                <input type="hidden" name="search" value="<?= htmlspecialchars($search); ?>">
                <input type="hidden" name="title" value="<?= htmlspecialchars($sort_item); ?>">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort_order); ?>">

                <div>
                    <label for="newsletterFilter" class="form-label fs-6 fw-bold mb-1">Newsletter</label>
                    <select id="newsletterFilter" name="newsletter" class="form-select form-select-sm">
                        <option value="all" <?= ($newsletter_filter == 'all') ? 'selected' : ''; ?>>All</option>
                        <option value="yes" <?= ($newsletter_filter == 'yes') ? 'selected' : ''; ?>>Subscribed</option>
                        <option value="no" <?= ($newsletter_filter == 'no') ? 'selected' : ''; ?>>Not Subscribed</option>
                    </select>
                </div>
                
                <div>
                    <label for="genderFilter" class="form-label fs-6 fw-bold mb-1">Gender</label>
                    <select id="genderFilter" name="gender_filter" class="form-select form-select-sm">
                        <option value="all" <?= ($gender_filter == 'all') ? 'selected' : ''; ?>>All Genders</option>
                        <option value="Male" <?= ($gender_filter == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?= ($gender_filter == 'Female') ? 'selected' : ''; ?>>Female</option>
                        </select>
                </div>

                <div>
                    <label class="form-label fs-6 fw-bold mb-1">Last Login Date</label>
                    <div class="d-flex gap-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text text-light">From</span>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="<?= htmlspecialchars($date_from ?? ''); ?>">
                        </div>
                        -
                        <div class="input-group input-group-sm">
                            <span class="input-group-text text-light">To</span>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="<?= htmlspecialchars($date_to ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <button class="btn btn-sm btn-primary align-self-end py-1 px-3">
                    Apply Filters
                </button>
            </form>
        </div>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) .$base_url; ?>">
            <input type="hidden" name="all_selected_ids" id="allSelectedIds" value="">
            <div class="table-responsive">
                <table class="table table-light table-striped table-hover table-bordered table-sm m-0">
                    <thead class="text-center">
                        <tr>
                            <th class="fs-6 text-center text-light">
                                <input type="checkbox" id="selectAllCheckbox" class="d-none">
                                <i class="bi bi-square select-all-icon" id="selectAllIcon" role="button" title="Select/Deselect All"></i>
                            </th>
                            <th class="text-light"><?= sort_link('id', 'ID', $sort_item, $sort_order, $search, $date_from, $date_to, $newsletter_filter, $gender_filter) ?></th>
                            <th></th>
                            <th class="text-light"><?= sort_link('email', 'Email', $sort_item, $sort_order, $search, $date_from, $date_to, $newsletter_filter, $gender_filter) ?></th>
                            <th class="text-light"><?= sort_link('first_name', 'Name', $sort_item, $sort_order, $search, $date_from, $date_to, $newsletter_filter, $gender_filter) ?></th>
                            <th class="text-light text-nowrap"><?= sort_link('dob', 'Date of Birth', $sort_item, $sort_order, $search, $date_from, $date_to, $newsletter_filter, $gender_filter) ?></th>
                            <th class="text-light">Gender</th>
                            <th class="text-light"><?= sort_link('hometown', 'Hometown', $sort_item, $sort_order, $search, $date_from, $date_to, $newsletter_filter, $gender_filter) ?></th>
                            <th class="text-light">Newsletter</th>
                            <th class="text-light"><?= sort_link('login_time', 'Login Time', $sort_item, $sort_order, $search, $date_from, $date_to, $newsletter_filter, $gender_filter) ?></th>
                            <th class="text-light">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $edit_modals_html = [];
                        if ($displayed_rows > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): 
                                    if (isset($edit_errors) && !empty($edit_errors) && $row['id'] == ($edit_id ?? -1)){
                                        $data = [
                                            'id' => $row['id'],
                                            'email' => $row['email'], 
                                            'first_name' => $edit_firstName, 
                                            'last_name' => $edit_lastName,   
                                            'dob' => $edit_dob,             
                                            'gender' => $edit_gender,       
                                            'hometown' => $edit_hometown,   
                                            'newsletter' => $edit_newsletter, 
                                            'profile_image' => $row['profile_image'] 
                                        ];
                                        $errors_to_pass = $edit_errors;
                                    } else {
                                        $data = $row;
                                        $errors_to_pass = [];
                                    }
                            ?>
                                <tr>
                                    <td class="text-center"><input type="checkbox" name="selected_ids[]" value="<?= $row['id']; ?>"></td>
                                    <td><?= $row['id']; ?></td>
                                    <td class="text-center"><img src="profile_images/<?php echo ($row['profile_image'] !== null) ? $row['profile_image'] : (($row['gender'] == 'Female') ? 'girl.png' : 'boy.png'); ?>" alt="<?= $row['first_name']; ?>'s Profile Image" width=35 class="rounded-circle"></td>
                                    <td><?= $row['email']; ?></td>
                                    <td class="text-nowrap"><?= $row['first_name']; ?> <?= $row['last_name']; ?></td>
                                    <td class="text-nowrap"><?= $row['dob']; ?></td>
                                    <td><?= $row['gender']; ?></td>
                                    <td class="text-nowrap"><?= $row['hometown']; ?></td>
                                    <td class="text-center"><?= $row['newsletter']; ?></td>
                                    <td class="text-nowrap"><?= $row['login_time']; ?></td>
                                    <td class="text-center">
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#editUserModal_<?= $row['id'] ?>" class="btn fs-5 p-0 border-0 align-baseline text-primary">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>&nbsp;
                                        <?php $edit_modals_html[] = editUser_modal($data['id'], $data['email'], $data['first_name'], $data['last_name'], $data['dob'], $data['gender'], $data['hometown'], $data['newsletter'], $data['profile_image'], $base_url, $errors_to_pass); ?>
                                        <button type="submit" name="action_delete" value="<?= $row['id']; ?>" class="btn text-danger fs-5 p-0 border-0 align-baseline">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan='11' class="ps-3">No results found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center my-2">
                <p class="m-0">Showing results <strong>
                    <?php 
                    if ($displayed_rows == 0) {
                        $start_no = 0;
                    } 
                    if ($start_no == $end_no){
                        echo "$start_no"; 
                    } else {
                        echo "$start_no - $end_no"; 
                    }?></strong> 
                out of <strong><?php echo $total_rows; ?></strong></p>
                
                <nav>
                    <ul class="pagination z-0 m-0"> 
                    <?php if ($page > 1): ?> <!-- Previous page -->
                        <li class="page-item"><a href="<?php echo $link_base_url; ?>&page=<?php echo $page - 1; ?>" class="page-link py-1">Previous</a></li> 
                    <?php endif; ?> 
                    <?php 
                        $total_pages = ceil($total_rows / $items_per_page);
                        $start_page = max(1, $page - 1);
                        $end_page = min($total_pages, $page + 1);

                        if ($end_page - $start_page + 1 < 3){
                            if ($start_page == 1) { 
                                $end_page = min(3, $total_pages); 
                            } else { 
                                $start_page = max(1, $end_page - 2); 
                            }
                        }
                        for ($i = $start_page; $i <= $end_page; $i++){
                            $page_url = $link_base_url . "&page=$i";
                            if ($i == $page){
                                echo "<li class='page-item'><a href='$page_url' class='page-link  py-1 active'>$i</a></li>"; 
                            } else {
                                echo "<li class='page-item'><a href='$page_url' class='page-link py-1'>$i</a></li>"; 
                            }
                        } 
                    ?>
                    <?php if ($page < $total_pages): ?> <!-- Next page -->
                        <li class="page-item"><a href="<?php echo $link_base_url; ?>&page=<?php echo $page + 1; ?>" class="page-link py-1">Next</a></li>
                    <?php endif; ?> 
                    </ul>
                </nav>

                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" name="action_bulk_delete" class="btn btn-sm btn-danger text-light border-danger px-3">
                        <i class="bi bi-trash3-fill"></i> Delete Selected
                    </button>
                </div>
            </div>
        </form>

        <div class="modal fade" id="addUser" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered mx-auto">
                <div class="modal-content p-2">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . $base_url; ?>">
                        <div class="modal-header">
                            <h2 class="modal-title">Register User</h2>
                            <button type="submit" name="reset" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group col-md-6 mb-2">
                                    <label for="firstName">First Name</label>
                                    <input type="text" class="form-control <?php echo isset($_POST['action_register_user']) ? (isset($errors['firstName']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="firstName" name="firstName" value="<?php echo isset($_POST['firstName']) ? $_POST['firstName'] : ''; ?>">
                                </div>
                                <div class="form-group col-md-6 mb-2">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" class="form-control <?php echo isset($_POST['action_register_user']) ? (isset($errors['lastName']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="lastName" name="lastName" value="<?php echo isset($_POST['lastName']) ? $_POST['lastName'] : ''; ?>">
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
                                    <label for="dob">Date of Birth</label>
                                    <input type="date" class="form-control <?php echo isset($_POST['action_register_user']) ? (isset($errors['dob']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="dob" name="dob" value="<?php echo isset($_POST['dob']) ? $_POST['dob'] : ''; ?>">
                                    <div class="invalid-feedback">
                                        <?php echo $errors['dob'] ?? ''; ?>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="form-label d-block">Gender</label>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input mt-0" type="radio" id="female" name="gender" value="Female" <?php echo (!isset($_POST['action_register_user']) || (isset($_POST['gender']) && $_POST['gender'] == 'Female')) ? 'checked' : '' ; ?>>
                                        <label class="form-check-label" for="female">Female</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input mt-0" type="radio" id="male" name="gender" value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'checked' : '' ; ?>>
                                        <label class="form-check-label" for="male">Male</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-2">
                                <label for="email">Email</label>
                                <input type="text" class="form-control <?php echo isset($_POST['action_register_user']) ? ((isset($errors['email']) || isset($errors['duplicate'])) ? 'is-invalid' : 'is-valid') : ''; ?>" id="email" name="email" placeholder="e.g. abc@gmail.com" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
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

                            <div class="form-group col-md-6 mb-2">
                                <label for="hometown">Hometown</label>
                                <input type="text" class="form-control <?php echo isset($_POST['action_register_user']) ? (isset($errors['hometown']) ? 'is-invalid' : 'is-valid') : ''; ?>" id="hometown" name="hometown" placeholder="e.g. Kuching, Sarawak" value="<?php echo $_POST['hometown'] ?? ''; ?>">
                                <div class="invalid-feedback">
                                    <?php echo $errors['hometown'] ?? ''; ?>
                                </div>
                            </div>

                            <div class="form-group col-11 col-md-6 mb-2 position-relative">
                                <label for="password">Password&emsp;<span data-bs-toggle="tooltip" data-bs-placement="right" title="Password must be at least 8 characters, include a number and a symbol."><i class="bi bi-info-circle"></i></span></label>
                                <input type="checkbox" id="showPassword" class="d-none">
                                <input type="text" class="form-control password-field <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" id="password" name="password">

                                <label for="showPassword" class="position-absolute toggle-password">
                                    <i class="bi bi-eye-slash"></i>
                                    <i class="bi bi-eye d-none"></i>
                                </label>
                                <div class="invalid-feedback">
                                    <?php echo $errors['password'] ?? ''; ?>
                                </div>
                            </div>

                            <div class="form-group col-11 col-md-6 mb-2 position-relative">
                                <label for="cf-password">Confirm Password</label>
                                <input type="checkbox" id="showPassword1" class="d-none">
                                <input type="text" class="form-control password-field" id="cf-password" name="cf-password">

                                <label for="showPassword1" class="position-absolute toggle-password">
                                    <i class="bi bi-eye-slash"></i>
                                    <i class="bi bi-eye d-none"></i>
                                </label>
                                <div class="invalid-feedback">
                                    <?php echo $errors['cf_password'] ?? ''; ?>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" name="reset" class="btn" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" name="action_register_user">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php 
            if (!empty($edit_modals_html)){
                echo implode('', $edit_modals_html);
            }
        ?>
    </article>

	<footer class="mt-auto text-dark ps-4 ps-lg-5 py-2 fs-6">
		<p>&copy; 2025 Root Flower</p>
	</footer>

<?php if (isset($_POST['action_register_user']) && !empty($errors)): ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var addUserModal = new bootstrap.Modal(document.getElementById('addUser'));
        addUserModal.show();
    }); 
    </script>
<?php endif; ?>

<script>
    // Injects the complete list of IDs currently matched by the filters.
    window.allSelectableIds = <?php echo json_encode($all_filtered_ids); ?>;
</script> 
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
<script src="js/main.js"></script>
<!-- Open Edit Modal if got errors -->
<?php if (isset($_POST['action_edit_user']) && !empty($edit_errors)): ?>
    <script>
    window.onload = function() {
        var modalElement = document.getElementById('editUserModal_<?= str_pad($edit_id, 4, '0', STR_PAD_LEFT) ?>');
        if (modalElement) {
            var editUserModal = new bootstrap.Modal(modalElement);
            editUserModal.show();
        }
    };
    </script>
<?php endif; ?>
</body>
</html>