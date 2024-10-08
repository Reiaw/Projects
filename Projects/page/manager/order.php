<?php
session_start();
include('../../config/db.php');


if ($_SESSION['role'] !== 'manager' || $_SESSION['store_id'] === null) {
    header('Location: ../../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$store_id = $_SESSION['store_id'];

// Fetch user and store information
$query = "SELECT u.name, u.surname, u.role, s.store_name 
          FROM users u
          LEFT JOIN stores s ON u.store_id = s.store_id 
          WHERE u.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $name = $user['name'];
    $surname = $user['surname'];
    $role = $user['role'];
    $store_name = $user['store_name'];
} else {
    header("Location: ../../auth/login.php");
    exit();
}

$query = "SELECT listproduct_id, product_name, category, price_set, quantity_set, product_pic, product_info 
          FROM products_info 
          WHERE visible = 1 
          ORDER BY category, product_name";
$result = $conn->query($query);
$products = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OrderPage-Store Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./respontive.css">
    <style>
        .product-card {
            height: 100%;
        }
        .product-image {
            height: 200px;
            object-fit: cover;
        }
        #order-summary {
            position: sticky;
            top: 76px;
        }
    </style>
</head>
<body>
    <button id="menu-toggle">☰</button>
    <header id="banner">
        <a id="user-info">Name: <?php echo $name . ' ' . $surname; ?> | Role: <?php echo $role; ?>
        <?php if (!is_null($store_id)) { ?> 
            | Store: <?php echo $store_name; ?> 
        <?php } ?>
        </a>
        <button class="btn btn-danger" onclick="window.location.href='../../auth/logout.php'">Log Out</button>
    </header>
    <div id="sidebar">
        <h4 class="text-center">Menu</h4>
        <a href="dashboard.php">Dashboard</a>
        <a href="show_user.php">Show User</a>
        <a href="order.php">Order</a>
        <a href="tracking.php">Tracking</a>
        <a href="scaning_product.php">Scaning Product</a>
        <a href="inventory.php">Inventory</a>
        <a href="reports_ploblem.php">Reports Ploblem</a>
        <a href="reports.php">Reports </a>
    </div>
    <div id="main-content">
        <h2 class="mb-4">Order Products</h2>
        <div class="row">
            <div class="col-md-9">
                <div class="row" id="product-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card product-card">
                                <img src="../picture_product/<?php echo htmlspecialchars($product['product_pic']); ?>" class="card-img-top product-image" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                                    <p class="card-text">
                                        หมวดหมู่: <?php echo htmlspecialchars($product['category']); ?><br>
                                        ราคา: ฿<?php echo number_format($product['price_set'], 2); ?><br>
                                        จำนวน: <?php echo htmlspecialchars($product['quantity_set']); ?>
                                    </p>
                                    <button class="btn btn-primary btn-sm view-details" data-product-id="<?php echo $product['listproduct_id']; ?>">View Details</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-3">
                <div id="order-summary" class="card">
                    <div class="card-header">
                        <h3>Order Summary</h3>
                    </div>
                    <div class="card-body">
                        <ul id="order-items" class="list-group list-group-flush">
                            <!-- Order items will be dynamically added here -->
                        </ul>
                    </div>
                    <div class="card-footer">
                        <h4>Total: ฿<span id="order-total">0.00</span></h4>
                        <button id="confirm-order" class="btn btn-success btn-block mt-3">Confirm Order</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details Modal -->
    <div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Product Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <img id="modal-product-image" src="" alt="Product Image" class="img-fluid mb-3">
                    <h4 id="modal-product-name"></h4>
                    <p id="modal-product-category"></p>
                    <p id="modal-product-price"></p>
                    <p id="modal-product-quantity-set"></p>
                    <p id="modal-product-info"></p>
                    <div class="form-group">
                        <label for="modal-product-quantity">จำนวนเชตที่ต้องการ:</label>
                        <input type="number" class="form-control" id="modal-product-quantity" min="1" value="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="add-to-order">Add to Order</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Confirm Order</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h6>Order Details:</h6>
                    <ul id="modal-order-items"></ul>
                    <h6>Total: ฿<span id="modal-order-total"></span></h6>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submit-order">Submit Order</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            let orderItems = {};
            let total = 0;
            let products = <?php echo json_encode($products); ?>;

            function updateOrderSummary() {
                let $orderItems = $('#order-items');
                $orderItems.empty();
                total = 0;

                for (let productId in orderItems) {
                    let item = orderItems[productId];
                    let itemTotal = item.price * item.quantity;
                    total += itemTotal;

                    $orderItems.append(`
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ${item.name} (x${item.quantity})
                            <span>
                                ฿${itemTotal.toFixed(2)}
                                <button class="btn btn-danger btn-sm ml-2 remove-item" data-product-id="${productId}">Remove</button>
                            </span>
                        </li>
                    `);
                }

                $('#order-total').text(total.toFixed(2));
            }

            $('.view-details').click(function() {
                let productId = $(this).data('product-id');
                let product = products.find(p => p.listproduct_id == productId);
                
                $('#modal-product-image').attr('src', '../picture_product/' + product.product_pic);
                $('#modal-product-name').text(product.product_name);
                $('#modal-product-category').text('หมวดหมู: ' + product.category);
                $('#modal-product-price').text('ราคา: ฿' + parseFloat(product.price_set).toFixed(2));
                $('#modal-product-quantity-set').text('จำนวนสินค้า: ' + product.quantity_set);
                $('#modal-product-info').text(product.product_info);
                $('#modal-product-quantity').val(1);
                
                $('#add-to-order').data('product-id', productId);
                
                $('#productModal').modal('show');
            });

            $('#add-to-order').click(function() {
                let productId = $(this).data('product-id');
                let product = products.find(p => p.listproduct_id == productId);
                let quantity = parseInt($('#modal-product-quantity').val());

                if (orderItems[productId]) {
                    orderItems[productId].quantity += quantity;
                } else {
                    orderItems[productId] = {
                        name: product.product_name,
                        price: parseFloat(product.price_set),
                        quantity: quantity
                    };
                }

                updateOrderSummary();
                $('#productModal').modal('hide');
            });

            $(document).on('click', '.remove-item', function() {
                let productId = $(this).data('product-id');
                delete orderItems[productId];
                updateOrderSummary();
            });

            $('#confirm-order').click(function() {
                let $modalOrderItems = $('#modal-order-items');
                $modalOrderItems.empty();

                for (let productId in orderItems) {
                    let item = orderItems[productId];
                    let itemTotal = item.price * item.quantity;
                    $modalOrderItems.append(`<li>${item.name} (x${item.quantity}) - ฿${itemTotal.toFixed(2)}</li>`);
                }

                $('#modal-order-total').text(total.toFixed(2));
                $('#confirmationModal').modal('show');
            });

            $('#submit-order').click(function() {
                $.ajax({
                    url: 'submit_order.php',
                    method: 'POST',
                    data: {
                        order_items: JSON.stringify(orderItems),
                        total: total,
                        store_id: <?php echo $store_id; ?>
                    },
                    success: function(response) {
                        alert('Order submitted successfully!');
                        orderItems = {};
                        updateOrderSummary();
                        $('#confirmationModal').modal('hide');
                    },
                    error: function() {
                        alert('There was an error submitting your order. Please try again.');
                    }
                });
            });
        });

        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('main-content').classList.toggle('sidebar-active');
        });
    </script>
</body>
</html>