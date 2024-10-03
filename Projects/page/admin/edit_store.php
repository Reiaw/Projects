<?php
include('../../config/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $store_id = mysqli_real_escape_string($conn, $_POST['store_id']);
    $store_name = mysqli_real_escape_string($conn, $_POST['store_name']);
    $tel_store = mysqli_real_escape_string($conn, $_POST['tel_store']);
    $street = mysqli_real_escape_string($conn, $_POST['street']);
    $district = mysqli_real_escape_string($conn, $_POST['district']);
    $province = mysqli_real_escape_string($conn, $_POST['province']);
    $postal_code = mysqli_real_escape_string($conn, $_POST['postal_code']);

    // Check if the new store_name is unique (excluding the current store)
    $check_query = "SELECT * FROM stores WHERE store_name = '$store_name' AND store_id != '$store_id'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>
            alert('Store name already exists. Please choose another name.');
            window.location.href = 'manage_stores.php';
            </script>";
    } else {
        // First update the address
        $update_location_query = "UPDATE address a 
                                  JOIN stores s ON s.location_id = a.location_id
                                  SET a.street = '$street', a.district = '$district', 
                                      a.province = '$province', a.postal_code = '$postal_code'
                                  WHERE s.store_id = '$store_id'";
        if (mysqli_query($conn, $update_location_query)) {
            // Then update the store details
            $update_store_query = "UPDATE stores 
                                   SET store_name = '$store_name', tel_store = '$tel_store' 
                                   WHERE store_id = '$store_id'";
            if (mysqli_query($conn, $update_store_query)) {
                echo "<script>
                    alert('Store updated successfully!');
                    window.location.href = 'manage_stores.php';
                    </script>";
            } else {
                echo "<script>
                    alert('Error updating store: " . mysqli_error($conn) . "');
                    window.location.href = 'manage_stores.php';
                    </script>";
            }
        } else {
            echo "<script>
                alert('Error updating location: " . mysqli_error($conn) . "');
                window.location.href = 'manage_stores.php';
                </script>";
        }
    }
}
?>
