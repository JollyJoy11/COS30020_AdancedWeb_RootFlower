<?php 
include "include/session.php"; 
include "include/db_connect.php";

if (isset($_SESSION['user']) && $_SESSION['role'] !== 'admin'){
	header('Location:main_menu.php');
	exit;
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

// Basic sql
$sql_base = "SELECT r.*, u.first_name, u.last_name FROM ar_table r JOIN user_table u ON r.email = u.email";
$sql_total = "SELECT COUNT(*) FROM ar_table r JOIN user_table u ON r.email = u.email";

// Search sql
if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($conn, $search); 
    $sql_search = " AND (u.first_name LIKE '%$search_escaped%' OR u.last_name LIKE '%$search_escaped%' OR r.email LIKE '%$search_escaped%' OR r.flowers LIKE '%$search_escaped%')";
    $sql_base .= $sql_search;
    $sql_total .= $sql_search;
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

mysqli_close($conn);

function sort_link($field, $display_name, $current_sort_item, $current_sort_order, $search) {
    // Determine the next sort order
    $next_sort_order = ($current_sort_item == $field && $current_sort_order == 'ASC') ? 'DESC' : 'ASC';
    if ($current_sort_item != $field) {
        // Set default ASC/DESC for a new column
        $next_sort_order = ($field == 'upload_time') ? 'DESC' : 'ASC';
    }

    // Determine the icon to display
    $icon = '';
    $icon_class = 'text-light';

    if ($current_sort_item == $field) {
        if ($field == 'id' || $field == 'upload_time') {
             $icon = '<i class="bi bi-sort-numeric-' . ($current_sort_order == 'ASC' ? 'down' : 'up') . '"></i>';
        } else {
             $icon = '<i class="bi bi-sort-alpha-' . ($current_sort_order == 'ASC' ? 'down' : 'up') . '"></i>';
        }
    } else {
        $icon_class = 'sort-text'; 
        if ($field == 'id' || $field == 'upload_time') {
             $icon = '<i class="bi bi-sort-numeric-down-alt"></i>'; 
        } else {
             $icon = '<i class="bi bi-sort-alpha-down"></i>'; 
        }
    }

    // Build the URL parameters
    $url = "?title=$field&sort=$next_sort_order";
    if (!empty($search)) $url .= "&search=" . urlencode($search);

    return '<a href="' . $url . '" class="'.$icon_class.' text-decoration-none d-flex align-items-center justify-content-center">' . $display_name . '&nbsp;' . $icon . '</a>';
}
?>
<!DOCTYPE html>

<html lang="en">
<!-- Description: Manage AR Creations -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 2/12/2025 -->
<!-- Validated: OK 6/12/2025 -->

<head>
    <title>AR Creations History | Root Flower</title>
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
		<h1 class="py-3">AR Creations History</h1>

        <div class="d-flex justify-content-end align-items-center mb-3">
            <a href="manage_ar.php" class="me-3 btn py-0 px-2 bg-white bg-opacity-75" id="refresh"><i class="bi bi-arrow-clockwise"></i></a>
            <form method="GET" class="d-flex">
                <div class="input-group">
                    <input type="search" class="form-control" placeholder="Search..." name="search" value="<?= htmlspecialchars($search); ?>">
                    <button class="btn btn-primary py-0 px-2" type="submit"><i class="bi bi-search"></i></button>
                </div>
                
                <input type="hidden" name="title" value="<?= htmlspecialchars($sort_item); ?>">
                <input type="hidden"    name="sort" value="<?= htmlspecialchars($sort_order); ?>">
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-light table-striped table-hover table-bordered table-sm m-0">
                <thead class="text-center">
                    <tr>
                        <th class="text-light"><?= sort_link('id', 'ID', $sort_item, $sort_order, $search) ?></th>
                        <th class="text-light"><?= sort_link('email', 'Email', $sort_item, $sort_order, $search) ?></th>
                        <th class="text-light"><?= sort_link('first_name', 'Name', $sort_item, $sort_order, $search) ?></th>
                        <th  class="text-light">Flowers</th>
                        <th class="text-light"><?= sort_link('image', 'Creation', $sort_item, $sort_order, $search) ?></th>
                        <th class="text-light"><?= sort_link('upload_time', 'Created At', $sort_item, $sort_order, $search) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($displayed_rows > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $row['id']; ?></td>
                                <td><?= $row['email']; ?></td>
                                <td><?= $row['first_name']; ?> <?= $row['last_name']; ?></td>
                                <td><?= $row['flowers']; ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#imageModal<?= $row['id']; ?>">
                                        <i class="bi bi-image me-2"></i> View Creation
                                    </button>

                                    <div class="modal fade" id="imageModal<?= $row['id']; ?>" tabindex="-1" aria-labelledby="imageModal<?= $row['id']; ?>Label" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h2 class="modal-title" id="imageModalLabel">Image Preview</h2>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <img id="modalImage" src="<?= htmlspecialchars($row['image']); ?>" class="img-fluid" alt="Preview">
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="<?= $row['image']; ?>" class="btn btn-primary" download>
                                                        <i class="bi bi-download me-2"></i>Download
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= $row['upload_time']; ?></td>
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
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>
</html>