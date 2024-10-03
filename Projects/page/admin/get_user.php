<?php
session_start();
include('../../config/db.php');

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    $sql = "SELECT user_id, name, surname, email, tel_user, role, store_id FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        echo json_encode($user);
    } else {
        echo json_encode(['error' => 'User not found']);
    }
}
?>