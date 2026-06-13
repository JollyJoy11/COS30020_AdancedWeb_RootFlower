<?php 
ob_start();
include "include/session.php"; 
include "include/db_connect.php";
include "include/workshops_info.php";

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
$default_sort = ($sort_item == 'submit_time') ? 'DESC' : 'ASC';
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : $default_sort;
$new_sort = ($sort_order=='ASC') ? 'DESC' : 'ASC';

$items_per_page = 5; //Number display per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Workshop Batches Filter
$workshop_batches = [
    '2025' => [
        'August 2025',
        'September 2025',
        'October 2025',
        'November 2025',
        'December 2025',
    ],
    '2026' => [
        'January 2026'
    ]
];

$month_filter = isset($_GET['month_filter']) ? sanitise_input($_GET['month_filter']) : '';

// Search & Workshop title filter
$search = isset($_GET['search']) ? sanitise_input($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'pending';
$workshop_filter = isset($_GET['workshop_title']) ? sanitise_input($_GET['workshop_title']) : '';

// Dates range filter
$date_from = isset($_GET['date_from']) && $_GET['date_from'] !== '' ? $_GET['date_from'] : null;
$date_to = isset($_GET['date_to']) && $_GET['date_to'] !== '' ? $_GET['date_to'] : null;

// Basic sql
$sql_base = "SELECT * FROM workshop_table WHERE trash='no'";
$sql_total = "SELECT COUNT(*) FROM workshop_table WHERE trash='no'";

// Status Filter Logic
if ($status_filter == 'edited') {
    $sql_status = " AND edit_status = 'pending'";
} else {
    $sql_status = " AND approve_status = '$status_filter' AND edit_status = 'none'";
}

$sql_base .= $sql_status;
$sql_total .= $sql_status;

// Search sql
if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($conn, $search); 
    $sql_search = " AND (first_name LIKE '%$search_escaped%' OR last_name LIKE '%$search_escaped%' OR email LIKE '%$search_escaped%' OR workshop_title LIKE '%$search_escaped%' OR contact_number LIKE '%$search_escaped%')";
    $sql_base .= $sql_search;
    $sql_total .= $sql_search;
}

// Workshop title sql
if (!empty($workshop_filter) && $workshop_filter != 'all') {
    $workshop_title_escaped = mysqli_real_escape_string($conn, $workshop_filter);
    $sql_workshop_filter = " AND workshop_title = '$workshop_title_escaped'";
    $sql_base .= $sql_workshop_filter;
    $sql_total .= $sql_workshop_filter;
}

// Batch sql
if (!empty($month_filter)) {
    $date_object = DateTime::createFromFormat('F Y', $month_filter);

    if ($date_object) {
        $db_month_format = $date_object->format('Y-m'); 
        $month_filter_escaped = mysqli_real_escape_string($conn, $db_month_format);

        $sql_batch_filter = " AND date LIKE '%$month_filter_escaped%'"; 
        
        $sql_base .= $sql_batch_filter;
        $sql_total .= $sql_batch_filter;
    }
}

// Date range filter
if ($date_from !== null) {
    $date_from_escaped = mysqli_real_escape_string($conn, $date_from);
    $sql_date_from = " AND DATE(submit_time) >= '$date_from_escaped'";
    $sql_base .= $sql_date_from;
    $sql_total .= $sql_date_from;
}

if ($date_to !== null) {
    $date_to_escaped = mysqli_real_escape_string($conn, $date_to);
    $sql_date_to = " AND DATE(submit_time) <= '$date_to_escaped'";
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

if (!empty($month_filter)) { 
    $base_url .= "&month=" . urlencode($month_filter);
    $link_base_url .= "&month=" . urlencode($month_filter);
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
        $result = mysqli_query($conn, "UPDATE workshop_table SET approve_status='approved' WHERE id=$id");

        if ($result){
            $email_query = mysqli_query($conn, "SELECT email, first_name, workshop_title, date, time, no_of_seats FROM workshop_table WHERE id=$id");
            $row = mysqli_fetch_assoc($email_query);

            $firstName = $row['first_name'];
            $workshopTitle = $row['workshop_title'];
            $date = $row['date'];
            $time = $row['time'];
            $seat = $row['no_of_seats'];

            $to = $row['email'];
            $subject = "Workshop Registration Confirmed - $workshopTitle";
            $message = "Dear $firstName,\n\nWe are pleased to inform you that your registration for the $workshopTitle workshop has been APPROVED.\n\nHere are your confirmed details:\nWorkshop Title: $workshopTitle\nDate: $date\nTime: $time\nSeat(s) Reserved: $seat\n\nPlease check your student dashboard for any pre-workshop materials or updates, including the final venue details.\n\nWe look forward to seeing you there!\n\nSincerely,\nRoot Flower Team";
            sendEmail($to, $subject, $message);

            generateUserNotification($conn, '', $row['email'], 'workshop_app', $id, 'workshop_table');
        }
        header("Location: manage_workshop_reg.php" . $link_base_url);
        exit;
    }

    // Delete single
    else if (isset($_POST['action_delete'])) {
        $id = (int)$_POST['action_delete'];
        mysqli_query($conn, "UPDATE workshop_table SET trash='yes', trash_date = NOW() WHERE id=$id");

        $alert['success'] = "Record deleted successfully.";
        $_SESSION['alert'] = $alert;
        header("Location: manage_workshop_reg.php" . $link_base_url);
        exit;
    }

    // Approve Seat Edit
    else if (isset($_POST['action_approve_edit'])) {
        $id = (int)$_POST['action_approve_edit'];
        $result = mysqli_query($conn, "UPDATE workshop_table SET no_of_seats = pending_seats, approve_status='approved', pending_seats = NULL, edit_status = 'none' WHERE id=$id");

        if ($result){
            $email_query = mysqli_query($conn, "SELECT email, first_name, workshop_title, date, time, no_of_seats FROM workshop_table WHERE id=$id");
            $row = mysqli_fetch_assoc($email_query);

            $firstName = $row['first_name'];
            $workshopTitle = $row['workshop_title'];
            $date = $row['date'];
            $time = $row['time'];
            $seat = $row['no_of_seats'];

            $to = $row['email'];
            $subject = "Seat Count Update: Approved for $workshopTitle Workshop";
            $message = "Dear $firstName,\n\nThis is to confirm that your request to modify the number of seats for the $workshopTitle Workshop has been approved.\n\nUpdated Details:\nNew Seat Count: $seats\n\nYour registration details on the system have been updated to reflect $seats.\n\nIf you have any questions, please reply to this email.\n\nSincerely,\nRoot Flower Team";
            sendEmail($to, $subject, $message);

            generateUserNotification($conn, '', $row['email'], 'seat_app', $id, 'workshop_table');
        }
        header("Location: manage_workshop_reg.php" . $link_base_url);
        exit;
    }

    // Bulk reject
    else if (isset($_POST['action_bulk_reject'])) {
        if (isset($_POST['all_selected_ids']) && $_POST['all_selected_ids'] !== '') {
            $ids_string = $_POST['all_selected_ids'];

            $ids = array_map('intval', explode(",", $ids_string));
            $valid_ids = array_filter($ids, function($id) {
                return $id > 0;
            });

            $generic_reason = "Your registration was rejected during a bulk review process. Please contact support for more details."; 
            $reason = mysqli_real_escape_string($conn, $generic_reason);

            if (!empty($valid_ids)) {
                $ids_sql = implode(",", $valid_ids);
                $result = mysqli_query($conn, "UPDATE workshop_table SET approve_status='rejected' WHERE id IN ($ids_sql)");

                if ($result) {
                    $email_query = mysqli_query($conn, "SELECT email, first_name, workshop_title, date, time, no_of_seats FROM workshop_table WHERE id IN ($ids_sql)"); 

                    if ($email_query) {
                        while ($row = mysqli_fetch_assoc($email_query)) {
                            $firstName = $row['first_name'];
                            $workshopTitle = $row['workshop_title'];
                            $date = $row['date'];
                            $time = $row['time'];
                            $seat = $row['no_of_seats'];

                            $to = $row['email'];
                            $subject = "Important: Your Registration for $workshopTitle Workshop has been Rejected";
                            $message = "Dear $firstName,\n\nThank you for your interest in the $workshopTitle Workshop. We regret to inform you that your registration has been rejected by the administrator.\n\nReason for Rejection:\n$reason\n\nIf you believe this is an error or need further clarification, please contact our support team immediately.\n\nSincerely,\nRoot Flower Team";
                            sendEmail($to, $subject, $message);

                            generateUserNotification($conn, '', $row['email'], 'workshop_rej', $id, 'workshop_table');
                        }

                        mysqli_free_result($email_query);
                    } 
                } 
            }
        }
        header("Location: manage_workshop_reg.php" . $link_base_url);
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
                mysqli_query($conn, "UPDATE workshop_table SET trash='yes', trash_date = NOW() WHERE id IN ($ids_sql)");

                $alert['success'] = "Records deleted successfully.";
                $_SESSION['alert'] = $alert;
            }
        }
        header("Location: manage_workshop_reg.php" . $link_base_url);
        exit;
    }

    // Bulk Approve Seat Edit
    else if (isset($_POST['action_bulk_approve_edit'])) {
        if (isset($_POST['all_selected_ids']) && $_POST['all_selected_ids'] !== '') {
            $ids_string = $_POST['all_selected_ids'];
            
            $ids = array_map('intval', explode(",", $ids_string));
            
            $valid_ids = array_filter($ids, function($id) {
                return $id > 0;
            });

            if (!empty($valid_ids)) {
                $ids_sql = implode(",", $valid_ids);
                $result = mysqli_query($conn, "UPDATE workshop_table SET no_of_seats = pending_seats, approve_status = 'approved', pending_seats = NULL, edit_status = 'none' WHERE id IN ($ids_sql)");

                if ($result) {
                    $email_query = mysqli_query($conn, "SELECT email, first_name, workshop_title, date, time, no_of_seats FROM workshop_table WHERE id IN ($ids_sql)"); 

                    if ($email_query) {
                        while ($row = mysqli_fetch_assoc($email_query)) {
                            $firstName = $row['first_name'];
                            $workshopTitle = $row['workshop_title'];
                            $date = $row['date'];
                            $time = $row['time'];
                            $seat = $row['no_of_seats'];

                            $to = $row['email'];
                            $subject = "Seat Count Update: Approved for $workshopTitle Workshop";
                            $message = "Dear $firstName,\n\nThis is to confirm that your request to modify the number of seats for the $workshopTitle Workshop has been approved.\n\nUpdated Details:\nNew Seat Count: $seats\n\nYour registration details on the system have been updated to reflect $seats.\n\nIf you have any questions, please reply to this email.\n\nSincerely,\nRoot Flower Team";
                            sendEmail($to, $subject, $message);

                            generateUserNotification($conn, '', $row['email'], 'seat_app', $id, 'workshop_table');
                        }

                        mysqli_free_result($email_query);
                    } 
                } 
            }
        }
        header("Location: manage_workshop_reg.php" . $link_base_url);
        exit;
    }

    // Bulk Reject Seat Edit
    else if (isset($_POST['action_bulk_reject_edit'])) {
        if (isset($_POST['all_selected_ids']) && $_POST['all_selected_ids'] !== '') {
            $ids_string = $_POST['all_selected_ids'];
            
            $ids = array_map('intval', explode(",", $ids_string));
            
            $valid_ids = array_filter($ids, function($id) {
                return $id > 0;
            });

            $generic_reason = "Your request to change number of seats was rejected during a bulk review process. Please contact support for assistance."; 
            $reason_escaped = mysqli_real_escape_string($conn, $generic_reason);

            if (!empty($valid_ids)) {
                $ids_sql = implode(",", $valid_ids);
                $result = mysqli_query($conn, "UPDATE workshop_table SET pending_seats = NULL, edit_status = 'none' WHERE id IN ($ids_sql)");

                if ($result) {
                    $email_query = mysqli_query($conn, "SELECT email, first_name, workshop_title, date, time, no_of_seats FROM workshop_table WHERE id IN ($ids_sql)"); 

                    if ($email_query) {
                        while ($row = mysqli_fetch_assoc($email_query)) {
                            $firstName = $row['first_name'];
                            $workshopTitle = $row['workshop_title'];
                            $date = $row['date'];
                            $time = $row['time'];
                            $seat = $row['no_of_seats'];

                            $to = $row['email'];
                            $subject = "Important: Your Seat Change Request for $workshopTitle Workshop has been Rejected";
                            $message = "Dear $firstName,\n\nWe have reviewed your request to change the number of seats for the $workshopTitle Workshop and regret to inform you that the request has been rejected.\n\nReason for Rejection:\n$reason\n\nThe original seat count of $seat remains active. If you would like to submit a new request, please do so via your dashboard.\n\nSincerely,\nRoot Flower Team";
                            sendEmail($to, $subject, $message);

                            generateUserNotification($conn, '', $row['email'], 'seat_rej', $id, 'workshop_table');
                        }

                        mysqli_free_result($email_query);
                    } 
                } 
            }
        }
        header("Location: manage_workshop_reg.php" . $link_base_url);
        exit;
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
                $result = mysqli_query($conn, "UPDATE workshop_table SET approve_status = 'approved' WHERE id IN ($ids_sql)");

                if ($result) {
                    $email_query = mysqli_query($conn, "SELECT email, first_name, workshop_title, date, time, no_of_seats FROM workshop_table WHERE id IN ($ids_sql)"); 

                    if ($email_query) {
                        while ($row = mysqli_fetch_assoc($email_query)) {
                            $firstName = $row['first_name'];
                            $workshopTitle = $row['workshop_title'];
                            $date = $row['date'];
                            $time = $row['time'];
                            $seat = $row['no_of_seats'];

                            $to = $row['email'];
                            $subject = "Workshop Registration Confirmed - $workshopTitle";
                            $message = "Dear $firstName,\n\nWe are pleased to inform you that your registration for the $workshopTitle workshop has been APPROVED.\n\nHere are your confirmed details:\nWorkshop Title: $workshopTitle\nDate: $date\nTime: $time\nSeat(s) Reserved: $seat\n\nPlease check your student dashboard for any pre-workshop materials or updates, including the final venue details.\n\nWe look forward to seeing you there!\n\nSincerely,\nRoot Flower Team";
                            sendEmail($to, $subject, $message);

                            generateUserNotification($conn, '', $row['email'], 'workshop_app', $id, 'workshop_table');
                        }

                        mysqli_free_result($email_query);
                    } 
                } 
            }
        }
        header("Location: manage_workshop_reg.php" . $link_base_url);
        exit;
    }

    // Normal reject or seat edit reject
    else if (isset($_POST['action_reject_modal']) && isset($_POST['action_type_modal']) && isset($_POST['rejection_reason']) && isset($_POST['conf_reject'])) {
        $id = (int)$_POST['action_reject_modal'];
        $action_type = $_POST['action_type_modal'];
        $reason = mysqli_real_escape_string($conn, $_POST['rejection_reason']);
        
        if ($action_type == 'normal_reject') {
            // Normal Reject
            $result = mysqli_query($conn, "UPDATE workshop_table SET approve_status='rejected' WHERE id=$id");
            
            if ($result){
                $email_query = mysqli_query($conn, "SELECT email, first_name, workshop_title, date, time, no_of_seats FROM workshop_table WHERE id=$id");
                $row = mysqli_fetch_assoc($email_query);

                $firstName = $row['first_name'];
                $workshopTitle = $row['workshop_title'];
                $date = $row['date'];
                $time = $row['time'];
                $seat = $row['no_of_seats'];

                $to = $row['email'];
                $subject = "Important: Your Registration for $workshopTitle Workshop has been Rejected";
                $message = "Dear $firstName,\n\nThank you for your interest in the $workshopTitle Workshop. We regret to inform you that your registration has been rejected by the administrator.\n\nReason for Rejection:\n$reason\n\nIf you believe this is an error or need further clarification, please contact our support team immediately.\n\nSincerely,\nRoot Flower Team";
                sendEmail($to, $subject, $message);

                generateUserNotification($conn, '', $row['email'], 'workshop_rej', $id, 'workshop_table');
            }
            
        } else if ($action_type == 'edit_reject') {
            // Reject Seat Edit
            $result = mysqli_query($conn, "UPDATE workshop_table SET pending_seats = NULL, edit_status = 'none' WHERE id=$id");
            
            if ($result){
                $email_query = mysqli_query($conn, "SELECT email, first_name, workshop_title, date, time, no_of_seats FROM workshop_table WHERE id=$id");
                $row = mysqli_fetch_assoc($email_query);

                $firstName = $row['first_name'];
                $workshopTitle = $row['workshop_title'];
                $date = $row['date'];
                $time = $row['time'];
                $seat = $row['no_of_seats'];

                $to = $row['email'];
                $subject = "Important: Your Seat Change Request for $workshopTitle Workshop has been Rejected";
                $message = "Dear $firstName,\n\nWe have reviewed your request to change the number of seats for the $workshopTitle Workshop and regret to inform you that the request has been rejected.\n\nReason for Rejection:\n$reason\n\nThe original seat count of $seat remains active. If you would like to submit a new request, please do so via your dashboard.\n\nSincerely,\nRoot Flower Team";
                sendEmail($to, $subject, $message);

                generateUserNotification($conn, '', $row['email'], 'seat_rej', $id, 'workshop_table');
            }
        }

        header("Location: manage_workshop_reg.php" . $link_base_url);
        exit;
    }

    // Edit workshop
    else if (isset($_POST['action_edit_workshop'])) {
        $edit_id = $_POST['edit_id']; 
        $seats_new = $_POST['seats_new'];
        $edit_email = $_POST['edit_email'];
        $edit_title = $_POST['edit_title'];
        $dates = NULL;
        $times = NULL;

        // Basic validation
        if (!is_numeric($seats_new) || $seats_new <= 0) {
            $alert['danger'] = "Please insert the number of seats.";
            $_SESSION['alert'] = $alert;
            header("Location: manage_workshop_reg.php" . $base_url);
            exit;
        }

        if (isset($_POST['session_id'])){
            $parts = array_map('trim', explode(',', $_POST['session_id']));

            if (count($parts) == 2){ // Hobby class
                $dates = $parts[0];
                $times = $parts[1];
            } else { // Handtied Bouquet
                $dates = $parts[0] . ', ' . $parts[1]; 
                $times = $parts[2] . ', ' . $parts[3];
            }
        } else {
            $new_dates = [];
            $new_times = [];
            
            for ($i = 1; $i <= 4; $i++) {
                $new_date = $_POST['edit_date_day' . $i] ?? null;
                $new_time = $_POST['edit_time_day' . $i] ?? null;

                $new_dates[] = $new_date; 
                $new_times[] = $new_time;
            }

            $dates = implode(", ", $new_dates);
            $times = implode(", ", $new_times);
        }
        
        $duplicate_query = "SELECT id, date, time FROM workshop_table WHERE email = '$edit_email' AND workshop_title = '$edit_title' AND trash = 'no' AND (approve_status = 'approved' OR approve_status = 'pending')";
        $duplicate_result = mysqli_query($conn, $duplicate_query);

        if (mysqli_num_rows($duplicate_result) > 0) {
            while ($row = mysqli_fetch_array($duplicate_result)){
                $savedDate = $row['date'];
                $savedTime = $row['time'];

                if (strpos($edit_title, 'Florist To Be') === false) {
                    if ($savedDate == $dates && $savedTime === $times){
                        if ($row['id'] != $edit_id) {
                            $alert['danger'] = "The person has already registered this workshop.";
                            $_SESSION['alert'] = $alert;
                            header("Location: manage_workshop_reg.php" . $base_url);
                            exit;
                        }
                    }
                } else {
                    $firstDate = explode(", ", $savedDate)[0];
                    $savedMonth = date("F Y", strtotime($firstDate));

                    $submittedDate = explode(", ", $dates)[0];
                    $submittedBatch = date("F Y", strtotime($submittedDate));

                    if ($savedMonth === $submittedBatch){
                        if ($row['id'] != $edit_id) {
                            $alert['danger'] = "The person has already registered this workshop.";
                            $_SESSION['alert'] = $alert;
                            header("Location: manage_workshop_reg.php" . $base_url);
                            exit;
                        }
                    }
                } 
            }
        }

        $update_query = "UPDATE workshop_table SET no_of_seats = $seats_new, date = '$dates', time = '$times' WHERE id = $edit_id";
        if (mysqli_query($conn, $update_query)){
            $alert['success'] = "The workshop has been updated successfully.";
            $_SESSION['alert'] = $alert;

            $to = $edit_email;
            $subject = "Your Workshop Registration Details Have Been Updated";
            $message = "Dear Participant,\n\nYour registration for the $edit_title Workshop has been updated.\n\nHere are your updated workshop details:\n\nDate: $dates\nTime: $times\nNumber of Seats: $seats_new\n\nIf you have any inquiries, please contact our support team immediately. We look forward to seeing you at the workshop!\n\nSincerely,\nRoot Flower Team";
            sendEmail($to, $subject, $message);

            generateUserNotification($conn, '', $edit_email, 'workshop_update', $edit_id, 'workshop_table');

            header("Location: manage_workshop_reg.php" . $base_url);
            exit;
        }
    }
}

// For select all records of the current sorting and filter
$sql_all_ids = str_replace('SELECT *', 'SELECT id', $sql_base);
$result_all_ids = mysqli_query($conn, $sql_all_ids);
$all_filtered_ids = [];

if ($result_all_ids) {
    while ($row = mysqli_fetch_assoc($result_all_ids)) {
        $all_filtered_ids[] = $row['id'];
    }
}

function sort_link($field, $display_name, $current_sort_item, $current_sort_order, $search, $status_filter, $workshop_filter, $date_from, $date_to, $month_filter) {
    // Determine the next sort order
    $next_sort_order = ($current_sort_item == $field && $current_sort_order == 'ASC') ? 'DESC' : 'ASC';
    if ($current_sort_item != $field) {
        // Set default ASC/DESC for a new column
        $next_sort_order = ($field == 'submit_time') ? 'DESC' : 'ASC';
    }

    // Determine the icon to display
    $icon = '';
    $icon_class = 'text-light';

    if ($current_sort_item == $field) {
        if ($field == 'id' || $field == 'contact_number' || $field == 'submit_time' || $field == 'no_of_seats') {
             $icon = '<i class="bi bi-sort-numeric-' . ($current_sort_order == 'ASC' ? 'down' : 'up') . '"></i>';
        } else {
             $icon = '<i class="bi bi-sort-alpha-' . ($current_sort_order == 'ASC' ? 'down' : 'up') . '"></i>';
        }
    } else {
        $icon_class = 'sort-text'; 
        if ($field == 'id' || $field == 'contact_number' || $field == 'submit_time' || $field == 'no_of_seats') {
             $icon = '<i class="bi bi-sort-numeric-down-alt"></i>'; 
        } else {
             $icon = '<i class="bi bi-sort-alpha-down"></i>'; 
        }
    }

    // Build the URL parameters
    $url = "?title=$field&sort=$next_sort_order&status=" . urlencode($status_filter);
    if (!empty($search)) $url .= "&search=" . urlencode($search);
    if (!empty($workshop_filter)) $url .= "&workshop_title=" . urlencode($workshop_filter);
    if (!empty($month_filter)) $url .= "&month=" . urlencode($month_filter);
    if ($date_from !== null) $url .= "&date_from=" . urlencode($date_from);
    if ($date_to !== null) $url .= "&date_to=" . urlencode($date_to);

    return '<a href="' . $url . '" class="'.$icon_class.' text-decoration-none d-flex align-items-center justify-content-center">' . $display_name . '&nbsp;' . $icon . '</a>';
}
?>
<!DOCTYPE html>

<html lang="en">
<!-- Description: Manage Workshop Registration -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 20/11/2025 -->
<!-- Validated: OK 6/12/2025 -->

<head>
    <title>Manage Workshop Registration | Root Flower</title>
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
		<h1 class="py-3">Manage Workshop Registration</h1>

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
                        <a class="nav-link <?= $status_filter == 'rejected' ? 'active' : '' ?> me-2 py-1 px-3 text-light" href="?status=rejected">Rejected</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $status_filter == 'edited' ? 'active' : '' ?> me-3 py-1 px-3 text-light" href="?status=edited">Edited</a>
                    </li>
                </ul>

                <button type="button" class="me-3 btn py-1 px-2 bg-white bg-opacity-75" data-bs-toggle="modal" data-bs-target="#addWorkshopReg">Add Workshop Registration <i class="bi bi-patch-plus"></i></button>
                
                <a href="manage_workshop_reg.php?status=<?php echo $status_filter; ?>" class="me-3 btn py-1 px-2 bg-white bg-opacity-75" id="refresh"><i class="bi bi-arrow-clockwise"></i></a>
            </div>

            <form method="GET" class="d-flex">
                <div class="input-group">
                    <input type="search" class="form-control" placeholder="Search..." name="search" value="<?= htmlspecialchars($search); ?>">
                    <button class="btn btn-primary py-0 px-2" type="submit"><i class="bi bi-search"></i></button>
                </div>
                
                <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter); ?>">
                <input type="hidden" name="title" value="<?= htmlspecialchars($sort_item); ?>">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort_order); ?>">
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
                    <label for="monthFilter" class="form-label fs-6 fw-bold mb-1">Filter by Batch</label>
                    <select id="monthFilter" name="month_filter" class="form-select form-select-sm">
                        <option value="">All Batches</option>
                        <?php foreach ($workshop_batches as $group_label => $options): ?>
                            <optgroup label="<?= htmlspecialchars($group_label); ?>">
                            <?php foreach ($options as $filter_value): ?>
                                <option value="<?= htmlspecialchars($filter_value); ?>" <?= ($month_filter == $filter_value) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($filter_value); ?>
                                </option>
                            <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="form-label fs-6 fw-bold mb-1">Submit Date</label>
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
                            <th class="text-light"><?= sort_link('id', 'ID', $sort_item, $sort_order, $search, $status_filter, $workshop_filter, $date_from, $date_to, $month_filter) ?></th>
                            <th class="text-light"><?= sort_link('email', 'Email', $sort_item, $sort_order, $search, $status_filter, $workshop_filter, $date_from, $date_to, $month_filter) ?></th>
                            <th class="text-light"><?= sort_link('first_name', 'Name', $sort_item, $sort_order, $search, $status_filter, $workshop_filter, $date_from, $date_to, $month_filter) ?></th>
                            <th class="text-light text-nowrap"><?= sort_link('workshop_title', 'Workshop Title', $sort_item, $sort_order, $search, $status_filter, $workshop_filter,  $date_from, $date_to, $month_filter) ?></th>
                            <th class="text-light text-nowrap"><?= sort_link('contact_number', 'Contact No.', $sort_item, $sort_order, $search, $status_filter, $workshop_filter,  $date_from, $date_to, $month_filter) ?></th>
                            <th class="text-light  text-nowrap"><?= sort_link('no_of_seats', 'No. Seats', $sort_item, $sort_order, $search, $status_filter, $workshop_filter,  $date_from, $date_to, $month_filter) ?></th>
                            <th class="text-light  text-nowrap">Date & Time</th>
                            <?php if($status_filter == 'edited'): ?>
                                <th class="text-light">Status</th>
                            <?php endif; ?>
                            <th class="text-light">Decision</th>
                            <th class="text-light"><?= sort_link('submit_time', 'Submit Time', $sort_item, $sort_order, $search, $status_filter, $workshop_filter, $date_from, $date_to, $month_filter) ?></th>
                            <th class="text-light">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rejection_modals_html = [];
                        $edit_modals_html = [];
                        if ($displayed_rows > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): 
                                $workshop_index = null; 
                                $workshop_session_data = null; 

                                switch ($row['workshop_title']) {
                                    case 'Hobby Class':
                                        $workshop_index = 0;
                                        break;
                                    case 'Handtied Bouquet':
                                        $workshop_index = 1;
                                        break;
                                    case 'Florist To Be 1':
                                        $workshop_index = 2;
                                        break;
                                    case 'Florist To Be 2':
                                        $workshop_index = 3;
                                        break;
                                }

                                if ($workshop_index !== null && isset($workshops[$workshop_index])) {
                                    if (strpos($row['workshop_title'], 'Florist To Be') === false) {
                                        $workshop_session_data = $workshops[$workshop_index]["schedule"];
                                    } else {
                                        $workshop_session_data = $workshops[$workshop_index]["batches"];
                                    }
                                }
                                ?>
                                <tr>
                                    <td class="text-center"><input type="checkbox" name="selected_ids[]" value="<?= $row['id']; ?>"></td>
                                    <td><?= $row['id']; ?></td>
                                    <td><?= $row['email']; ?></td>
                                    <td class="text-nowrap"><?= $row['first_name']; ?> <?= $row['last_name']; ?></td>
                                    <td class="text-nowrap"><?= $row['workshop_title']; ?></td>
                                    <td><?= $row['contact_number']; ?></td>
                                    <td class="text-center">
                                        <?php echo isset($row['pending_seats']) ? '<span class="text-muted">' : '' ; ?><?= $row['no_of_seats']; ?><?php echo isset($row['pending_seats']) ? '</span> <i class="bi bi-arrow-right-short"></i> <span class="text-primary fw-bold">' . $row['pending_seats'] . '</span>' : '' ; ?>
                                    </td>

                                    <?php
                                    $dates_array = array_map('trim', explode(',', $row['date']));
                                    $times_array = array_map('trim', explode(',', $row['time']));
                                    $is_edit_valid = false;
                                    
                                    $first_session_timestamp = strtotime($dates_array[0]);
                                    
                                    if ($status_filter == 'pending' || $status_filter == 'edited'){
                                        $is_edit_valid = true;
                                    } else {
                                        if (str_contains($row['workshop_title'], 'Florist To Be')) {
                                            if ($first_session_timestamp < strtotime('first day of this month')) {
                                                $is_edit_valid = true;
                                            }
                                        } else {
                                            if ($first_session_timestamp < strtotime(date('Y-m-d'))) {
                                                $is_edit_valid = true;
                                            }
                                        }
                                    }
                                    ?>
                                    <td class="small text-nowrap p-0 align-middle">
                                        <div>
                                            <?php 
                                            foreach ($dates_array as $i => $d): 
                                                $is_even = ($i % 2 == 0);
                                                $bg_class = $is_even ? 'bg-light' : '';
                                                $inner_classes = "w-100 p-1 text-center border-top border-secondary border-opacity-25 {$bg_class}";
                                            ?>
                                                <div class="<?= $inner_classes; ?>">
                                                    <?= htmlspecialchars($d); ?> @ <?= htmlspecialchars($times_array[$i] ?? ''); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    
                                    <?php if ($status_filter == 'edited'): ?>
                                        <td class="text-center">
                                            <span class="badge <?php echo ($row['approve_status'] == 'approved') ? 'bg-success' : 'bg-danger'; ?>"><?= $row['approve_status']; ?></span>
                                        </td>
                                    <?php endif; ?>
                                    <td class="text-center text-nowrap">
                                        <?php if ($status_filter == 'edited'): ?>
                                            <button type="submit" name="action_approve_edit" value="<?= $row['id']; ?>" class="btn btn-sm approve-btn">
                                                <i class="bi bi-check2"></i> APPROVE
                                            </button>

                                            <button type="button" class="btn btn-sm reject-btn" data-bs-toggle="modal" data-bs-target="#rejectModalEdit_<?= $row['id']; ?>">
                                                <i class="bi bi-x"></i> REJECT
                                            </button>
                                            <?php $rejection_modals_html[] = rejection_modal($row['id'], 'edit_reject', $base_url); ?>
                                        <?php else: ?>
                                            <?php if ($status_filter != 'approved'): ?>
                                                <button type="submit" name="action_approve" value="<?= $row['id']; ?>" class="btn btn-sm approve-btn">
                                                    <i class="bi bi-check2"></i> APPROVE
                                                </button>
                                            <?php endif; ?> 
                                            <?php if ($status_filter != 'rejected'): ?>
                                                <button type="button" class="btn btn-sm reject-btn" data-bs-toggle="modal" data-bs-target="#rejectModalNormal_<?= $row['id']; ?>">
                                                    <i class="bi bi-x"></i> REJECT
                                                </button>
                                                <?php $rejection_modals_html[] = rejection_modal($row['id'], 'normal_reject', $base_url); ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-nowrap"><?= $row['submit_time']; ?></td>
                                    <td class="text-center">
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#editModal_<?= $row['id'] ?>" class="btn fs-5 p-0 border-0 align-baseline <?php echo $is_edit_valid ? 'text-secondary' : 'text-primary'; ?>" <?php echo $is_edit_valid ? 'disabled' : ''; ?>>
                                            <i class="bi bi-pencil-square"></i>
                                        </button>&nbsp;
                                        <?php $edit_modals_html[] = edit_modal($row['id'], $row['email'], $row['first_name'], $row['last_name'], $row['workshop_title'], $row['contact_number'], $row['no_of_seats'], $base_url, $workshop_session_data, $dates_array, $times_array); ?>
                                        <button type="submit" name="action_delete" value="<?= $row['id']; ?>" class="btn text-danger fs-5 p-0 border-0 align-baseline">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan='<?php echo ($status_filter == 'edited') ? '12' : '11' ; ?>' class="ps-3">No results found</td>
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
                    <?php if ($status_filter == 'edited'): ?>
                        <button type="submit" name="action_bulk_approve_edit" class="btn btn-success btn-sm text-light border-success px-3">
                            <i class="bi bi-check2-square"></i> Approve Selected
                        </button>
                        <button type="submit" name="action_bulk_reject_edit" class="btn btn-sm btn-warning text-muted border-warning px-3">
                            <i class="bi bi-x-square-fill"></i> Reject Selected
                        </button>
                    <?php else: ?>
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
                    <?php endif; ?>
                    
                    <button type="submit" name="action_bulk_delete" class="btn btn-sm btn-danger text-light border-danger px-3">
                        <i class="bi bi-trash3-fill"></i> Delete Selected
                    </button>
                </div>
            </div>
        </form>

        <?php 
            if (!empty($rejection_modals_html)) {
                echo implode('', $rejection_modals_html);
            }

            if (!empty($edit_modals_html)){
                echo implode('', $edit_modals_html);
            }
        ?>

        <!-- New Workshop Registration Form Modal & Validation -->
        <?php include_once "include/addworkshop_modal.php"; ?>
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

<?php 
mysqli_close($conn);
ob_end_flush(); 
?>
</body>
</html>
