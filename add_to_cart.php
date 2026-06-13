<?php
include "include/session.php";
include "include/db_connect.php";

if ($_SESSION['role'] !== 'user') {
    header('Location: main_menu_admin.php');
    exit;
}

if (isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];

    $result = mysqli_query($conn, "SELECT * FROM products_table WHERE id = $product_id AND trash = 'no'");
    if ($row = mysqli_fetch_assoc($result)) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['qty']++;
        } else {
            $_SESSION['cart'][$product_id] = [
                'id'    => $product_id,
                'name'  => $row['product_name'],
                'price' => (float)$row['price'],
                'image' => $row['product_image'],
                'qty'   => 1
            ];
        }
        $_SESSION['alert'] = ['success' => '<strong>' . htmlspecialchars($row['product_name']) . '</strong> added to your basket.'];
    }
}

mysqli_close($conn);
$redirect = $_POST['redirect'] ?? 'products.php';
header("Location: $redirect");
exit;
