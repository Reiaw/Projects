<?php
session_start();
include('../../config/db.php');  // เปลี่ยนเส้นทางการเชื่อมต่อฐานข้อมูล

if ($_SESSION['role'] !== 'manager') {
    header('Location: ../../auth/login.php');  // เปลี่ยนเส้นทางการเช็ค role
    exit;
}

?>

<h1>Manager Dashboard</h1>
<nav>
    <a href="manage_products.php">จัดการสินค้า</a>
</nav>
<form action="/Projects/auth/logout.php" method="POST" style="display: inline;">
    <button type="submit">Logout</button>
</form>
