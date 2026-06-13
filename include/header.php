<?php
    echo 
    "<input type='checkbox' id='nav-toggle' class='d-lg-none' hidden>
    <label for='nav-toggle' class='position-fixed px-2 py-1 rounded-end text-light d-flex align-items-center justify-content-center d-lg-none'>
        <i class='bi bi-chevron-double-right'></i>
        <i class='bi bi-chevron-double-left d-none'></i>
    </label>

    <div id='side-nav' class='fixed-top vh-100 overflow-hidden'>
        <a href='main_menu.php'><img src='img/rootflower.jpg' alt='Root Flower Logo' class='d-block mx-auto'></a>

        <ul class='nav flex-column d-lg-none ps-0 ps-sm-1'>
            <li class='nav-item pt-5 pb-2'>
                <a href='main_menu.php' title='Home' class='nav-link text-secondary'><i class='bi bi-house'></i> <span class='d-none fs-6'>&ensp;Home</span></a>
            </li>
            <li class='nav-item pb-2'>
                <div class='d-flex justify-content-between align-items-center'>
                    <a href='products.php' title='Shop' class='nav-link text-secondary'><i class='bi bi-shop'></i> <span class='d-none fs-6'>&ensp;Shop</span></a>

                    <button class='btn border-0 fs-5' type='button' data-bs-toggle='collapse' data-bs-target='#productsDropdown' aria-expanded='false' aria-controls='productsDropdown'>
                        <i class='icon bi'></i>
                    </button>
                </div>

                <div class='collapse ms-5' id='productsDropdown'>
                    <h3 class='text-secondary fs-6 fw-bold'>By Category</h3>
                    <ul class='list-unstyled fs-6'>
                        <li><a href='products.php?product=bouquet' class='dropdown-item text-secondary'>Bouquets</a></li>
                        <li><a href='products.php?product=basket' class='dropdown-item text-secondary'>Flower Baskets</a></li>
                        <li><a href='products.php?product=stand' class='dropdown-item text-secondary'>Flower Stands</a></li>
                        <li><a href='products.php?product=special' class='dropdown-item text-secondary'>Specials</a></li>
                    </ul>

                    <h3 class='text-secondary fs-6 pt-3 fw-bold'>By Occasions</h3>
                    <ul class='list-unstyled fs-6'>
                        <li><a href='products.php?product=anniversary' class='dropdown-item text-secondary'>Anniversary</a></li>
                        <li><a href='products.php?product=graduation' class='dropdown-item text-secondary'>Graduation</a></li>
                        <li><a href='products.php?product=wedding' class='dropdown-item text-secondary'>Wedding</a></li>
                        <li><a href='products.php?product=cny' class='dropdown-item text-secondary'>Chinese New Year</a></li>
                    </ul>
                </div>
            </li>
            <li class='nav-item pb-2'>
                <a href='workshops.php' title='Workshops' class='nav-link text-secondary'><i class='bi bi-people'></i> <span class='d-none fs-6'>&ensp;Workshops</span></a>
            </li>
            <li class='nav-item pb-2'>
                <a href='studentworks.php' title='Student Creations' class='nav-link text-secondary'><i class='bi bi-brush'></i> <span class='d-none fs-6'>&ensp;Student Creations</span></a>
            </li>
        </ul>
    </div>
    
    <header class='pe-5 pt-4 fs-6 d-flex justify-content-between align-items-center sticky-top bg-white z-3'>
        <nav class='navbar navbar-expand-lg mx-auto pb-0' id='main_nav'>
            <div class='collapse navbar-collapse'>
                <ul class='navbar-nav nav-underline nav-fill w-100'>
                    <li class='nav-item'><a href='main_menu.php' class='text-secondary nav-link'>Home</a></li>
                    <li class='nav-item dropdown position-static'>
                        <a href='products.php' class='text-secondary nav-link'>Shop</a>

                        <div class='dropdown-menu border-0 border-top border-bottom w-100 pe-3 position-fixed'>
                            <div class='row p-4'>
                                <div class='col-2'>
                                    <h3 class='text-secondary fs-5 ps-3 fw-bold'>By Category</h3>
                                    <ul class='list-unstyled'>
                                        <li><a href='products.php?product=bouquet' class='dropdown-item text-secondary'>Bouquets</a></li>
                                        <li><a href='products.php?product=basket' class='dropdown-item text-secondary'>Flower Baskets</a></li>
                                        <li><a href='products.php?product=stand' class='dropdown-item text-secondary'>Flower Stands</a></li>
                                        <li><a href='products.php?product=special' class='dropdown-item text-secondary'>Specials</a></li>
                                    </ul>
                                </div>

                                <div class='col-2'>
                                    <h3 class='text-secondary fs-5 ps-3 fw-bold'>By Occasions</h3>
                                    <ul class='list-unstyled'>
                                        <li><a href='products.php?product=anniversary' class='dropdown-item text-secondary'>Anniversary</a></li>
                                        <li><a href='products.php?product=graduation' class='dropdown-item text-secondary'>Graduation</a></li>
                                        <li><a href='products.php?product=wedding' class='dropdown-item text-secondary'>Wedding</a></li>
                                        <li><a href='products.php?product=cny' class='dropdown-item text-secondary'>Chinese New Year</a></li>
                                    </ul>
                                </div>

                                <div class='col-8'>
                                    <div class='d-flex gap-3 px-5 me-5'>
                                        <a href='products.php?product=bouquet' class='text-decoration-none'>
                                            <div class='position-relative flex-fill'>
                                                <img src='img/products/product1.jpg' class='img-fluid d-block w-100 h-auto' alt='Bouquet'>
                                                <div class='img-gradient position-absolute h-50 bottom-0 start-0 end-0'></div>
                                                <div class='overlay-text position-absolute text-light px-3'>
                                                    <p class='text-uppercase small m-0'>Bouquet</p>
                                                    <p class='fw-bold fs-5 m-0'>Elegant Surprise</p>
                                                </div>
                                            </div>
                                        </a>

                                        <a href='products.php?product=basket' class='text-decoration-none'>
                                            <div class='position-relative flex-fill'>
                                                <img src='img/products/product4.jpg' class='img-fluid d-block w-100 h-auto' alt='Flower Basket'>
                                                <div class='img-gradient position-absolute h-50 bottom-0 start-0 end-0'></div>
                                                <div class='overlay-text position-absolute text-light px-3'>
                                                    <p class='text-uppercase small m-0'>Flower Basket</p>
                                                    <p class='fw-bold fs-5 m-0'>Joy in a Basket</p>
                                                </div>
                                            </div>
                                        </a>

                                        <a href='products.php?product=stand' class='text-decoration-none'>
                                            <div class='position-relative flex-fill'>
                                                <img src='img/products/product13.jpg' class='img-fluid d-block w-100 h-auto' alt='Flower Stand'>
                                                <div class='img-gradient position-absolute h-50 bottom-0 start-0 end-0'></div>
                                                <div class='overlay-text position-absolute text-light px-3'>
                                                    <p class='text-uppercase small m-0'>Flower Stand</p>
                                                    <p class='fw-bold fs-5 m-0'>Grand Opening</p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class='nav-item'><a href='workshops.php' class='text-secondary nav-link'>Workshops</a></li>
                    <li class='nav-item'><a href='studentworks.php' class='text-secondary nav-link'>Student Creations</a></li>
                </ul>
            </div>
        </nav>

        <ul class='nav fs-5'>
            <li class='nav-item'><a href='flower.php' class='nav-link text-secondary pb-0'><i class='bi bi-flower2'></i></a></li>
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
                
                <ul class='dropdown-menu bg-white mt-0 mt-lg-1 overflow-auto' aria-labelledby='noti-dropdown'>
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

            <li class='nav-item dropdown'>
                <button class='nav-link text-secondary pb-0' type='button' data-bs-toggle='dropdown' aria-expanded='false' id='profile-dropdown'><i class='bi bi-person'></i></button>

                <ul class='dropdown-menu bg-white mt-0 mt-lg-1' aria-labelledby='profile-dropdown'>
                    <li class='text-center'>
                        <figure class='rounded-circle overflow-hidden border mx-auto mt-2'>";
                        if (!empty($_SESSION['profile'])){
                            echo "<img src='profile_images/{$_SESSION['profile']}' alt='{$_SESSION['name']} Profile Picture' class='img-fluid'>";
                        } else {
                            $defaultImage = (isset($_SESSION['gender']) && $_SESSION['gender'] == 'Female') ? 'girl.png' : 'boy.png';
                            echo "<img src='profile_images/$defaultImage' alt='Profile Image' class='img-fluid'>";
                        }
                        
                    echo "</figure>
                        <span class='dropdown-item-text'>Hi, " . $_SESSION['name'] . "!</span>
                    </li>
                    <li><hr class='dropdown-divider'></li>
                    <li><a class='dropdown-item' href='update_profile.php'>View Profile</a></li>
                    <li><a class='dropdown-item' href='order_history.php'>Order History</a></li>
                    <li><a class='dropdown-item' href='my_workshops.php'>My Workshops</a></li>
                    <li><hr class='dropdown-divider'></li>
                    <li><a class='dropdown-item' href='logout.php'>Log Out <i class='bi bi-box-arrow-right'></i></a></li>
                </ul>
            </li>
            <li class='nav-item'><a href='cart.php' class='nav-link text-secondary pb-0'><i class='bi bi-basket'></i></a></li>
        </ul>
    </header>";

    // Alert
    if (isset($_SESSION['alert'])) {
        foreach ($_SESSION['alert'] as $type => $message) {
            echo "<div class='alert alert-$type alert-dismissible fade show px-4 py-1 position-fixed end-0 mt-3 mx-auto d-flex z-3' role='alert'>
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