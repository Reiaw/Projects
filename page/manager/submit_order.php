<?php
session_start();
include('../../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_items = json_decode($_POST['order_items'], true);
    $total_amount = $_POST['total'];
    $store_id = $_POST['store_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert into orders table
        $stmt = $conn->prepare("INSERT INTO orders (store_id, order_status, total_amount, order_date) VALUES (?, 'pending_payment', ?, NOW())");
        $stmt->bind_param("id", $store_id, $total_amount);
        $stmt->execute();

        $order_id = $conn->insert_id;

        // Insert into detail_orders table
        $stmt = $conn->prepare("INSERT INTO detail_orders (order_id, listproduct_id, quantity_set, price) VALUES (?, ?, ?, ?)");

        foreach ($order_items as $product_id => $item) {
            $stmt->bind_param("iiid", $order_id, $product_id, $item['quantity'], $item['price']);
            $stmt->execute();
        }

        // Commit transaction
        $conn->commit();

        // Redirect to payment page
        header("Location: payment.php?order_id=" . $order_id);
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}
?>