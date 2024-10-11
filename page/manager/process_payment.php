<?php
require_once('../../vendor/autoload.php');
require_once('../../config/db.php');

\Stripe\Stripe::setApiKey('sk_test_51Q8Gj8CLFIieIhW4C3c2ufG5TQxuNERogYLnKYylBEnjg1QXZUQpZVAmyqzKO9SvbC84KV0u6YMYX1SIeiC8CEDC00r1ap5dOd');

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $payment_method = $input['payment_method'];
    $amount = $input['amount'];
    $order_id = $input['order_id'];

    try {
        if ($payment_method === 'card') {
            $payment_intent = \Stripe\PaymentIntent::create([
                'amount' => $amount * 100, // Stripe uses cents
                'currency' => 'thb',
                'payment_method_types' => ['card'],
            ]);

            $client_secret = $payment_intent->client_secret;
        } elseif ($payment_method === 'promptpay') {
            $payment_intent = \Stripe\PaymentIntent::create([
                'amount' => $amount * 100, // Stripe uses cents, amount in satang
                'currency' => 'thb',
                'payment_method_types' => ['promtpay'],
            ]);
        
            $client_secret = $payment_intent->client_secret;
           
        } else {
            throw new Exception('Invalid payment method');
        }

        // Insert payment record
        $stmt = $conn->prepare("INSERT INTO payments (order_id, payment_date, payment_method, amount) VALUES (?, NOW(), ?, ?)");
        $stmt->bind_param("isd", $order_id, $payment_method, $amount);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Update order status
            $update_stmt = $conn->prepare("UPDATE orders SET order_status = 'paid' WHERE order_id = ?");
            $update_stmt->bind_param("i", $order_id);
            $update_stmt->execute();

            echo json_encode(['success' => true, 'client_secret' => $client_secret]);
        } else {
            throw new Exception('Failed to insert payment record');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>