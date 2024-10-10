<?php
session_start();
include('../../config/db.php');
require_once __DIR__ . '/../../vendor/autoload.php';
\Stripe\Stripe::setApiKey('sk_test_51Q8Gj8CLFIieIhW4C3c2ufG5TQxuNERogYLnKYylBEnjg1QXZUQpZVAmyqzKO9SvbC84KV0u6YMYX1SIeiC8CEDC00r1ap5dOd');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_items = json_decode($_POST['order_items'], true);
    $total = floatval($_POST['total']);
    $store_id = intval($_POST['store_id']);
    $payment_method = $_POST['payment_method'];

    // บันทึกคำสั่งซื้อลงในฐานข้อมูล
    $conn->begin_transaction();

    try {
        // เพิ่มข้อมูลในตาราง orders
        $query = "INSERT INTO orders (store_id, total_amount, order_status) VALUES (?, ?, 'pending_payment')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("id", $store_id, $total);
        $stmt->execute();
        $order_id = $conn->insert_id;

        // เพิ่มรายการสินค้าในคำสั่งซื้อ
        $query = "INSERT INTO detail_orders (order_id, listproduct_id, quantity_set, price) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        foreach ($order_items as $product_id => $item) {
            $stmt->bind_param("iiid", $order_id, $product_id, $item['quantity'], $item['price']);
            $stmt->execute();
        }

        // Commit transaction เมื่อทุกอย่างสำเร็จ
        $conn->commit();

        if ($payment_method === 'credit_card') {
            // สร้าง PaymentIntent ผ่าน Stripe API
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => intval($total * 100), // แปลงเป็นหน่วยสตางค์และทำให้เป็นจำนวนเต็ม
                'currency' => 'thb',
                'metadata' => ['order_id' => $order_id],
            ]);
            $clientSecret = $paymentIntent->client_secret;
        } else {
            $clientSecret = null;
        }
    
    } catch (Exception $e) {
        // Rollback transaction ถ้ามีข้อผิดพลาด
        $conn->rollback();
        die("เกิดข้อผิดพลาด: " . $e->getMessage());
    }

    $conn->close();
} else {
    die("Invalid request method");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ชำระเงิน - Store Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">ชำระเงิน</h1>
        <?php if ($payment_method === 'credit_card'): ?>
            <div id="card-payment-section">
                <h3>ชำระด้วยบัตรเครดิต/เดบิต</h3>
                <form id="payment-form">
                    <div id="card-element" class="mb-3">
                        <!-- Stripe จะใส่ฟอร์มกรอกข้อมูลบัตรที่นี่ -->
                    </div>
                    <div id="card-errors" role="alert" class="text-danger mb-3"></div>
                    <button id="submit" class="btn btn-primary">ชำระเงิน ฿<?php echo number_format($total, 2); ?></button>
                </form>
            </div>
        <?php else: ?>
            <div id="bank-transfer-section">
                <h3>โอนเงินผ่านธนาคาร</h3>
                <p>โปรดโอนเงินไปยังบัญชีธนาคารด้านล่าง:</p>
                <ul>
                    <li>ธนาคาร: ธนาคารกสิกรไทย</li>
                    <li>ชื่อบัญชี: บริษัท สโตร์ แมเนจเมนท์ จำกัด</li>
                    <li>เลขที่บัญชี: 123-4-56789-0</li>
                    <li>จำนวนเงิน: ฿<?php echo number_format($total, 2); ?></li>
                </ul>
                <p>หลังจากโอนเงินเรียบร้อยแล้ว กรุณาอัพโหลดหลักฐานการโอนเงิน:</p>
                <form id="bank-transfer-form" action="process_bank_transfer.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                    <div class="form-group">
                        <input type="file" name="transfer_slip" class="form-control-file" required>
                    </div>
                    <button type="submit" class="btn btn-primary">อัพโหลดหลักฐานการโอนเงิน</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        <?php if ($payment_method === 'credit_card'): ?>
        var stripe = Stripe('pk_test_51Q8Gj8CLFIieIhW44yBjAb8BkOWg6rpXaLt3qjVHRvaj9onmLE4Df66nER0wYzthOWSPuu8Bqwp99Ja6QjTJeXnj00vIqRsdV7');
        var elements = stripe.elements();
        var card = elements.create('card');
        card.mount('#card-element');

        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            stripe.confirmCardPayment("<?php echo $clientSecret; ?>", {
                payment_method: {
                    card: card
                }
            }).then(function(result) {
                if (result.error) {
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                } else {
                    // การชำระเงินสำเร็จ
                    savePaymentInfo(<?php echo $order_id; ?>, <?php echo $total; ?>, 'credit_card');
                }
            });
        });

        function savePaymentInfo(orderId, amount, paymentMethod) {
            $.ajax({
                url: 'save_payment.php',
                method: 'POST',
                data: {
                    order_id: orderId,
                    amount: amount,
                    payment_method: paymentMethod
                },
                success: function(response) {
                    alert('การชำระเงินสำเร็จ!');
                    window.location.href = 'order.php';
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการบันทึกข้อมูลการชำระเงิน');
                }
            });
        }
        <?php endif; ?>
    </script>
</body>
</html>