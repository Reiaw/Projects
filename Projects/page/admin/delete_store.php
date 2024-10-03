<?php
include('../../config/db.php');

if (isset($_GET['store_id'])) {
    $store_id = mysqli_real_escape_string($conn, $_GET['store_id']);

    // Check if any users are associated with this store
    $user_check_query = "SELECT * FROM users WHERE store_id = '$store_id'";
    $user_check_result = mysqli_query($conn, $user_check_query);

    if (mysqli_num_rows($user_check_result) > 0) {
        echo "<script>
            alert('Cannot delete store. Users are associated with this store.');
            window.location.href = 'manage_stores.php';
            </script>";
    } else {
        // If no users are associated, delete the store
        $delete_store_query = "DELETE FROM stores WHERE store_id = '$store_id'";
        if (mysqli_query($conn, $delete_store_query)) {
            echo "<script>
                alert('Store deleted successfully!');
                window.location.href = 'manage_stores.php';
                </script>";
        } else {
            echo "<script>
                alert('Error deleting store: " . mysqli_error($conn) . "');
                window.location.href = 'manage_stores.php';
                </script>";
        }
    }
}
?>
