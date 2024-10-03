<?php
include('../../config/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $tel_user = $_POST['tel_user'];
    $role = $_POST['role'];
    $store_id = $_POST['store_id'] ? $_POST['store_id'] : NULL;

    // Validate phone number
    if (!preg_match('/^[0-9]{10}$/', $tel_user)) {
        echo json_encode(['success' => false, 'message' => 'Invalid phone number. Please enter 10 digits only.']);
        exit;
    }

    $sql = "UPDATE users SET name = ?, surname = ?, tel_user = ?, role = ?, store_id = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $name, $surname, $tel_user, $role, $store_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating user']);
    }
}
?>