<?php
include('../../config/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_GET['id'];

    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>