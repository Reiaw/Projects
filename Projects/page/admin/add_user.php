<?php
include('../../config/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $tel_user = $_POST['tel_user'];
    $role = $_POST['role'];
    $store_id = $_POST['store_id'] ? $_POST['store_id'] : NULL;
    $reset_password = 1;

    // Validate phone number
    if (!preg_match('/^[0-9]{10}$/', $tel_user)) {
        echo json_encode(['success' => false, 'message' => 'Invalid phone number. Please enter 10 digits only.']);
        exit;
    }

    // Check if email already exists
    $check_email = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }

    // Insert new user
    $sql = "INSERT INTO users (name, surname, email, password, tel_user, role, store_id, reset_password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssis", $name, $surname, $email, $password, $tel_user, $role, $store_id, $reset_password);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding user']);
    }
}
?>