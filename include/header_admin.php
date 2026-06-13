<?php
    echo 
    "<input type='checkbox' id='nav-toggle' hidden>
    <label for='nav-toggle' class='position-fixed px-2 py-1 rounded-end text-light d-flex align-items-center justify-content-center'>
        <i class='bi bi-chevron-double-right'></i>
        <i class='bi bi-chevron-double-left d-none'></i>
    </label>

    <div id='admin-nav' class='fixed-top vh-100 overflow-hidden'>
        <a href='main_menu_admin.php'><img src='img/rootflower.jpg' alt='Root Flower Logo' class='d-block mx-auto'></a>

        <ul class='nav flex-column ps-0 ps-sm-1'>
            <li class='nav-item pt-4 pb-2'>
                <a href='main_menu_admin.php' title='Home' class='nav-link text-secondary'><i class='bi bi-house'></i> <span class='d-none fs-6'>&ensp;Home</span></a>
            </li>
            <li class='nav-item pb-2'>
                <a href='manage_accounts.php' title='Users' class='nav-link text-secondary'><i class='bi bi-people'></i> <span class='d-none fs-6'>&ensp;Users</span></a>
            </li>
            <li class='nav-item pb-2'>
                <a href='manage_studentwork.php' title='Student Works' class='nav-link text-secondary'><i class='bi bi-brush'></i> <span class='d-none fs-6'>&ensp;Student Works</span></a>
            </li>
            <li class='nav-item pb-2'>
                <a href='manage_workshop_reg.php' title='Workshops' class='nav-link text-secondary'><i class='bi bi-flower1'></i> <span class='d-none fs-6'>&ensp;Workshops</span></a>
            </li>
            <li class='nav-item pb-2'>
                <a href='manage_ar.php' title='AR Creations' class='nav-link text-secondary'><i class='bi bi-box'></i> <span class='d-none fs-6'>&ensp;AR Creations</span></a>
            </li>
        </ul>

        <ul class='nav flex-column ps-0 ps-sm-1 mb-4 position-absolute bottom-0'>
            <li class='nav-item'>
                <a href='recycle.php' class='nav-link text-secondary'><i class='bi bi-trash'></i> <span class='d-none fs-6'>&ensp;Recycle Bin</span></a>
            </li>
            <li class='nav-item'>
                <a href='logout.php' class='nav-link text-secondary'><i class='bi bi-box-arrow-right'></i> <span class='d-none fs-6'>&ensp;Log Out</span></a>
            </li>
        </ul>
    </div>
    
    <header class='pe-5 pt-4 d-flex justify-content-end align-items-center sticky-top z-3' id='admin-header'>
        <ul class='nav d-flex align-items-center gap-4'>
            <li class='nav-item'>
                <button class='nav-link text-secondary pb-0 position-relative' type='button' data-bs-toggle='dropdown' aria-expanded='false' id='noti-dropdown'>
                    <i class='bi bi-bell'></i>";
                if (!empty($unread_count) && $unread_count > 0) {
                    echo "<span class='position-absolute badge rounded-pill bg-danger noti-badge'>
                        $unread_count
                        <span class='visually-hidden'>unread messages</span>
                    </span>";
                }
            echo "</button>
                
                <ul class='dropdown-menu bg-white overflow-auto' aria-labelledby='noti-dropdown'>
                    <li class='dropdown-item-text text-secondary'><strong>Notifications</strong></li>";
                if (!empty($unread_notifications)){
                    foreach ($unread_notifications as $noti) {
                        $timestamp = date('j M, H:i', strtotime($noti['created_at']));
                        $safe_message = htmlspecialchars($noti['message']);
                        $safe_url = htmlspecialchars($noti['url']);
                        $notification_id = $noti['id'];

                        $url = $safe_url . (parse_url($safe_url, PHP_URL_QUERY) ? '&' : '?') . 'mark_read=' . $notification_id;
                        
                        echo "
                        <li>
                            <a class='dropdown-item d-flex align-items-center border-top text-secondary' href='$url'>
                                <i class='bi {$noti['icon']} me-2 fs-5'></i>
                                <div>
                                    <p class='m-0 small fw-bold text-wrap'>$safe_message</p>
                                    <p class='m-0 text-muted small fst-italic'>$timestamp</p>
                                </div>
                            </a>
                        </li>";
                    }
                } else {
                    echo 
                    "<li class='dropdown-item-text d-flex flex-column justify-content-center align-items-center h-75'>
                        <i class='bi bi-check2-circle fs-1 text-secondary'></i>
                        You're all caught up!
                    </li>";
                }
            echo "</ul>
            </li>

            <li class='nav-item'>
                <span id='liveClock' class='text-dark'></span>
            </li>
        </ul>
    </header>";

    // Alert
    if (isset($_SESSION['alert'])) {
        foreach ($_SESSION['alert'] as $type => $message) {
            echo "<div class='alert alert-$type alert-dismissible fade show px-4 py-1 position-fixed end-0 mt-3 mx-auto d-flex z-3 admin-alert' role='alert'>
                <div class='pe-2'>";
                    if ($type == "success"){
                        echo "<i class='bi bi-check-circle'></i>";
                    } else if ($type == "danger"){
                        echo "<i class='bi bi-exclamation-circle'></i>";
                    } else if ($type == "info"){
                        echo "<i class='bi bi-info-circle'></i>";
                    }
            echo "</div>
                $message
            </div>";
        }
        unset($_SESSION['alert']); 
    }
?>