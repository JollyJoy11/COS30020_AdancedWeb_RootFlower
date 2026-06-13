<?php
// Insert dummy users
$users = [
    ["admin@swin.edu.my", "Admin", "Admin", null, "Other", "Kuching"],
    ["joannecjx.0111@gmail.com", "Joanne Jia Xuan", "Chin", "2005-01-11", "Female", "Kota Kinabalu, Sabah"],
    ["robertyong@gmail.com", "Robert", "Yong", "2000-03-27", "Male", "Kuching, Sarawak"],
    ["ellieteng09@gmail.com", "Ellie", "Teng", "2005-09-09", "Female", "Sarikei, Sarawak"],
    ["lydenlau017@gmail.com", "Lyden", "Lau", "2005-06-17", "Male", "Kota Kinabalu, Sabah"]
];

foreach ($users as $u) {
    $check = "SELECT 1 FROM user_table WHERE email = '$u[0]'";
    $result = mysqli_query($conn, $check);

    if (mysqli_num_rows($result) == 0) {
        $fname    = mysqli_real_escape_string($conn, $u[1]);
        $lname    = mysqli_real_escape_string($conn, $u[2]);
        $dob_sql  = ($u[3] === null) ? 'NULL' : "'" . mysqli_real_escape_string($conn, $u[3]) . "'";
        $gender   = mysqli_real_escape_string($conn, $u[4]);
        $hometown = mysqli_real_escape_string($conn, $u[5]);
        $query = "INSERT INTO user_table (email, first_name, last_name, dob, gender, hometown) VALUES ('$u[0]', '$fname', '$lname', $dob_sql, '$gender', '$hometown')";
        mysqli_query($conn, $query);
    }
}

// Insert matching accounts
$accounts = [
    ["admin@swin.edu.my", password_hash("admin", PASSWORD_DEFAULT), "admin"],
    ["joannecjx.0111@gmail.com", password_hash("qwer1234!", PASSWORD_DEFAULT), "user"],
    ["robertyong@gmail.com", password_hash("qwer123!", PASSWORD_DEFAULT), "user"],
    ["ellieteng09@gmail.com", password_hash("Ell!e0909", PASSWORD_DEFAULT), "user"],
    ["lydenlau017@gmail.com", password_hash("lydenlau1234!", PASSWORD_DEFAULT), "user"]
];

foreach ($accounts as $a) {
    $check = "SELECT 1 FROM account_table WHERE email = '$a[0]'";
    $result = mysqli_query($conn, $check);

    if (mysqli_num_rows($result) == 0) {
        $query = "INSERT INTO account_table (email, password, type) VALUES ('$a[0]', '$a[1]', '$a[2]')";
        mysqli_query($conn, $query);
    }
}

// Insert dummy workshops
$workshops = [
    ["joannecjx.0111@gmail.com", "Joanne Jia Xuan", "Chin", "Hobby Class", "2025-10-18", "2:00 PM - 6:00 PM", 1, "012-3456789", "approved"],
    ["joannecjx.0111@gmail.com", "Joanne Jia Xuan", "Chin", "Florist To Be 2", "2025-11-07, 2025-11-10, 2025-11-17, 2025-11-26", "2:30 PM - 6:30 PM, 8:30 AM - 12:30 PM, 8:30 AM - 12:30 PM, 2:30 PM - 6:30 PM", 1, "012-3456789", "approved"],
    ["joannecjx.0111@gmail.com", "Joanne Jia Xuan", "Chin", "Handtied Bouquet", "2025-10-11, 2025-10-12", "2:00 PM - 6:00 PM, 8:00 AM - 11:30 AM", 1, "012-3456789", "rejected"],
    ["joannecjx.0111@gmail.com", "Joanne Jia Xuan", "Chin", "Handtied Bouquet", "2025-11-15, 2025-11-16", "1:00 PM - 5:00 PM, 2:00 PM - 5:30 PM", 1, "012-3456789", "approved"],
    ["ellieteng09@gmail.com", "Ellie", "Teng", "Florist To Be 1", "2025-11-03, 2025-11-12, 2025-11-18, 2025-11-28", "8:30 AM - 12:30 PM, 8:30 AM - 12:30 PM, 8:30 AM - 12:30 PM, 8:30 AM - 12:30 PM", 1, "011-10891653", "approved"],
    ["joannecjx.0111@gmail.com", "Aryn", "Jee", "Handtied Bouquet",  "2025-12-20, 2025-12-21", "1:00 PM - 5:00 PM, 2:00 PM - 5:30 PM", 5, "011-25292278", "approved"],
    ["ellieteng09@gmail.com", "Ellie", "Teng", "Hobby Class", "2025-10-18", "2:00 PM - 6:00 PM", 1, "012-3456789", "approved"],
    ["ellieteng09@gmail.com", "Ellie", "Teng", "Handtied Bouquet", "2025-09-06, 2025-09-07", "1:00 PM - 5:00 PM, 8:00 AM - 11:30 AM", 1, "012-3456789", "rejected"],
    ["robertyong@gmail.com", "Robert", "Yong", "Hobby Class", "2025-12-20", "8:00 AM - 12:00 PM", 1, "012-3356789", "approved"],
    ["robertyong@gmail.com", "Robert", "Yong", "Florist To Be 2", "2025-12-01, 2025-12-12, 2025-12-17, 2025-12-24", "2:30 PM - 6:30 PM, 8:30 AM - 12:30 PM, 8:30 AM - 12:30 PM, 2:30 PM - 6:30 PM", 3, "012-3356789", "rejected"],
    ["robertyong@gmail.com", "Robert", "Yong", "Handtied Bouquet", "2025-10-11, 2025-10-12", "2:00 PM - 6:00 PM, 8:00 AM - 11:30 AM", 1, "012-3356789", "approved"],
    ["robertyong@gmail.com", "Robert", "Yong", "Hobby Class", "2026-01-17", "1:00 PM - 5:00 PM", 2, "012-3356789", "pending"],
    ["lydenlau017@gmail.com", "Lyden", "Lau", "Handtied Bouquet", "2025-09-06, 2025-09-07", "1:00 PM - 5:00 PM, 8:00 AM - 11:30 AM", 1, "012-3456789", "approved"],
    ["lydenlau017@gmail.com", "Lyden", "Lau", "Hobby Class", "2026-01-17", "8:00 AM - 12:00 PM", 1, "012-3356789", "pending"],
    ["lydenlau017@gmail.com", "Lyden", "Lau", "Florist To Be 2", "2025-09-01, 2025-09-12, 2025-09-17, 2025-09-24", "2:30 PM - 6:30 PM, 8:30 AM - 12:30 PM, 8:30 AM - 12:30 PM, 2:30 PM - 6:30 PM", 3, "012-3356789", "approved"],
    ["lydenlau017@gmail.com", "Lyden", "Lau", "Handtied Bouquet", "2025-10-11, 2025-10-12", "2:00 PM - 6:00 PM, 8:00 AM - 11:30 AM", 1, "012-3356789", "approved"]
];

foreach ($workshops as $w) {
    $check = "SELECT 1 FROM workshop_table WHERE email = '$w[0]' AND workshop_title = '$w[3]' AND date = '$w[4]'";
    $result = mysqli_query($conn, $check);

    if (mysqli_num_rows($result) == 0) {
        $query = "INSERT INTO workshop_table (email, first_name, last_name, workshop_title, date, time, no_of_seats, contact_number, approve_status) VALUES ('$w[0]', '$w[1]', '$w[2]', '$w[3]', '$w[4]', '$w[5]', $w[6], '$w[7]', '$w[8]')";
        mysqli_query($conn, $query);
    }
}

// Insert dummy flowers
$flowers = [
    ["Rosa chinensis", "Chinese Rose", "rose.jpg", "generated_1764704055.pdf"],
    ["Hibiscus rosa-sinensis", "Bunga Raya", "hibiscus.jpg", "generated_1764705246.pdf"],
    ["Nymphaea nouchali", "Water Lily", "waterlily.jpg", "generated_1764704234.pdf"],
    ["Orchidaceae", "Orchid", "orchid.jpg", "generated_1764704287.pdf"],
    ["Camellia japonica", "Japanese Camellia", "camellia.jpg", "generated_1764704331.pdf"],
    ["Tulipa gesneriana", "Garden Tulip", "tulip.jpg", "generated_1764704378.pdf"],
    ["Helianthus annuus", "Sunflower", "sunflower.jpeg", "generated_1764704442.pdf"],
    ["Lavandula angustifolia", "Lavender", "lavender.jpg", "generated_1764704611.pdf"],
    ["Dahlia pinnata", "Dahlia", "dahlia.jpeg", "generated_1764704659.pdf"],
    ["Galanthus nivalis", "Snowdrop", "snowdrop.jpeg", "generated_1764704725.pdf"],
    ["Lilium", "Lily", "lily.jpg", "generated_1764704779.pdf"],
    ["Freesia", "Freesia", "freesia.jpg", "generated_1764704848.pdf"],
    ["Zinnia elegans", "Zinnia", "zinnia.jpeg", "generated_1764704896.pdf"],
    ["Chrysanthemum", "Mum", "mum.jpg", "generated_1764704938.pdf"],
    ["Crocus", "Crocus", "crocus.jpg", "generated_1764704988.pdf"]
];

foreach ($flowers as $f) {
    $sci = mysqli_real_escape_string($conn, $f[0]);
    $check = "SELECT 1 FROM flower_table WHERE Scientific_Name = '$sci'";
    $result = mysqli_query($conn, $check);

    if (mysqli_num_rows($result) == 0) {
        $com = mysqli_real_escape_string($conn, $f[1]);
        $img = mysqli_real_escape_string($conn, $f[2]);
        $desc = mysqli_real_escape_string($conn, $f[3]);
        $query = "INSERT INTO flower_table (Scientific_Name, Common_Name, plants_image, description) VALUES ('$sci', '$com', '$img', '$desc')";
        mysqli_query($conn, $query);
    }
}

// Insert dummy student works
$studentworks = [
    ["joannecjx.0111@gmail.com", "Joanne", "Chin", 1, "work1.jpeg, work7.mp4, work11.jpeg", "I really enjoyed my first workshop! I made my first boutineer, tried a lively flower arrangement video, and explored an abstract bridal bouquet design.", 87, "2025-01-10"],
    ["joannecjx.0111@gmail.com", "Aryn", "Jee", 2, "work3.jpeg, work15.jpeg, work18.jpeg, work13.jpeg", "This workshop was so much fun! I created a mix of flower baskets, hand-tied bouquets, and a bridal bouquet, and learned how to balance colors and textures.", 123, "2025-01-15"],
    ["joannecjx.0111@gmail.com", "Joanne", "Chin", 2, "work6.jpeg, work19.jpeg", "I focused on minimalist and single-stalk bouquets today. I loved arranging them carefully and seeing how small changes make a big difference.", 71, "2025-01-24"],
    ["joannecjx.0111@gmail.com", "Brenda", "Banana", 1, "work20.mp4, work23.jpeg, work25.jpeg, work26.mp4", "In this workshop, I tried a variety of media — from slow-motion flower videos to classic bouquets and festive arrangements. I learned to experiment with different styles.", 137, "2025-03-02"],

    ["robertyong@gmail.com", "Robert", "Yong", 11, "work2.jpeg, work5.jpeg, work21.jpeg, work9.jpeg", "🌼✨", 42, "2025-01-12"],
    ["robertyong@gmail.com", "Robert", "Yong", 11, "work4.mp4, work10.jpeg, work17.jpeg", "🏵️", 64, "2025-01-18"],
    
    ["ellieteng09@gmail.com", "Ellie", "Teng", 5, "work22.jpeg, work24.jpg", "", 105, "2025-03-07"],

    ["lydenlau017@gmail.com", "Lyden", "Lau", 13, "work14.mp4, work8.jpeg, work16.jpeg", "I enjoyed learning how to make each piece unique and presentable.", 29, "2025-01-30"],
    ["lydenlau017@gmail.com", "Lyden", "Lau", 16, "work12.jpeg, work27.jpeg, work28.jpeg", "It was very satisfying to see all the pieces come together.", 47, "2025-02-10"]
];

foreach ($studentworks as $s) {
    $caption = mysqli_real_escape_string($conn, $s[5]);

    $check = "SELECT 1 FROM studentworks_table WHERE workshop_media = '$s[4]'";
    $result = mysqli_query($conn, $check);

    if (mysqli_num_rows($result) == 0) {
        $query = "INSERT INTO studentworks_table (email, first_name, last_name, workshop_id, workshop_media, caption, likes, upload_time, approve_status) VALUES ('$s[0]', '$s[1]', '$s[2]', $s[3], '$s[4]', '$caption', $s[6], '$s[7]', 'approved')";
        mysqli_query($conn, $query);
    }
}

// Insert dummy comments
$comments = [
    [1, "ellieteng09@gmail.com", "This arrangement looks so beautiful and well-balanced!"],
    [2, "robertyong@gmail.com", "Your color choices are amazing. 😍 Love the soft tones!"],
    [3, "ellieteng09@gmail.com", "I really like the minimalist style in this bouquet. 💐"],
    [4, "lydenlau017@gmail.com", "The slow-motion video is so satisfying to watch! Great job."]
];

foreach ($comments as $c) {
    $comment = mysqli_real_escape_string($conn, $c[2]);

    $check = "SELECT 1 FROM studentworkcomments_table WHERE comment_text = '$comment' AND email = '$c[1]'";
    $result = mysqli_query($conn, $check);

    if (mysqli_num_rows($result) == 0) {
        $query = "INSERT INTO studentworkcomments_table (studentwork_id, email, comment_text) VALUES ($c[0], '$c[1]', '$comment')";
        mysqli_query($conn, $query);
    }
}

// Insert dummy likes
$likes = [
    [1, "robertyong@gmail.com"],
    [2, "joannecjx.0111@gmail.com"],
    [3, "joannecjx.0111@gmail.com"],
    [4, "joannecjx.0111@gmail.com"]
];

foreach ($likes as $l) {
    $check = "SELECT 1 FROM studentworklikes_table WHERE studentwork_id = $l[0] AND email = '$l[1]'";
    $result = mysqli_query($conn, $check);

    if (mysqli_num_rows($result) == 0) {
        $query = "INSERT INTO studentworklikes_table (studentwork_id, email) VALUES ($l[0], '$l[1]')";
        mysqli_query($conn, $query);
    }
}

// Insert dummy products
$products = [
    ["Pink Lily Bouquet", 189, "bouquet", "", "img/products/product1.jpg", 4.5, 24],
    ["Red Rose Bouquet", 169, "bouquet", "anniversary", "img/products/product2.jpg", 4.2, 18],
    ["Sunshine Daisy Bouquet", 149, "bouquet", "", "img/products/product3.jpg", 4.7, 32],
    ["Orchid Love Special", 199, "special", "", "img/products/product5.jpg", 4.6, 21],
    ["Mixed Flower Surprise", 179, "special", "", "img/products/product6.jpg", 4.1, 15],
    ["Classic Rose Bouquet", 189, "bouquet", "", "img/products/product7.jpg", 4.8, 40],
    ["Sunflower Graduation Joy", 169, "", "graduation", "img/products/product15.jpg", 4.5, 28],
    ["Tulip Graduation Charm", 159, "", "graduation", "img/products/product16.jpg", 4.6, 30],
    ["Romantic Carnation Combo", 189, "bouquet", "anniversary", "img/products/product9.jpg", 4.7, 35],
    ["Pink Rose Delight", 179, "bouquet", "", "img/products/product10.jpg", 4.3, 22],
    ["Peony CNY Blessing", 199, "special", "cny", "img/products/product11.jpg", 4.6, 27],
    ["Wedding White Roses", 249, "", "wedding", "img/products/product12.jpg", 4.8, 42],
    ["Grand Opening Stand", 299, "stand", "", "img/products/product13.jpg", 4.5, 19],
    ["CNY Orchid Joy", 209, "special", "cny", "img/products/product14.jpg", 4.2, 20],
    ["Bright Sunflower Graduation", 179, "", "graduation", "img/products/product18.jpg", 4.6, 24],
    ["Blush Whisper Roses", 199, "bouquet", "anniversary", "img/products/product19.jpg", 4.7, 31],
    ["Anniversary Rose Charm", 189, "bouquet", "anniversary", "img/products/product22.jpg", 4.4, 26],
    ["Pink Crunchy Love", 179, "special", "anniversary", "img/products/product23.jpg", 4.5, 29],
    ["Petals of Grace Basket", 169, "basket", "", "img/products/product4.jpg", 4.6, 18],
    ["Flourish & Fortune Basket", 159, "basket", "", "img/products/product41.jpg", 4.3, 15],
    ["Fortune & Harmony Basket", 169, "basket", "", "img/products/product42.jpg", 4.4, 20],
    ["Get Well Soon Basket", 189, "basket", "", "img/products/product43.jpg", 4.5, 17],
    ["Romantic Bridal Bouquet", 239, "", "wedding", "img/products/product21.jpg", 4.8, 39],
    ["Blossom Wedding Bouquet", 249, "", "wedding", "img/products/product24.jpg", 4.7, 25],
    ["Tulip Wedding Bouquet", 259, "", "wedding", "img/products/product26.jpg", 4.9, 33],
    ["Orchid Wedding Dream", 229, "", "wedding", "img/products/product30.jpg", 4.6, 21],
    ["Sunset Bouquet", 169, "bouquet", "", "img/products/product20.jpg", 4.3, 15],
    ["Blue Serenity Mix", 159, "bouquet", "", "img/products/product27.jpg", 4.2, 19],
    ["Special Rose Romance", 199, "special", "anniversary", "img/products/product31.jpg", 4.5, 22],
    ["Anniversary Peony Charm", 189, "bouquet", "anniversary", "img/products/product34.jpg", 4.4, 17],
    ["Delightful Tulip", 159, "bouquet", "", "img/products/product35.jpg", 4.2, 16],
    ["Sweet Bliss Snack Bouquet", 209, "special", "", "img/products/product33.jpg", 4.6, 24],
    ["Festival CNY Roses", 179, "special", "cny", "img/products/product25.jpg", 4.3, 22],
    ["Lucky Peony CNY", 159, "special", "cny", "img/products/product37.jpg", 4.4, 28],
    ["Golden Lily CNY", 189, "special", "cny", "img/products/product38.jpg", 4.5, 30],
    ["Fortune Abundance Plant", 179, "special", "cny", "img/products/product39.jpg", 4.6, 25],
    ["Special Graduation Charm", 199, "special", "graduation", "img/products/product40.jpg", 4.7, 27],
    ["Rose Basket Joy", 169, "basket", "", "img/products/product51.jpg", 4.2, 14],
    ["Whispers of Spring Basket", 199, "basket", "", "img/products/product52.jpg", 4.3, 20],
    ["Grand Opening Sunflower Stand", 299, "stand", "", "img/products/product28.jpg", 4.6, 18],
    ["Eternal Love Bouquet", 189, "bouquet", "anniversary", "img/products/product44.jpg", 4.7, 33],
    ["Anniversary Charm Bouquet", 209, "bouquet", "anniversary", "img/products/product46.jpg", 4.6, 28],
    ["Special Rose Glow", 199, "special", "anniversary", "img/products/product50.jpg", 4.5, 26],
    ["Teddy's Graduation Blooms", 239, "", "graduation", "img/products/product45.jpg", 4.4, 19],
    ["Pompon Graduation Smile", 229, "", "graduation", "img/products/product47.jpg", 4.5, 23],
    ["Sunflower Graduation Glow", 99, "", "graduation", "img/products/product48.jpg", 4.6, 25],
    ["Sweet Serenity Roses", 209, "bouquet", "anniversary", "img/products/product49.jpg", 4.7, 29],
    ["Radiant Sunflower Bouquet", 229, "bouquet", "graduation", "img/products/product32.jpg", 4.5, 20],
    ["Bright Rose Stand", 259, "stand", "", "img/products/product53.jpg", 4.8, 21],
    ["Orchid Opening Stand", 279, "stand", "", "img/products/product54.jpg", 4.7, 18],
    ["Deluxe Celebration Stand", 289, "stand", "", "img/products/product55.jpg", 4.6, 16],
    ["Bridal Flower", 359, "", "wedding", "img/products/product8.jpg", 4.8, 22],
    ["Prosperity Blossom Vase", 199, "special", "cny", "img/products/product17.jpg", 4.5, 18],
    ["Elegant Tribute Stand", 269, "stand", "", "img/products/product29.jpg", 4.3, 11],
    ["Twilight Bloom Box", 259, "special", "", "img/products/product36.jpg", 4.4, 11]
];

foreach ($products as $p) {
    $product_name = mysqli_real_escape_string($conn, $p[0]);
    $product_image = mysqli_real_escape_string($conn, $p[4]);
    $check = "SELECT 1 FROM products_table WHERE product_name = '$product_name'";
    $result = mysqli_query($conn, $check);

    if (mysqli_num_rows($result) == 0) {
        $query = "INSERT INTO products_table (product_name, price, category, occasion, product_image, rating, reviews) VALUES ('$product_name', $p[1], '$p[2]', '$p[3]', '$product_image', $p[5], $p[6])";
        mysqli_query($conn, $query);
    }
}
?>
