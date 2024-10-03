<?php
session_start();
include('../../config/db.php');

// Query to get all users with their store information
$sql = "SELECT users.user_id, users.name, users.surname, users.email, users.tel_user, users.role, 
               IFNULL(stores.store_name, 'No Store') AS store_name, users.update_at
        FROM users
        LEFT JOIN stores ON users.store_id = stores.store_id";

$result = $conn->query($sql);

// Query to get all stores for the dropdowns
$storeQuery = "SELECT store_id, store_name FROM stores";
$storeResult = $conn->query($storeQuery);
$stores = $storeResult->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
    </style>
</head>
<body>
    <h1>User Management</h1>
    <button onclick="openAddUserModal()">Add User</button>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Surname</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Store</th>
            <th>Last Updated</th>
            <th>Actions</th>
        </tr>

        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['user_id']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['surname']}</td>
                        <td>{$row['email']}</td>
                        <td>{$row['tel_user']}</td>
                        <td>{$row['role']}</td>
                        <td>{$row['store_name']}</td>
                        <td>{$row['update_at']}</td>
                        <td>
                            <button onclick='openEditUserModal({$row['user_id']})'>Edit</button>
                            <button onclick='deleteUser({$row['user_id']})'>Delete</button>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='9'>No users found</td></tr>";
        }
        ?>
    </table>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <h2>Add New User</h2>
            <form id="addUserForm">
                <input type="text" name="name" placeholder="Name" required>
                <input type="text" name="surname" placeholder="Surname" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="password" placeholder="Password" required>
                <input type="tel" name="tel_user" placeholder="Phone (10 digits)" pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number" required>
                <select name="role" required>
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="staff">Staff</option>
                </select>
                <select name="store_id">
                    <option value="">No store</option>
                    <?php
                    foreach ($stores as $store) {
                        echo "<option value='{$store['store_id']}'>{$store['store_name']}</option>";
                    }
                    ?>
                </select>
                <button type="submit">Save</button>
                <button type="button" onclick="closeModal('addUserModal')">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <h2>Edit User</h2>
            <form id="editUserForm">
                <input type="hidden" name="user_id" id="edit_user_id">
                <input type="text" name="name" id="edit_name" required>
                <input type="text" name="surname" id="edit_surname" required>
                <input type="tel" name="tel_user" id="edit_tel_user" placeholder="Phone (10 digits)" pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number" required>
                <select name="role" id="edit_role" required>
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="staff">Staff</option>
                </select>
                <select name="store_id" id="edit_store_id">
                    <option value="">No store</option>
                    <?php
                    foreach ($stores as $store) {
                        echo "<option value='{$store['store_id']}'>{$store['store_name']}</option>";
                    }
                    ?>
                </select>
                <button type="submit">Save</button>
                <button type="button" onclick="closeModal('editUserModal')">Cancel</button>
            </form>
        </div>
    </div>
    <form action="/Projects/auth/logout.php" method="POST" style="display: inline;">
    <button type="submit">Logout</button>
    </form>

    <script>
    function openAddUserModal() {
        document.getElementById('addUserModal').style.display = 'block';
    }

    function openEditUserModal(userId) {
        // Fetch user data and populate the form
        fetch(`get_user.php?id=${userId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit_user_id').value = data.user_id;
                document.getElementById('edit_name').value = data.name;
                document.getElementById('edit_surname').value = data.surname;
                document.getElementById('edit_tel_user').value = data.tel_user;
                document.getElementById('edit_role').value = data.role;
                document.getElementById('edit_store_id').value = data.store_id;
                document.getElementById('editUserModal').style.display = 'block';
            });
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            fetch(`delete_user.php?id=${userId}`, { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('User deleted successfully');
                        location.reload();
                    } else {
                        alert('Error deleting user');
                    }
                });
        }
    }

    document.getElementById('addUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch('add_user.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User added successfully');
                location.reload();
            } else {
                alert(data.message || 'Error adding user');
            }
        });
    });

    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch('edit_user.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User updated successfully');
                location.reload();
            } else {
                alert('Error updating user');
            }
        });
    });
    </script>
</body>
</html>