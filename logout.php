<?php
session_start();
session_destroy(); // ล้างข้อมูลการเข้าสู่ระบบ
header("Location: login.php"); // ดีดกลับไปหน้า Login
?>