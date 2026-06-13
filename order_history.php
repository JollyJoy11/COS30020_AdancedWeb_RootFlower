<?php
include "include/session.php";
include "include/db_connect.php";

if ($_SESSION['role'] !== 'user') {
    header('Location: main_menu_admin.php');
    exit;
}

$notification_data = displayNotification($conn, $_SESSION['user']);
$unread_notifications = $notification_data['notifications'];
$unread_count = $notification_data['count'];

$email = mysqli_real_escape_string($conn, $_SESSION['user']);
$orders_result = mysqli_query($conn, "SELECT * FROM orders_table WHERE email = '$email' ORDER BY created_at DESC");

$orders = [];
while ($order = mysqli_fetch_assoc($orders_result)) {
    $oid = (int)$order['id'];
    $items_result = mysqli_query($conn, "SELECT * FROM order_items_table WHERE order_id = $oid");
    $order['items'] = [];
    while ($item = mysqli_fetch_assoc($items_result)) {
        $order['items'][] = $item;
    }
    $orders[] = $order;
}

mysqli_close($conn);

$articleClass = empty($orders)
    ? "p-5 d-flex flex-column justify-content-center align-items-center text-center"
    : "p-4 p-md-5";
?>

<!DOCTYPE html>

<html lang="en">
<!-- Description: Order History -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 5/10/2025 -->
<!-- Validated: OK 6/10/2025 -->

<head>
    <title>Order History | Root Flower</title>
	<meta charset="utf-8"/>
	<meta name="author" content="Joanne Chin Jia Xuan"/>
	<meta name="description" content="Root Flower is a creative florist hub offering fresh floral products, inspiring workshops, and a platform for students to showcase their floral artistry. Discover, learn, and create with us.">
	<meta name="keywords" content="Root Flower, florist, kuching florist, flower, flower bouquet, florist workshop"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<link rel="icon" type="image/x-icon" href="img/favicon.ico"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">
	<link rel="stylesheet" type="text/css" href="style/style.css"/>
</head>

<body class="fs-5">
	<?php include "include/header.php"; ?>

    <article class="<?= $articleClass ?>" id="history-article">
        <?php if (empty($orders)): ?>
            <i class="bi bi-bag fs-1 text-secondary mb-3"></i>
            <h1 class="fs-4">No Purchases Yet</h1>
            <p class="small pb-3">Browse our products and make your first purchase today.</p>
            <a href="products.php" class="btn btn-primary">Shop Now</a>

        <?php else: ?>
            <h1 class="fs-3 mb-4">Order History</h1>

            <?php foreach ($orders as $order): ?>
            <div class="border rounded mb-4 overflow-hidden">
                <!-- Order header -->
                <div class="d-flex flex-wrap justify-content-between align-items-center px-4 py-3 bg-light gap-2">
                    <div class="fs-6">
                        <span class="text-muted small">Order placed</span><br>
                        <strong><?= date('d M Y', strtotime($order['created_at'])) ?></strong>
                    </div>
                    <div class="fs-6">
                        <span class="text-muted small">Total (incl. delivery)</span><br>
                        <strong>RM <?= number_format($order['total'], 2) ?></strong>
                    </div>
                    <div class="fs-6">
                        <span class="text-muted small">Status</span><br>
                        <span class="badge rounded-pill" style="background-color:#978475"><?= ucfirst($order['status']) ?></span>
                    </div>
                    <div class="fs-6 text-muted small">
                        Order #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?>
                    </div>
                </div>

                <!-- Order items -->
                <?php foreach ($order['items'] as $item): ?>
                <div class="d-flex align-items-center gap-3 px-4 py-3 border-top">
                    <figure class="m-0 flex-shrink-0 overflow-hidden rounded" style="width:70px;height:70px;">
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="w-100 h-100" style="object-fit:cover;">
                    </figure>
                    <div class="flex-grow-1">
                        <p class="mb-0 fs-6 fw-bold"><?= htmlspecialchars($item['product_name']) ?></p>
                        <p class="mb-0 small text-muted">Qty: <?= $item['qty'] ?> &times; RM <?= number_format($item['price'], 2) ?></p>
                    </div>
                    <p class="mb-0 fw-bold fs-6 text-nowrap">RM <?= number_format($item['price'] * $item['qty'], 2) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>

            <a href="products.php" class="btn btn-primary mt-2">Continue Shopping</a>
        <?php endif; ?>
    </article>

	<?php include "include/footer.php"; ?>

<script src="js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>
</html>
