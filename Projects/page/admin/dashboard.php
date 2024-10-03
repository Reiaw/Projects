<?php
session_start();
include('../../config/db.php');  // เปลี่ยนเส้นทางการเชื่อมต่อฐานข้อมูล

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');  // เปลี่ยนเส้นทางการเช็ค role
    exit;
}

?>

<h1>Admin Dashboard</h1>
<nav>
    <a href="manage_users.php">จัดการผู้ใช้งาน</a>
    <a href="manage_stores.php">จัดการสาขา</a>
</nav>
<form action="/Projects/auth/logout.php" method="POST" style="display: inline;">
    <button type="submit">Logout</button>
</form>