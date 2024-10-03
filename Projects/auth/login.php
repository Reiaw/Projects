<?php
session_start(); // เริ่มต้น session
include('../config/db.php'); // เชื่อมต่อกับฐานข้อมูล

$error = ""; // ตัวแปรสำหรับเก็บข้อความแสดงข้อผิดพลาด

// ตรวจสอบว่าได้มีการส่งฟอร์มหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับข้อมูลจากฟอร์ม
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ตรวจสอบข้อมูลในฐานข้อมูล
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $user['password'])) {
            // ตรวจสอบการรีเซ็ตรหัสผ่าน
            if ($user['reset_password'] == 1) {
                // ถ้า reset_password เป็น 1 ให้พาไปหน้ารีเซ็ตรหัสผ่าน
                $_SESSION['user_id'] = $user['user_id'];
                header('Location: reset_password.php');
                exit();
            } else {
                // ถ้าผู้ใช้ล็อกอินสำเร็จและไม่ต้องรีเซ็ตรหัสผ่าน
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_id'] = $user['user_id'];
                
                switch ($user['role']) {
                    case 'admin':
                        header('Location: ../page/admin/dashboard.php');
                        break;
                    case 'manager':
                        header('Location: ../page/manager/dashboard.php');
                        break;
                    case 'staff':
                        header('Location: ../page/staff/dashboard.php');
                        break;
                }
                exit();
         
            }
        } else {
            // รหัสผ่านไม่ถูกต้อง
            $error = "รหัสผ่านไม่ถูกต้อง";
        }
    } else {
        // ไม่พบผู้ใช้งาน
        $error = "ไม่พบผู้ใช้งานนี้";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .login-container input[type="email"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .login-container input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            border: none;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }
        .login-container input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>

    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        
        <input type="submit" value="Login">
    </form>
</div>

</body>
</html>
