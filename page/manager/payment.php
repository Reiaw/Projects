<?php
session_start();
include('../../config/db.php');

if (!isset($_GET['order_id'])) {
    header('Location: order.php');
    exit();
}

$order_id = $_GET['order_id'];

// Fetch order details
$stmt = $conn->prepare("SELECT total_amount FROM orders WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo "Order not found";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Store Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Payment</h2>
        <p>Total Amount: ฿<?php echo number_format($order['total_amount'], 2); ?></p>
        
        <div class="form-check mb-3">
            <input class="form-check-input" type="radio" name="paymentMethod" id="cardPayment" value="card" checked>
            <label class="form-check-label" for="cardPayment">
                Credit Card
            </label>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="radio" name="paymentMethod" id="promptpayPayment" value="promptpay">
            <label class="form-check-label" for="promptpayPayment">
                PromptPay
            </label>
        </div>

        <div id="card-element" class="mb-3">
            <!-- Stripe Card Element will be inserted here -->
        </div>

        <div id="promptpay-element" class="mb-3" style="display:none;">
            <!-- PromptPay QR code will be displayed here -->
        </div>

        <button id="submit-payment" class="btn btn-primary">Pay Now</button>
        <div id="payment-result" class="mt-3"></div>
    </div>

    <script>
        var stripe = Stripe('pk_test_51Q8Gj8CLFIieIhW44yBjAb8BkOWg6rpXaLt3qjVHRvaj9onmLE4Df66nER0wYzthOWSPuu8Bqwp99Ja6QjTJeXnj00vIqRsdV7');
        var elements = stripe.elements();
        var cardElement = elements.create('card');
        cardElement.mount('#card-element');

        var paymentMethod = 'card';
        var orderId = <?php echo $order_id; ?>;
        var amount = <?php echo $order['total_amount']; ?>;

        

        document.querySelectorAll('input[name="paymentMethod"]').forEach(function(elem) {
            elem.addEventListener('change', function(event) {
                paymentMethod = event.target.value;
                if (paymentMethod === 'card') {
                    document.getElementById('card-element').style.display = 'block';
                    document.getElementById('promptpay-element').style.display = 'none';
                } else {
                    document.getElementById('card-element').style.display = 'none';
                    document.getElementById('promptpay-element').style.display = 'block';
                }
            });
        });

        document.getElementById('submit-payment').addEventListener('click', function() {
            fetch('process_payment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    payment_method: paymentMethod,
                    amount: amount,
                    order_id: orderId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (paymentMethod === 'card') {
                        stripe.confirmCardPayment(data.client_secret, {
                            payment_method: { card: cardElement }
                        }).then(function(result) {
                            if (result.error) {
                                document.getElementById('payment-result').textContent = result.error.message;
                            } else {
                                document.getElementById('payment-result').textContent = 'Payment successful!';
                                // Redirect to order confirmation page
                                window.location.href = 'order_confirmation.php?order_id=' + orderId;
                            }
                        });
                    } else if (paymentMethod === 'promptpay') {
                        // Display PromptPay QR code
                        document.getElementById('promptpay-element').innerHTML = '<img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' + data.client_secret + '">';
                        document.getElementById('payment-result').textContent = 'Scan the QR code to pay with PromptPay';
                    }
                } else {
                    document.getElementById('payment-result').textContent = data.error;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('payment-result').textContent = 'An error occurred. Please try again.';
            });
        });
    </script>
</body>
</html>