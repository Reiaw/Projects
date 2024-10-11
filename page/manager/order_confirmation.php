<?php
session_start();
include('../../config/db.php');

if (!isset($_GET['order_id'])) {
    header('Location: order.php');
    exit();
}

$order_id = $_GET['order_id'];

// Fetch order details
$stmt = $conn->prepare("SELECT o.*, p.payment_method, p.payment_date 
                        FROM orders o 
                        LEFT JOIN payments p ON o.order_id = p.order_id 
                        WHERE o.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo "Order not found";
    exit();
}

// Fetch order items
$stmt = $conn->prepare("SELECT d.*, pi.product_name 
                        FROM detail_orders d 
                        JOIN products_info pi ON d.listproduct_id = pi.listproduct_id 
                        WHERE d.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Store Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Order Confirmation</h2>
        <p>Order ID: <?php echo $order['order_id']; ?></p>
        <p>Order Status: <?php echo $order['order_status']; ?></p>
        <p>Total Amount: ฿<?php echo number_format($order['total_amount'], 2); ?></p>
        <p>Order Date: <?php echo $order['order_date']; ?></p>
        <p>Payment Method: <?php echo $order['payment_method']; ?></p>
        <p>Payment Date: <?php echo $order['payment_date']; ?></p>

        <h3>Order Items</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td><?php echo $item['product_name']; ?></td>
                        <td><?php echo $item['quantity_set']; ?></td>
                        <td>฿<?php echo number_format($item['price'], 2); ?></td>
                        <td>฿<?php echo number_format($item['quantity_set'] * $item['price'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="order.php" class="btn btn-primary">Back to Orders</a>
    </div>
</body>
</html>