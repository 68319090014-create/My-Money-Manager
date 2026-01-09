<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = $_POST['type'];
    $amount = $_POST['amount'];
    $wallet_id = $_POST['wallet_id'];
    $category_id = $_POST['category_id'];
    $note = $_POST['note'];
    $date = $_POST['date'];

    try {
        // 1. บันทึกลงตาราง transactions
        $sql = "INSERT INTO transactions (wallet_id, category_id, amount, type, note, transaction_date) 
                VALUES (:wallet_id, :category_id, :amount, :type, :note, :date)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':wallet_id'=>$wallet_id, ':category_id'=>$category_id, ':amount'=>$amount, ':type'=>$type, ':note'=>$note, ':date'=>$date]);

        // 2. อัปเดตยอดเงินในกระเป๋า
        $sql_update = $type == 'expense' 
            ? "UPDATE wallets SET balance = balance - :amount WHERE wallet_id = :wallet_id"
            : "UPDATE wallets SET balance = balance + :amount WHERE wallet_id = :wallet_id";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->execute([':amount'=>$amount, ':wallet_id'=>$wallet_id]);

        echo "<script>alert('บันทึกสำเร็จ!'); window.location='index.php';</script>";
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>