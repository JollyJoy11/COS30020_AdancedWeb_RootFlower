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
$default_sort = ($sort_item == 'upload_time' || $sort_item == 'likes') ? 'DESC' : 'ASC';
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : $default_sort;
$new_sort = ($sort_order=='ASC') ? 'DESC' : 'ASC';

$items_per_page = 5; //Number display per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Search & Workshop title filter
$search = isset($_GET['search']) ? sanitise_input($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'pending';
$workshop_filter = isset($_GET['workshop_title']) ? sanitise_input($_GET['workshop_title']) : '';

// Likes range filter
$likes_min = isset($_GET['likes_min']) ? (int)$_GET['likes_min'] : null;
$likes_max = isset($_GET['likes_max']) ? (int)$_GET['likes_max'] : null;

// Dates range filter
$date_from = isset($_GET['date_from']) && $_GET['date_from'] !== '' ? $_GET['date_from'] : null;
$date_to = isset($_GET['date_to']) && $_GET['date_to'] !== '' ? $_GET['date_to'] : null;

// Basic sql
$sql_base = "SELECT s.*, w.workshop_title FROM studentworks_table s JOIN workshop_table w ON s.workshop_id = w.id WHERE s.trash='no' AND s.approve_status = '$status_filter'";
$sql_total = "SELECT COUNT(*) FROM studentworks_table s JOIN workshop_table w ON s.workshop_id = w.id WHERE s.trash='no' AND s.approve_status = '$status_filter'";

// Search sql
if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($conn, $search); 
    $sql_search = " AND (s.first_name LIKE '%$search_escaped%' OR s.last_name LIKE '%$search_escaped%' OR s.email LIKE '%$search_escaped%' OR w.workshop_title LIKE '%$search_escaped%')";
    $sql_base .= $sql_search;
    $sql_total .= $sql_search;
}

// Workshop title sql
if (!empty($workshop_filter) && $workshop_filter != 'all') {
    $workshop_title_escaped = mysqli_real_escape_string($conn, $workshop_filter);
    $sql_workshop_filter = " AND w.workshop_title = '$workshop_title_escaped'";
    $sql_base .= $sql_workshop_filter;
    $sql_total .= $sql_workshop_filter;
}

// Likes range sql
if ($likes_min !== null && $likes_min > 0) {
    $sql_likes_min = " AND s.likes >= $likes_min";
    $sql_base .= $sql_likes_min;
    $sql_total .= $sql_likes_min;
}

if ($likes_max !== null && $likes_max > 0) {
    $sql_likes_max = " AND s.likes <= $likes_max";
    $sql_base .= $sql_likes_max;
    $sql_total .= $sql_likes_max;
}

// Date range filter
if ($date_from !== null) {
    $date_from_escaped = mysqli_real_escape_string($conn, $date_from);
    $sql_date_from = " AND DATE(s.upload_time) >= '$date_from_escaped'";
    $sql_base .= $sql_date_from;
    $sql_total .= $sql_date_from;
}

if ($date_to !== null) {
    $date_to_escaped = mysqli_real_escape_string($conn, $date_to);
    $sql_date_to = " AND DATE(s.upload_time) <= '$date_to_escaped'";
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

// Fetch Unique Workshop Titles for Filter Dropdown 
$title_query = "SELECT DISTINCT workshop_title FROM workshop_table WHERE trash = 'no' ORDER BY workshop_title ASC";
$title_result = mysqli_query($conn, $title_query); 
$unique_titles = [];
if ($title_result) {
    while ($row = mysqli_fetch_assoc($title_result)) {
        $unique_titles[] = $row['workshop_title'];
    }
}

// Url with query parameters
$base_url = "?status=" . urlencode($status_filter) . "&search=" . urlencode($search) . "&title=" . urlencode($sort_item) . "&sort=" . urlencode($sort_order) . "&workshop_title=" . urlencode($workshop_filter) . "&page=" . urlencode($page);
$link_base_url = "?status=" . urlencode($status_filter) . "&search=" . urlencode($search) . "&title=" . urlencode($sort_item) . "&sort=" . urlencode($sort_order) . "&workshop_title=" . urlencode($workshop_filter);

if ($likes_min !== null) {
    $base_url .= "&likes_min=" . $likes_min;
    $link_base_url .= "&likes_min=" . $likes_min;
}
if ($likes_max !== null) {
    $base_url .= "&likes_max=" . $likes_max;
    $link_base_url .= "&likes_max=" . $likes_max;
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
    // Approve
    if (isset($_POST['action_approve'])) {
        $id = (int)$_POST['action_approve'];
        mysqli_query($conn, "UPDATE studentworks_table SET approve_status='approved' WHERE id=$id");

        $select = mysqli_query($conn, "SELECT email FROM studentworks_table WHERE id=$id");
        $row = mysqli_fetch_assoc($select);
        generateUserNotification($conn, '', $row['email'], 'stuwork_app', $id, 'studentworks_table');
    }

    // Reject
    else if (isset($_POST['action_reject'])) {
        $id = (int)$_POST['action_reject'];
        mysqli_query($conn, "UPDATE studentworks_table SET approve_status='rejected' WHERE id=$id");

        $select = mysqli_query($conn, "SELECT email FROM studentworks_table WHERE id=$id");
        $row = mysqli_fetch_assoc($select);
        generateUserNotification($conn, '', $row['email'], 'stuwork_rej', $id, 'studentworks_table');
    }

    // Delete single
    else if (isset($_POST['action_delete'])) {
        $id = (int)$_POST['action_delete'];
        mysqli_query($conn, "UPDATE studentworks_table SET trash='yes', trash_date = NOW() WHERE id=$id");
        
        $alert['success'] = "Record deleted successfully.";
        $_SESSION['alert'] = $alert;
    }

    // Bulk approve
    else if (isset($_POST['action_bulk_approve'])) {
        if (isset($_POST['all_selected_ids']) && $_POST['all_selected_ids'] !== '') {
            $ids_string = $_POST['all_selected_ids'];

            $ids = array_map('intval', explode(",", $ids_string));
            $valid_ids = array_filter($ids, function($id) {
                return $id > 0;
            });

            if (!empty($valid_ids)) {
                $ids_sql = implode(",", $valid_ids);
                mysqli_query($conn, "UPDATE studentworks_table SET approve_status='approved' WHERE id IN ($ids_sql)");

                $select_users_sql = "SELECT id, email FROM $table_name WHERE id IN ($ids_sql)";
                $result = mysqli_query($conn, $select_users_sql);

                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $contributor_id = $row['id'];
                        $user_email = $row['email'];
                        
                        generateUserNotification($conn, '', $user_email, 'stuwork_app', $contributor_id, 'studentworks_table');
                    }
                }
            }
        }
    }

    // Bulk reject
    else if (isset($_POST['action_bulk_reject'])) {
        if (isset($_POST['all_selected_ids']) && $_POST['all_selected_ids'] !== '') {
            $ids_string = $_POST['all_selected_ids'];

            $ids = array_map('intval', explode(",", $ids_string));
            $valid_ids = array_filter($ids, function($id) {
                return $id > 0;
            });

            if (!empty($valid_ids)) {
                $ids_sql = implode(",", $valid_ids);
                mysqli_query($conn, "UPDATE studentworks_table SET approve_status='rejected' WHERE id IN ($ids_sql)");

                $select_users_sql = "SELECT id, email FROM $table_name WHERE id IN ($ids_sql)";
                $result = mysqli_query($conn, $select_users_sql);

                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $contributor_id = $row['id'];
                        $user_email = $row['email'];
                        
                        generateUserNotification($conn, '', $user_email, 'stuwork_rej', $contributor_id, 'studentworks_table');
                    }
                }
            }
        }
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
                mysqli_query($conn, "UPDATE studentworks_table SET trash='yes', trash_date = NOW() WHERE id IN ($ids_sql)");
                
                $alert['success'] = "Records deleted successfully.";
                $_SESSION['alert'] = $alert;
            }
        }
    }

    header("Location: manage_studentwork.php" . $link_base_url);
    exit;
}

// For select all records of the current sorting and filter
$sql_all_ids = str_replace('SELECT s.*, w.workshop_title', 'SELECT s.id', $sql_base);
$result_all_ids = mysqli_query($conn, $sql_all_ids);
$all_filtered_ids = [];

if ($result_all_ids) {
    while ($row = mysqli_fetch_assoc($result_all_ids)) {
        $all_filtered_ids[] = $row['id'];
    }
}

mysqli_close($conn);

function sort_link($field, $display_name, $current_sort_item, $current_sort_order, $search, $status_filter, $workshop_filter, $likes_min, $likes_max, $date_from, $date_to) {
    // Determine the next sort order
    $next_sort_order = ($current_sort_item == $field && $current_sort_order == 'ASC') ? 'DESC' : 'ASC';
    if ($current_sort_item != $field) {
        // Set default ASC/DESC for a new column
        $next_sort_order = ($field == 'upload_time' || $field == 'likes') ? 'DESC' : 'ASC';
    }

    // Determine the icon to display
    $icon = '';
    $icon_class = 'text-light';

    if ($current_sort_item == $field) {
        if ($field == 'id' || $field == 'likes' || $field == 'upload_time') {
             $icon = '<i class="bi bi-sort-numeric-' . ($current_sort_order == 'ASC' ? 'down' : 'up') . '"></i>';
        } else {
             $icon = '<i class="bi bi-sort-alpha-' . ($current_sort_order == 'ASC' ? 'down' : 'up') . '"></i>';
        }
    } else {
        $icon_class = 'sort-text'; 
        if ($field == 'id' || $field == 'likes' || $field == 'upload_time') {
             $icon = '<i class="bi bi-sort-numeric-down-alt"></i>'; 
        } else {
             $icon = '<i class="bi bi-sort-alpha-down"></i>'; 
        }
    }

    // Build the URL parameters
    $url = "?title=$field&sort=$next_sort_order&status=" . urlencode($status_filter);
    if (!empty($search)) $url .= "&search=" . urlencode($search);
    if (!empty($workshop_filter)) $url .= "&workshop_title=" . urlencode($workshop_filter);
    if ($likes_min !== null) $url .= "&likes_min=" . $likes_min;
    if ($likes_max !== null) $url .= "&likes_max=" . $likes_max;
    if ($date_from !== null) $url .= "&date_from=" . urlencode($date_from);
    if ($date_to !== null) $url .= "&date_to=" . urlencode($date_to);

    return '<a href="' . $url . '" class="'.$icon_class.' text-decoration-none d-flex align-items-center justify-content-center">' . $display_name . '&nbsp;' . $icon . '</a>';
}
?>
<!DOCTYPE html>

<html lang="en">
<!-- Description: Manage Studentworks -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 11/11/2025 -->
<!-- Validated: OK 6/12/2025 -->

<head>
    <title>Manage Student Works | Root Flower</title>
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
		<h1 class="py-3">Manage Student Works</h1>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex fs-6">
                <ul class="nav nav-pills" id="status-tabs">
                    <li class="nav-item">
                        <a class="nav-link <?= $status_filter == 'pending' ? 'active' : '' ?> me-2 py-1 px-3 text-light" href="?status=pending">Pending</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $status_filter == 'approved' ? 'active' : '' ?> me-2 py-1 px-3 text-light" href="?status=approved">Approved</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $status_filter == 'rejected' ? 'active' : '' ?> me-3 py-1 px-3 text-light" href="?status=rejected">Rejected</a>
                    </li>
                </ul>
                
                <a href="manage_studentwork.php?status=<?php echo $status_filter; ?>" class="me-3 btn py-1 px-2 bg-white bg-opacity-75" id="refresh"><i class="bi bi-arrow-clockwise"></i></a>
            </div>

            <form method="GET" class="d-flex">
                <div class="input-group">
                    <input type="search" class="form-control" placeholder="Search..." name="search" value="<?= htmlspecialchars($search); ?>">
                    <button class="btn btn-primary py-0 px-2" type="submit"><i class="bi bi-search"></i></button>
                </div>
                
                <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter); ?>">
                <input type="hidden" name="title" value="<?= htmlspecialchars($sort_item); ?>">
                <input type="hidden"    name="sort" value="<?= htmlspecialchars($sort_order); ?>">
            </form>
        </div>

        <div class="mb-2 pt-2 pb-3 px-3 border rounded bg-secondary bg-opacity-25">
            <form method="GET" class="d-flex gap-4 justify-content-between filter align-items-end">
                <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter); ?>">
                <input type="hidden" name="search" value="<?= htmlspecialchars($search); ?>">
                <input type="hidden" name="title" value="<?= htmlspecialchars($sort_item); ?>">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort_order); ?>">

                <div>
                    <label for="workshopFilter" class="form-label fs-6 fw-bold mb-1">Filter by Workshop</label>
                    <select id="workshopFilter" name="workshop_title" class="form-select form-select-sm">
                        <option value="all" <?= (empty($workshop_filter) || $workshop_filter == 'all') ? 'selected' : ''; ?>>All Workshops</option>
                        <?php foreach ($unique_titles as $title): ?>
                            <option value="<?= htmlspecialchars($title); ?>" <?= ($workshop_filter == $title) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="likesRange" class="form-label fs-6 fw-bold mb-1">Likes Range
                    </label>
                    
                    <div id="likesSliderPlaceholder" class="d-flex gap-2">
                        <input type="number" name="likes_min" class="form-control form-control-sm" placeholder="Min" min="0" value="<?= $likes_min !== null ? $likes_min : '' ?>">
                        -
                        <input type="number" name="likes_max" class="form-control form-control-sm" placeholder="Max" min="0" value="<?= $likes_max !== null ? $likes_max : '' ?>">
                    </div>
                </div>

                <div>
                    <label class="form-label fs-6 fw-bold mb-1">Upload Date</label>
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
                            <th class="text-light"><?= sort_link('id', 'ID', $sort_item, $sort_order, $search, $status_filter, $workshop_filter, $likes_min, $likes_max, $date_from, $date_to) ?></th>
                            <th class="text-light"><?= sort_link('email', 'Email', $sort_item, $sort_order, $search, $status_filter, $workshop_filter, $likes_min, $likes_max, $date_from, $date_to) ?></th>
                            <th class="text-light"><?= sort_link('first_name', 'Name', $sort_item, $sort_order, $search, $status_filter, $workshop_filter, $likes_min, $likes_max, $date_from, $date_to) ?></th>
                            <th class="text-light"><?= sort_link('workshop_title', 'Workshop Title', $sort_item, $sort_order, $search, $status_filter, $workshop_filter, $likes_min, $likes_max, $date_from, $date_to) ?></th>
                            <th class="text-light">Decision</th>
                            <?php if ($status_filter != 'pending'): ?>
                                <th class="text-light"><?= sort_link('likes', 'Likes', $sort_item, $sort_order, $search, $status_filter, $workshop_filter, $likes_min, $likes_max, $date_from, $date_to) ?></th>
                            <?php endif; ?>
                            <th class="text-light"><?= sort_link('upload_time', 'Upload Time', $sort_item, $sort_order, $search, $status_filter, $workshop_filter, $likes_min, $likes_max, $date_from, $date_to) ?></th>
                            <th class="text-light">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($displayed_rows > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="text-center"><input type="checkbox" name="selected_ids[]" value="<?= $row['id']; ?>"></td>
                                    <td><?= $row['id']; ?></td>
                                    <td><?= $row['email']; ?></td>
                                    <td><?= $row['first_name']; ?> <?= $row['last_name']; ?></td>
                                    <td><?= $row['workshop_title']; ?></td>
                                    <td class="text-center">
                                        <?php if ($status_filter != 'approved'): ?>
                                            <button type="submit" name="action_approve" value="<?= $row['id']; ?>" class="btn btn-sm approve-btn">
                                                <i class="bi bi-check2"></i> APPROVE
                                            </button>
                                        <?php endif; ?> 
                                        <?php if ($status_filter != 'rejected'): ?>
                                            <button type="submit" name="action_reject" value="<?= $row['id']; ?>" class="btn btn-sm reject-btn">
                                                <i class="bi bi-x"></i> REJECT
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($status_filter != 'pending'): ?>
                                        <td class="text-center"><?= $row['likes']; ?></td>
                                    <?php endif; ?>
                                    <td><?= $row['upload_time']; ?></td>
                                    <td class="text-center">
                                        <a href="studentwork_detail.php?id=<?= $row['id']; ?>"><i class="bi bi-eye-fill"></i></a>&nbsp;
                                        <button type="submit" name="action_delete" value="<?= $row['id']; ?>" class="btn text-danger fs-5 p-0 border-0 align-baseline">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan='9' class="ps-3">No results found</td>
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
                    <?php if ($status_filter != 'approved'): ?>
                        <button type="submit" name="action_bulk_approve" class="btn btn-success btn-sm text-light border-success px-3">
                            <i class="bi bi-check2-square"></i> Approve Selected
                        </button>
                    <?php endif; ?>

                    <?php if ($status_filter != 'rejected'): ?>
                        <button type="submit" name="action_bulk_reject" class="btn btn-sm btn-warning text-muted border-warning px-3">
                            <i class="bi bi-x-square-fill"></i> Reject Selected
                        </button>
                    <?php endif; ?>
                    
                    <button type="submit" name="action_bulk_delete" class="btn btn-sm btn-danger text-light border-danger px-3">
                        <i class="bi bi-trash3-fill"></i> Delete Selected
                    </button>
                </div>
            </div>
        </form>
    </article>

	<footer class="mt-auto text-dark ps-4 ps-lg-5 py-2 fs-6">
		<p>&copy; 2025 Root Flower</p>
	</footer>

<script>
    // Injects the complete list of IDs currently matched by the filters.
    window.allSelectableIds = <?php echo json_encode($all_filtered_ids); ?>;
</script>
<script src="js/main.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>
</html>