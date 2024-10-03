<?php
session_start();
include('../config/db.php'); // เชื่อมต่อกับฐานข้อมูล

$error = "";
$success = "";

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // ตรวจสอบว่ารหัสผ่านใหม่และยืนยันรหัสผ่านตรงกันหรือไม่
    if ($new_password === $confirm_password) {
        // เข้ารหัสรหัสผ่านใหม่
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // อัปเดตข้อมูลรหัสผ่านในฐานข้อมูล
        $sql = "UPDATE users SET password = '$hashed_password', reset_password = 0 WHERE user_id = $user_id";
        if ($conn->query($sql) === TRUE) {
            $success = "เปลี่ยนรหัสผ่านสำเร็จ!";
            header('Location: login.php'); // เปลี่ยนเส้นทางไปยังหน้า login หลังจากสำเร็จ
            exit();
        } else {
            $error = "มีข้อผิดพลาดในการเปลี่ยนรหัสผ่าน: " . $conn->error;
        }
    } else {
        $error = "รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        /* CSS Styles */
    </style>
</head>
<body>

<div class="reset-password-container">
    <h2>รีเซ็ตรหัสผ่าน</h2>

    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <label for="new_password">รหัสผ่านใหม่:</label>
        <input type="text" id="new_password" name="new_password" required>
        
        <label for="confirm_password">ยืนยันรหัสผ่านใหม่:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
        
        <input type="submit" value="เปลี่ยนรหัสผ่าน">
    </form>
</div>

</body>
</html>
