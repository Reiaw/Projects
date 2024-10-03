<?php
include('../../config/db.php');

// Fetch all stores along with their location
$query = "SELECT s.store_id, s.store_name, s.tel_store, a.street, a.district, a.province, a.postal_code, s.update_at
          FROM stores s
          JOIN address a ON s.location_id = a.location_id";  // ใช้ address และ location_id
$stores = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Stores</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1>Manage Stores</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>Store ID</th>
                    <th>Store Name</th>
                    <th>Tel</th>
                    <th>Address</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($stores)) { ?>
                    <tr>
                        <td><?= $row['store_id'] ?></td>
                        <td><?= $row['store_name'] ?></td>
                        <td><?= $row['tel_store'] ?></td>
                        <td><?= $row['street'] . ', ' . $row['district'] . ', ' . $row['province'] . ' ' . $row['postal_code'] ?></td>
                        <td><?= $row['update_at'] ?></td>
                        <td>
                            <button class="btn btn-primary edit-btn" data-toggle="modal" data-target="#editStoreModal" 
                                    data-id="<?= $row['store_id'] ?>" data-name="<?= $row['store_name'] ?>" 
                                    data-tel="<?= $row['tel_store'] ?>" data-street="<?= $row['street'] ?>" 
                                    data-district="<?= $row['district'] ?>" data-province="<?= $row['province'] ?>" 
                                    data-postal="<?= $row['postal_code'] ?>">Edit</button>
                            <button class="btn btn-danger delete-btn" data-id="<?= $row['store_id'] ?>">Delete</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Button to trigger Add Store modal -->
        <button class="btn btn-success" data-toggle="modal" data-target="#addStoreModal">Add Store</button>

        <!-- Add Store Modal -->
        <div class="modal fade" id="addStoreModal" tabindex="-1" role="dialog" aria-labelledby="addStoreModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="add_store.php" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addStoreModalLabel">Add Store</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="store_name">Store Name</label>
                                <input type="text" class="form-control" name="store_name" required>
                            </div>
                            <div class="form-group">
                                <label for="tel_store">Telephone</label>
                                <input type="text" class="form-control" name="tel_store" required>
                            </div>
                            <div class="form-group">
                                <label for="street">Street</label>
                                <input type="text" class="form-control" name="street" required>
                            </div>
                            <div class="form-group">
                                <label for="district">District</label>
                                <input type="text" class="form-control" name="district" required>
                            </div>
                            <div class="form-group">
                                <label for="province">Province</label>
                                <input type="text" class="form-control" name="province" required>
                            </div>
                            <div class="form-group">
                                <label for="postal_code">Postal Code</label>
                                <input type="text" class="form-control" name="postal_code" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Store</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Store Modal (this will be populated via JavaScript) -->
        <div class="modal fade" id="editStoreModal" tabindex="-1" role="dialog" aria-labelledby="editStoreModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="edit_store.php" method="POST" id="editForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editStoreModalLabel">Edit Store</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="store_id" id="edit_store_id">
                            <div class="form-group">
                                <label for="edit_store_name">Store Name</label>
                                <input type="text" class="form-control" name="store_name" id="edit_store_name" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_tel_store">Telephone</label>
                                <input type="text" class="form-control" name="tel_store" id="edit_tel_store" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_street">Street</label>
                                <input type="text" class="form-control" name="street" id="edit_street" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_district">District</label>
                                <input type="text" class="form-control" name="district" id="edit_district" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_province">Province</label>
                                <input type="text" class="form-control" name="province" id="edit_province" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_postal_code">Postal Code</label>
                                <input type="text" class="form-control" name="postal_code" id="edit_postal_code" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Update Store</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

    <script>
        // Fill the edit modal with the selected store's data
        $('.edit-btn').click(function() {
            var storeId = $(this).data('id');
            var storeName = $(this).data('name');
            var tel = $(this).data('tel');
            var street = $(this).data('street');
            var district = $(this).data('district');
            var province = $(this).data('province');
            var postal = $(this).data('postal');

            $('#edit_store_id').val(storeId);
            $('#edit_store_name').val(storeName);
            $('#edit_tel_store').val(tel);
            $('#edit_street').val(street);
            $('#edit_district').val(district);
            $('#edit_province').val(province);
            $('#edit_postal_code').val(postal);
        });

        // Handle delete button click
        $('.delete-btn').click(function() {
            var storeId = $(this).data('id');
            if (confirm('Are you sure you want to delete this store?')) {
                window.location.href = 'delete_store.php?store_id=' + storeId;
            }
        });
    </script>
</body>
</html>
