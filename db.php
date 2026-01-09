<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "money_app";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "เชื่อมต่อไม่ได้: " . $e->getMessage();
}
?>