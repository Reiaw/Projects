<?php
include('../../config/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $store_name = mysqli_real_escape_string($conn, $_POST['store_name']);
    $tel_store = mysqli_real_escape_string($conn, $_POST['tel_store']);
    $street = mysqli_real_escape_string($conn, $_POST['street']);
    $district = mysqli_real_escape_string($conn, $_POST['district']);
    $province = mysqli_real_escape_string($conn, $_POST['province']);
    $postal_code = mysqli_real_escape_string($conn, $_POST['postal_code']);

    // Check if store_name is unique
    $check_query = "SELECT * FROM stores WHERE store_name = '$store_name'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>
            alert('Store name already exists. Please choose another name.');
            window.location.href = 'manage_stores.php'; // Redirect back to manage_stores page
            </script>";
    } else {
        // Insert the address first
        $location_query = "INSERT INTO address (street, district, province, postal_code) 
                          VALUES ('$street', '$district', '$province', '$postal_code')";
        if (mysqli_query($conn, $location_query)) {
            $location_id = mysqli_insert_id($conn);

            // Now insert the store with the location_id
            $store_query = "INSERT INTO stores (store_name, tel_store, location_id) 
                            VALUES ('$store_name', '$tel_store', '$location_id')";
            if (mysqli_query($conn, $store_query)) {
                echo "<script>
                    alert('Store added successfully!');
                    window.location.href = 'manage_stores.php'; // Redirect to manage_stores page
                    </script>";
            } else {
                echo "<script>
                    alert('Error adding store: " . mysqli_error($conn) . "');
                    window.location.href = 'manage_stores.php'; 
                    </script>";
            }
        } else {
            echo "<script>
                alert('Error adding location: " . mysqli_error($conn) . "');
                window.location.href = 'manage_stores.php';
                </script>";
        }
    }
}
?>
