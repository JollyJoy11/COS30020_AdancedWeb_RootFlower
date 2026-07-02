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

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'remove') {
        $id = (int)$_POST['product_id'];
        unset($_SESSION['cart'][$id]);
        $_SESSION['alert'] = ['info' => 'Item removed from basket.'];
        mysqli_close($conn);
        header('Location: cart.php');
        exit;
    }

    if ($action === 'update') {
        $id  = (int)$_POST['product_id'];
        $qty = max(0, (int)$_POST['qty']);
        if ($qty === 0) {
            unset($_SESSION['cart'][$id]);
        } elseif (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['qty'] = $qty;
        }
        mysqli_close($conn);
        header('Location: cart.php');
        exit;
    }

    if ($action === 'checkout' && !empty($_SESSION['cart'])) {
        $email    = mysqli_real_escape_string($conn, $_SESSION['user']);
        $subtotal = 0;
        foreach ($_SESSION['cart'] as $item) {
            $subtotal += $item['price'] * $item['qty'];
        }
        $delivery    = $subtotal >= 300 ? 0 : 20;
        $grand_total = $subtotal + $delivery;

        mysqli_query($conn, "INSERT INTO orders_table (email, total, delivery) VALUES ('$email', $grand_total, $delivery)");
        $order_id = mysqli_insert_id($conn);

        foreach ($_SESSION['cart'] as $item) {
            $name  = mysqli_real_escape_string($conn, $item['name']);
            $image = mysqli_real_escape_string($conn, $item['image']);
            mysqli_query($conn, "INSERT INTO order_items_table (order_id, product_id, product_name, price, qty, image)
                VALUES ($order_id, {$item['id']}, '$name', {$item['price']}, {$item['qty']}, '$image')");
        }

        // Build and send order confirmation email
        $orderNum  = str_pad($order_id, 6, '0', STR_PAD_LEFT);
        $itemLines = "";
        foreach ($_SESSION['cart'] as $item) {
            $itemLines .= "  - " . $item['name'] . " x" . $item['qty'] . "  —  RM " . number_format($item['price'] * $item['qty'], 2) . "\n";
        }
        $deliveryLine  = $delivery == 0 ? "FREE (order above RM 300)" : "RM " . number_format($delivery, 2);
        $receiptBody   =
            "Hi " . $_SESSION['name'] . ",\n\n" .
            "Thank you for your order! Here's your order confirmation:\n\n" .
            "Order #: $orderNum\n" .
            "Date   : " . date('d M Y') . "\n\n" .
            "Items:\n$itemLines\n" .
            "Subtotal : RM " . number_format($subtotal, 2) . "\n" .
            "Delivery : $deliveryLine\n" .
            "Total    : RM " . number_format($grand_total, 2) . "\n\n" .
            "We'll process your order soon. Thank you for shopping with Root Flower!\n\n" .
            "Root Flower Team";

        sendEmail($_SESSION['user'], "Your Root Flower Order #$orderNum", $receiptBody);

        $_SESSION['cart'] = [];
        $_SESSION['alert'] = ['success' => 'Your order has been placed! A confirmation email with your order details has been sent to your inbox.'];
        mysqli_close($conn);
        header('Location: order_history.php');
        exit;
    }
}

$cart     = $_SESSION['cart'] ?? [];
$total    = 0;
$count    = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['qty'];
    $count += $item['qty'];
}
$delivery    = $total >= 300 ? 0 : 20;
$grand_total = $total + $delivery;
$remaining   = max(0, 300 - $total);

mysqli_close($conn);

$articleClass = empty($cart)
    ? "p-5 d-flex flex-column justify-content-center align-items-center text-center"
    : "p-4 p-md-5";
?>

<!DOCTYPE html>

<html lang="en">
<!-- Description: Cart -->
<!-- Author: Joanne Chin Jia Xuan -->
<!-- Date: 5/10/2025 -->
<!-- Validated: OK 6/10/2025 -->

<head>
    <title>Shopping Bag | Root Flower</title>
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

    <article class="<?= $articleClass ?>" id="cart-article">
        <?php if (empty($cart)): ?>
            <i class="bi bi-basket fs-1 text-secondary mb-3"></i>
            <h1 class="fs-4">Your Shopping Bag Is Empty</h1>
            <p class="small pb-3">Start exploring our products and fill your bag with something you love today.</p>
            <a href="products.php" class="btn btn-primary">Continue Shopping</a>

        <?php else: ?>
            <div class="row g-4 g-lg-5">

                <!-- Cart Items -->
                <div class="col-lg-8">
                    <h1 class="fs-3 mb-4">Shopping Bag <span class="fs-6 fw-normal text-muted">(<?= $count ?> item<?= $count !== 1 ? 's' : '' ?>)</span></h1>

                    <?php foreach ($cart as $item): ?>
                    <div class="d-flex gap-3 border-bottom py-3 align-items-center">
                        <figure class="m-0 flex-shrink-0 overflow-hidden rounded" style="width:90px;height:90px;">
                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-100 h-100" style="object-fit:cover;">
                        </figure>

                        <div class="flex-grow-1">
                            <p class="fw-bold mb-0 fs-6"><?= htmlspecialchars($item['name']) ?></p>
                            <p class="small text-muted mb-0">RM <?= number_format($item['price'], 2) ?> each</p>
                        </div>

                        <!-- Qty controls -->
                        <div class="d-flex align-items-center gap-1">
                            <form method="POST" action="cart.php">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="qty" value="<?= $item['qty'] - 1 ?>">
                                <button type="submit" class="btn btn-sm py-0 px-2">-</button>
                            </form>

                            <span class="px-2 fs-6"><?= $item['qty'] ?></span>

                            <form method="POST" action="cart.php">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="qty" value="<?= $item['qty'] + 1 ?>">
                                <button type="submit" class="btn btn-sm py-0 px-2">+</button>
                            </form>
                        </div>

                        <p class="fw-bold mb-0 text-nowrap fs-6">RM <?= number_format($item['price'] * $item['qty'], 2) ?></p>

                        <!-- Remove -->
                        <form method="POST" action="cart.php">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn border-0 text-muted py-0 px-1" title="Remove">
                                <i class="bi bi-x-lg fs-6"></i>
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>

                    <a href="products.php" class="btn mt-4 small"><i class="bi bi-arrow-left me-1"></i>Continue Shopping</a>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="border p-4">
                        <h2 class="fs-5 mb-3">Order Summary</h2>

                        <div class="d-flex justify-content-between mb-2 fs-6">
                            <span>Subtotal (<?= $count ?> item<?= $count !== 1 ? 's' : '' ?>)</span>
                            <span>RM <?= number_format($total, 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-1 fs-6">
                            <span>Delivery</span>
                            <span><?= $delivery == 0 ? '<span class="text-success">FREE</span>' : 'RM ' . number_format($delivery, 2) ?></span>
                        </div>
                        <?php if ($remaining > 0): ?>
                            <p class="small mb-3 text-center" style="color:#978475">Add RM <?= number_format($remaining, 2) ?> more for free delivery</p>
                        <?php else: ?>
                            <p class="small mb-3 text-success text-center">You qualify for free delivery!</p>
                        <?php endif; ?>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold mb-4">
                            <span>Total</span>
                            <span>RM <?= number_format($grand_total, 2) ?></span>
                        </div>

                        <form method="POST" action="cart.php">
                            <input type="hidden" name="action" value="checkout">
                            <button type="submit" class="btn btn-primary w-100">Place Order</button>
                        </form>
                    </div>
                </div>

            </div>
        <?php endif; ?>
    </article>

	<?php include "include/footer.php"; ?>

<script src="js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>
</html>
