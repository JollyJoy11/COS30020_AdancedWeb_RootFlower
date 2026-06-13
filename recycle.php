<?php 
include "include/session.php"; 
include "include/db_connect.php";

$notification_data = displayNotification($conn, $_SESSION['user']);
$unread_notifications = $notification_data['notifications'];
$unread_count = $notification_data['count'];

if (isset($_SESSION['user']) && $_SESSION['role'] !== 'admin'){
    header('Location:main_menu.php');
    exit;
} 

// Table filter
$record_type = isset($_GET['type']) ? $_GET['type'] : 'users'; 
$valid_types = ['users', 'workshops', 'studentworks'];

// Sort
$sort_item = isset($_GET['title']) ? $_GET['title'] : 'id';
$default_sort = ($sort_item == 'trash_date') ? 'DESC' : 'ASC'; 
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : $default_sort;

$items_per_page = 5; //Number display per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

$search = isset($_GET['search']) ? sanitise_input($_GET['search']) : '';

// Dates range filter
$date_from = isset($_GET['date_from']) && $_GET['date_from'] !== '' ? $_GET['date_from'] : null;
$date_to = isset($_GET['date_to']) && $_GET['date_to'] !== '' ? $_GET['date_to'] : null;

$sql_base = "";
$sql_total = "";

if ($record_type == 'users') {
    $sql_base = "SELECT * FROM user_table WHERE trash='yes'";
    $sql_total = "SELECT COUNT(*) FROM user_table WHERE trash='yes'";
} elseif ($record_type == 'workshops') {
    $sql_base = "SELECT * FROM workshop_table WHERE trash='yes'";
    $sql_total = "SELECT COUNT(*) FROM workshop_table WHERE trash='yes'";
} elseif ($record_type == 'studentworks') {
    $sql_base = "SELECT s.*, w.workshop_title FROM studentworks_table s JOIN workshop_table w ON s.workshop_id = w.id WHERE s.trash='yes'";
    $sql_total = "SELECT COUNT(*) FROM studentworks_table s JOIN workshop_table w ON s.workshop_id = w.id WHERE s.trash='yes'";
}

// Search sql 
if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($conn, $search);
    $search_conditions = [];

    if ($record_type == 'users') {
        $search_conditions[] = "first_name LIKE '%$search_escaped%'";
        $search_conditions[] = "last_name LIKE '%$search_escaped%'";
        $search_conditions[] = "email LIKE '%$search_escaped%'";
    } elseif ($record_type == 'workshops') {
        $search_conditions[] = "workshop_title LIKE '%$search_escaped%'";
        $search_conditions[] = "first_name LIKE '%$search_escaped%'";
        $search_conditions[] = "last_name LIKE '%$search_escaped%'";
    } elseif ($record_type == 'studentworks') {
        $search_conditions[] = "s.first_name LIKE '%$search_escaped%'";
        $search_conditions[] = "s.last_name LIKE '%$search_escaped%'";
        $search_conditions[] = "w.workshop_title LIKE '%$search_escaped%'";
    }
    
    if (!empty($search_conditions)) {
        $sql_search = " AND (" . implode(" OR ", $search_conditions) . ")";
        $sql_base .= $sql_search;
        $sql_total .= $sql_search;
    }
}

// Date range filter
if ($date_from !== null) {
    $date_from_escaped = mysqli_real_escape_string($conn, $date_from);
    $prefix = ($record_type == 'studentworks') ? 's.' : '';
    $sql_date_from = " AND DATE({$prefix}trash_date) >= '$date_from_escaped'";
    $sql_base .= $sql_date_from;
    $sql_total .= $sql_date_from;
}

if ($date_to !== null) {
    $date_to_escaped = mysqli_real_escape_string($conn, $date_to);
    $prefix = ($record_type == 'studentworks') ? 's.' : '';
    $sql_date_to = " AND DATE({$prefix}trash_date) <= '$date_to_escaped'";
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
$base_url = "?type=" . urlencode($record_type) . "&search=" . urlencode($search) . "&title=" . urlencode($sort_item) . "&sort=" . urlencode($sort_order) . "&page=" . urlencode($page);
$link_base_url = "?type=" . urlencode($record_type) . "&search=" . urlencode($search) . "&title=" . urlencode($sort_item) . "&sort=" . urlencode($sort_order);

if ($date_from !== null) {
    $base_url .= "&date_from=" . urlencode($date_from);
    $link_base_url .= "&date_from=" . urlencode($date_from);
}
if ($date_to !== null) {
    $base_url .= "&date_to=" . urlencode($date_to);
    $link_base_url .= "&date_to=" . urlencode($date_to);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_table = '';

    if ($record_type == 'users') { 
        $current_table = 'user_table'; 
    } elseif ($record_type == 'workshops') { 
        $current_table = 'workshop_table'; 
    } elseif ($record_type == 'studentworks') { 
        $current_table = 'studentworks_table'; 
    }

    // Restore single
    if (isset($_POST['action_restore'])) {
        $id = (int)$_POST['action_restore'];
        mysqli_query($conn, "UPDATE $current_table SET trash='no', trash_date = NULL WHERE id=$id");
        $alert['success'] = "The data has been restored successfully.";
        $_SESSION['alert'] = $alert;
    }

    // Delete Permanently single
    else if (isset($_POST['action_delete_perm'])) {
        $id = (int)$_POST['action_delete_perm'];
        mysqli_query($conn, "DELETE FROM $current_table WHERE id=$id");
        $alert['success'] = "The data has been deleted permanently.";
        $_SESSION['alert'] = $alert;
    }

    // Bulk restore
    else if (isset($_POST['action_bulk_restore'])) {
        if (isset($_POST['all_selected_ids']) && $_POST['all_selected_ids'] !== '') {
            $ids_string = $_POST['all_selected_ids'];

            $ids = array_map('intval', explode(",", $ids_string));
            $valid_ids = array_filter($ids, function($id) { 
                return $id > 0; 
            });

            if (!empty($valid_ids)) {
                $ids_sql = implode(",", $valid_ids);
                mysqli_query($conn, "UPDATE $current_table SET trash='no', trash_date = NULL WHERE id IN ($ids_sql)");
                $alert['success'] = "The data has been restored successfully.";
                $_SESSION['alert'] = $alert;
            }
        }
    }

    // Bulk delete permanently
    else if (isset($_POST['action_bulk_delete_perm'])) {
        if (isset($_POST['all_selected_ids']) && $_POST['all_selected_ids'] !== '') {
            $ids_string = $_POST['all_selected_ids'];
            $ids = array_map('intval', explode(",", $ids_string));
            $valid_ids = array_filter($ids, function($id) { return $id > 0; });
            
            if (!empty($valid_ids)) {
                $ids_sql = implode(",", $valid_ids);
                mysqli_query($conn, "DELETE FROM $current_table WHERE id IN ($ids_sql)");
                $alert['success'] = "The data has been deleted permanently.";
                $_SESSION['alert'] = $alert;
            }
        }
    }
    
    header("Location: recycle.php" . $base_url);
    exit;
}

// For select all records of the current sorting and filter
$sql_all_ids = str_replace(
    ($record_type == 'studentworks' ? 'SELECT s.*, w.workshop_title' : 'SELECT *'), 
    ($record_type == 'studentworks' ? 'SELECT s.id' : 'SELECT id'), 
    $sql_base
);

$result_all_ids = mysqli_query($conn, $sql_all_ids);
$all_filtered_ids = [];

if ($result_all_ids) {
    while ($row = mysqli_fetch_assoc($result_all_ids)) {
        $all_filtered_ids[] = $row['id'];
    }
}

mysqli_close($conn);

function sort_link($field, $display_name, $current_sort_item, $current_sort_order, $search, $record_type, $date_from, $date_to) {
    $next_sort_order = ($current_sort_item == $field && $current_sort_order == 'ASC') ? 'DESC' : 'ASC';

    if ($current_sort_item != $field) {
        $next_sort_order = ($field == 'trash_date') ? 'DESC' : 'ASC';
    }

    $icon = '';
    $icon_class = 'text-light';

    if ($current_sort_item == $field) {
        if ($field == 'id' || $field == 'trash_date') {
            $icon = '<i class="bi bi-sort-numeric-' . ($current_sort_order == 'ASC' ? 'down' : 'up') . '"></i>';
        } else {
            $icon = '<i class="bi bi-sort-alpha-' . ($current_sort_order == 'ASC' ? 'down' : 'up') . '"></i>';
        }
    } else {
        $icon_class = 'sort-text'; 
        if ($field == 'id' || $field == 'trash_date') {
            $icon = '<i class="bi bi-sort-numeric-down-alt"></i>'; 
        } else {
            $icon = '<i class="bi bi-sort-alpha-down"></i>'; 
        }
    }

    // Build the URL parameters 
    $url = "?type=$record_type&title=$field&sort=$next_sort_order";
    if (!empty($search)) $url .= "&search=" . urlencode($search);
    if ($date_from !== null) $url .= "&date_from=" . urlencode($date_from);
    if ($date_to !== null) $url .= "&date_to=" . urlencode($date_to);

    return '<a href="' . $url . '" class="'.$icon_class.' text-decoration-none d-flex align-items-center justify-content-center">' . $display_name . '&nbsp;' . $icon . '</a>';
}
?>

<!DOCTYPE html>

<html lang="en">
<!-- Description: Recycle Bin -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 30/11/2025 -->
<!-- Validated: OK 6/12/2025 -->

<head>
    <title>Recycle Bin | Root Flower</title>
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
        <h1 class="py-3">Recycle Bin</h1>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex fs-6">
                <ul class="nav nav-pills" id="status-tabs">
                    <li class="nav-item">
                        <a class="nav-link <?= $record_type == 'users' ? 'active' : '' ?> me-2 py-1 px-3 text-light" href="?type=users">Deleted Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $record_type == 'workshops' ? 'active' : '' ?> me-2 py-1 px-3 text-light" href="?type=workshops">Deleted Workshops</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $record_type == 'studentworks' ? 'active' : '' ?> me-3 py-1 px-3 text-light" href="?type=studentworks">Deleted Student Works</a>
                    </li>
                </ul>
                
                <a href="recycle.php?type=<?php echo $record_type; ?>" class="me-3 btn py-1 px-2 bg-white bg-opacity-75" id="refresh"><i class="bi bi-arrow-clockwise"></i></a>
            </div>

            <form method="GET" class="d-flex">
                <div class="input-group">
                    <input type="search" class="form-control" placeholder="Search..." name="search" value="<?= htmlspecialchars($search); ?>">
                    <button class="btn btn-primary py-0 px-2" type="submit"><i class="bi bi-search"></i></button>
                </div>
                
                <input type="hidden" name="type" value="<?= htmlspecialchars($record_type); ?>">
                <input type="hidden" name="title" value="<?= htmlspecialchars($sort_item); ?>">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort_order); ?>">
            </form>
        </div>

        <div class="mb-2 pt-2 pb-3 px-3 border rounded bg-secondary bg-opacity-25">
            <form method="GET" class="d-flex gap-4 justify-content-start filter align-items-end">
                <input type="hidden" name="type" value="<?= htmlspecialchars($record_type); ?>">
                <input type="hidden" name="search" value="<?= htmlspecialchars($search); ?>">
                <input type="hidden" name="title" value="<?= htmlspecialchars($sort_item); ?>">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort_order); ?>">

                <div>
                    <label class="form-label fs-6 fw-bold mb-1">Date Deleted</label>
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

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . $base_url; ?>" id="recycleForm">
            <input type="hidden" name="all_selected_ids" id="allSelectedIds" value="">
            <div class="table-responsive">
                <table class="table table-light table-striped table-hover table-bordered table-sm m-0">
                    <thead class="text-center">
                        <tr>
                            <th class="fs-6 text-center text-light">
                                <input type="checkbox" id="selectAllCheckbox" class="d-none">
                                <i class="bi bi-square select-all-icon" id="selectAllIcon" role="button" title="Select/Deselect All"></i>
                            </th>
                            <th class="text-light"><?= sort_link('id', 'ID', $sort_item, $sort_order, $search, $record_type, $date_from, $date_to) ?></th>
                            
                            <?php if ($record_type == 'users'): ?>
                                <th class="text-light"><?= sort_link('email', 'Email', $sort_item, $sort_order, $search, $record_type, $date_from, $date_to) ?></th>
                                <th class="text-light"><?= sort_link('first_name', 'Name', $sort_item, $sort_order, $search, $record_type, $date_from, $date_to) ?></th>
                            <?php elseif ($record_type == 'workshops'): ?>
                                <th class="text-light"><?= sort_link('workshop_title', 'Title', $sort_item, $sort_order, $search, $record_type, $date_from, $date_to) ?></th>
                                <th class="text-light"><?= sort_link('first_name', 'Name', $sort_item, $sort_order, $search, $record_type, $date_from, $date_to) ?></th>
                                <th class="text-light">Original Status</th>
                            <?php elseif ($record_type == 'studentworks'): ?>
                                <th class="text-light"><?= sort_link('first_name', 'Artist Name', $sort_item, $sort_order, $search, $record_type, $date_from, $date_to) ?></th>
                                <th class="text-light">Caption</th>
                                <th class="text-light"><?= sort_link('workshop_title', 'Workshop Title', $sort_item, $sort_order, $search, $record_type, $date_from, $date_to) ?></th>
                                <th class="text-light"><?= sort_link('likes', 'Likes', $sort_item, $sort_order, $search, $record_type, $date_from, $date_to) ?></th>
                            <?php endif; ?>
                            
                            <th class="text-light"><?= sort_link('trash_date', 'Date Deleted', $sort_item, $sort_order, $search, $record_type, $date_from, $date_to) ?></th>
                            <th class="text-light">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $delete_modals_html = [];
                        if ($displayed_rows > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="text-center"><input type="checkbox" name="selected_ids[]" value="<?= $row['id']; ?>"></td>
                                    <td><?= $row['id']; ?></td>
                                    
                                    <?php if ($record_type == 'users'): ?>
                                        <td><?= $row['email']; ?></td>
                                        <td><?= $row['first_name']; ?> <?= $row['last_name']; ?></td>
                                    <?php elseif ($record_type == 'workshops'): ?>
                                        <td><?= $row['workshop_title']; ?></td>
                                        <td><?= $row['first_name']; ?> <?= $row['last_name']; ?></td>
                                        <td class="text-center">
                                            <span class="badge <?php echo ($row['approve_status'] == 'approved') ? 'bg-success' : 'bg-danger'; ?>"><?= $row['approve_status']; ?></span>
                                        </td>
                                    <?php elseif ($record_type == 'studentworks'): ?>
                                        <td><?= $row['first_name']; ?> <?= $row['last_name']; ?></td>
                                        <td><?= substr($row['caption'], 0, 50); ?>...</td>
                                        <td><?= $row['workshop_title']; ?></td>
                                        <td class="text-center"><?= $row['likes']; ?></td>
                                    <?php endif; ?>
                                    
                                    <td><?= $row['trash_date']; ?></td>
                                    <td class="text-center">
                                        <button type="submit" name="action_restore" value="<?= $row['id']; ?>" class="btn text-primary fs-5 p-0 border-0 align-baseline">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>&nbsp;
                                        
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#deleteSingleModal_<?= $row['id']; ?>" class="btn text-danger fs-5 p-0 border-0 align-baseline">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                        <?php $delete_modals_html[] = delete_modal($row['id'], $base_url); ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan='8' class="ps-3">No deleted items found for this category</td>
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
                    <?php if ($page > 1): ?> <li class="page-item"><a href="<?php echo $link_base_url; ?>&page=<?php echo $page - 1; ?>" class="page-link py-1">Previous</a></li> 
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
                                echo "<li class='page-item'><a href='$page_url' class='page-link py-1 active'>$i</a></li>"; 
                            } else {
                                echo "<li class='page-item'><a href='$page_url' class='page-link py-1'>$i</a></li>"; 
                            }
                        } 
                    ?>
                    <?php if ($page < $total_pages): ?> <li class="page-item"><a href="<?php echo $link_base_url; ?>&page=<?php echo $page + 1; ?>" class="page-link py-1">Next</a></li>
                    <?php endif; ?> 
                    </ul>
                </nav>

                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" name="action_bulk_restore" class="btn btn-success btn-sm text-light border-success px-3">
                        <i class="bi bi-arrow-counterclockwise"></i> Restore Selected
                    </button>
                    
                    <button type="button" class="btn btn-sm btn-danger text-light border-danger px-3" data-bs-toggle="modal" data-bs-target="#deletePermanentModal">
                        <i class="bi bi-trash3-fill"></i> Delete Selected Permanently
                    </button>
                </div>
            </div>
        </form>

        <?php 
            if (!empty($delete_modals_html)) {
                echo implode('', $delete_modals_html);
            }
        ?>

        <!-- Bulk Permanent Delete modal -->
        <div class="modal fade" id="deletePermanentModal" tabindex="-1" aria-labelledby="deletePermanentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mx-auto">
                <div class="modal-content p-2">
                    <div class="modal-header">
                        <h2 class="modal-title" id="deletePermanentModalLabel">Delete Confirmation</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body text-secondary">
                        <p class="lh-1">Are you absolutely sure you want to permanently delete this?</p>
                        <p class="text-danger lh-1">This action cannot be undone. The item will be lost forever.</p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                        
                        <button type="submit" name="action_bulk_delete_perm" class="btn btn-danger text-light border-0" form="recycleForm">
                            Permanently Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </article>

    <footer class="mt-auto text-dark ps-4 ps-lg-5 py-2 fs-6">
        <p>&copy; 2025 Root Flower</p>
    </footer>

<script>
    // Injects the complete list of IDs currently matched by the filters.
    window.allSelectableIds = <?php echo json_encode($all_filtered_ids); ?>;
</script>
<script src="js/main.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
</body>
</html>