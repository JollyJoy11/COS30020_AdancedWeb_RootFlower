<?php
    echo
    "<footer class='fs-6 pt-4 px-4 px-lg-5 text-secondary'>
        <div class='row mx-auto w-100'>
            <div class='col-12 col-md-4 col-lg-2 ms-lg-5'>
                <h3 class='fs-5'>
                    <button class='btn text-secondary p-0 d-md-none fs-5 w-100 d-flex justify-content-between border-0' type='button' data-bs-toggle='collapse' data-bs-target='#quickLinks' aria-expanded='false' aria-controls='quickLinks'>
                        Quick Links <i class='icon bi'></i>
                    </button>
                    <span class='d-none d-md-inline'>Quick Links</span>
                </h3>
                <div class='collapse d-md-block' id='quickLinks'>
                    <ul class='list-unstyled'>
                        <li><a href='main_menu.php' class='text-secondary text-decoration-none'>Home</a></li>
                        <li><a href='products.php' class='text-secondary text-decoration-none'>Products</a></li>
                        <li><a href='workshops.php' class='text-secondary text-decoration-none'>Workshops</a></li>
                        <li><a href='studentworks.php' class='text-secondary text-decoration-none'>Student Creations</a></li>
                        <li><a href='tel:+60143399709' class='text-secondary text-decoration-none' title='+60143399709'>Contact Us</a></li>
                    </ul>
                </div>
            </div>
            
            <div class='col-12 col-md-4 col-lg-2'>
                <h3 class='fs-5'>
                    <button class='btn text-secondary p-0 d-md-none fs-5 w-100 d-flex justify-content-between border-0' type='button' data-bs-toggle='collapse' data-bs-target='#productLinks' aria-expanded='false' aria-controls='productLinks'>
                        Shop by Category <i class='icon bi'></i>
                    </button>
                    <span class='d-none d-md-inline'>Shop by Category</span>
                </h3>
                <div class='collapse d-md-block' id='productLinks'>
                    <ul class='list-unstyled'>
                        <li><a href='products.php?product=bouquet' class='text-decoration-none text-secondary'>Bouquets</a></li>
                        <li><a href='products.php?product=basket' class='text-decoration-none text-secondary'>Flower Baskets</a></li>
                        <li><a href='products.php?product=stand' class='text-decoration-none text-secondary'>Flower Stands</a></li>
                        <li><a href='products.php?product=special' class='text-decoration-none text-secondary'>Specials</a></li>
                        <li><a href='products.php?occasion=all' class='text-decoration-none text-secondary'>By Occasions</a></li>
                    </ul>
                </div>
            </div>

            <div class='col-12 col-md-4 col-lg-2'>
                <h3 class='fs-5'>
                    <button class='btn text-secondary p-0 d-md-none fs-5 w-100 d-flex justify-content-between border-0' type='button' data-bs-toggle='collapse' data-bs-target='#accountLinks' aria-expanded='false' aria-controls='accountLinks'>
                        Account <i class='icon bi'></i>
                    </button>
                    <span class='d-none d-md-inline'>Account</span>
                </h3>
                <div class='collapse d-md-block' id='accountLinks'>
                    <ul class='list-unstyled'>
                        <li><a href='update_profile.php' class='text-decoration-none text-secondary'>My Profile</a></li>
                        <li><a href='order_history.php' class='text-decoration-none text-secondary'>Track Orders</a></li>
                        <li><a href='cart.php' class='text-decoration-none text-secondary'>Shopping Bag</a></li>
                        <li><a href='my_workshops.php' class='text-decoration-none text-secondary'>Workshop History</a></li>
                    </ul>
                </div>
            </div>

            <div class='col-12 col-lg-5'>
                <h3 class='fs-5'>Subscribe to our newsletter</h3>
                <p>Get latest updates on our products and upcoming workshops</p>
                <form action='newsletter_subscribe.php' name='newsletter' method='POST' class='d-flex flex-column flex-sm-row gap-2'>
                    <div class='input-group'>
                        <input type='email' name='email' class='form-control' placeholder='Enter your email' required>
                        <button type='submit' class='btn btn-primary py-0 px-2' id='newsletter-btn'>Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
        <hr>
        <div class='d-flex justify-content-between lh-1'>
            <p>&copy; 2025 Root Flower</p>
            <p>
                Follow Us 
                <a href='https://www.instagram.com/root.flowersss' target='_blank' title='Instagram' class='text-secondary text-decoration-none ps-2'><i class='bi bi-instagram'></i></a> 
                <a href='https://www.facebook.com/share/15ywShieQr/' target='_blank' title='Facebook' class='text-secondary text-decoration-none px-2'><i class='bi bi-facebook'></i></a> 
                <span class='pe-2'> | </span>
                <a href='https://api.whatsapp.com/send?phone=60143399709' target='_blank' title='WhatsApp' class='text-secondary text-decoration-none'><i class='bi bi-whatsapp'></i></a> 
            </p>
        </div>
    </footer>";
?>