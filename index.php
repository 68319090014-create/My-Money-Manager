<?php
session_start();
require_once 'db.php';

// ถ้ายังไม่ Login ให้ดีดกลับไปหน้า Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// 1. ดึงยอดเงิน
$stmt = $conn->prepare("SELECT SUM(balance) AS total FROM wallets WHERE user_id = :uid");
$stmt->execute([':uid' => $user_id]);
$total_balance = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// 2. ดึงรายรับ-จ่ายเดือนนี้
$sql_stats = "SELECT 
    SUM(CASE WHEN t.type = 'income' THEN amount ELSE 0 END) as income,
    SUM(CASE WHEN t.type = 'expense' THEN amount ELSE 0 END) as expense
    FROM transactions t 
    JOIN wallets w ON t.wallet_id = w.wallet_id
    WHERE w.user_id = :uid AND MONTH(transaction_date) = MONTH(CURRENT_DATE())";
$stmt = $conn->prepare($sql_stats);
$stmt->execute([':uid' => $user_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. ดึงรายการล่าสุด
$sql_recent = "SELECT t.*, w.wallet_name, c.name as category_name 
               FROM transactions t 
               JOIN wallets w ON t.wallet_id = w.wallet_id
               LEFT JOIN categories c ON t.category_id = c.category_id
               WHERE w.user_id = :uid
               ORDER BY t.transaction_date DESC LIMIT 5";
$stmt = $conn->prepare($sql_recent);
$stmt->execute([':uid' => $user_id]);
$recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. ดึงรายชื่อกระเป๋า
$stmt = $conn->prepare("SELECT * FROM wallets WHERE user_id = :uid");
$stmt->execute([':uid' => $user_id]);
$my_wallets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - My Money</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background: linear-gradient(-45deg, #0f0c29, #302b63, #24243e, #000000);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            color: #fff;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Glassmorphism Classes */
        .glass-navbar {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .glass-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.08);
        }

        /* ปุ่มหลัก */
        .btn-glow {
            background: linear-gradient(45deg, #ff00cc, #333399);
            border: none;
            color: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(255, 0, 204, 0.3);
            transition: 0.3s;
        }
        .btn-glow:hover {
            transform: scale(1.02);
            box-shadow: 0 6px 20px rgba(255, 0, 204, 0.5);
            color: white;
        }

        /* รายการล่าสุด */
        .list-group-item {
            background: rgba(255, 255, 255, 0.02);
            border: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: white;
        }
        .list-group-item:last-child { border-bottom: none; }

        /* Modal แต่งให้มืด */
        .modal-content {
            background-color: #1a1a2e;
            color: white;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .form-control, .form-select {
            background-color: #16213e;
            border: 1px solid #0f3460;
            color: white;
        }
        .form-control:focus, .form-select:focus {
            background-color: #1a1a2e;
            color: white;
            border-color: #e94560;
            box-shadow: none;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg glass-navbar mb-5">
        <div class="container">
            <a class="navbar-brand text-white fw-bold" href="#" style="font-family: 'Poppins';">
                <i class="fas fa-bolt text-warning me-2"></i>My Money
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white-50 me-3 d-none d-sm-block">สวัสดี, คุณ<?php echo $username; ?></span>
                <a href="logout.php" class="btn btn-sm btn-outline-light rounded-pill px-3">
                    <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        
        <div class="row g-4 mb-5">
            <div class="col-lg-4">
                <div class="glass-card text-center h-100 d-flex flex-column justify-content-center">
                    <h6 class="text-white-50 mb-2">ยอดเงินคงเหลือรวม</h6>
                    <h1 class="fw-bold display-5 mb-0" style="text-shadow: 0 0 20px rgba(255,255,255,0.3);">
                        ฿ <?php echo number_format($total_balance, 2); ?>
                    </h1>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="row g-4 h-100">
                    <div class="col-md-6">
                        <div class="glass-card h-100 border-success border-start border-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-25 p-3 rounded-circle me-3">
                                    <i class="fas fa-arrow-down text-success fa-2x"></i>
                                </div>
                                <div>
                                    <h6 class="text-white-50">รายรับ (เดือนนี้)</h6>
                                    <h3 class="fw-bold text-success mb-0">+ ฿ <?php echo number_format($stats['income'] ?? 0, 2); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="glass-card h-100 border-danger border-start border-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-danger bg-opacity-25 p-3 rounded-circle me-3">
                                    <i class="fas fa-arrow-up text-danger fa-2x"></i>
                                </div>
                                <div>
                                    <h6 class="text-white-50">รายจ่าย (เดือนนี้)</h6>
                                    <h3 class="fw-bold text-danger mb-0">- ฿ <?php echo number_format($stats['expense'] ?? 0, 2); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button class="btn btn-glow w-100 py-3 mb-5 fw-bold fs-5" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-plus-circle me-2"></i> จดบันทึกรายการใหม่
        </button>

        <div class="glass-card p-0 overflow-hidden">
            <div class="p-4 border-bottom border-secondary">
                <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-info"></i> รายการล่าสุด</h5>
            </div>
            <div class="list-group list-group-flush">
                <?php if(count($recent_transactions) > 0): ?>
                    <?php foreach($recent_transactions as $t): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle p-2 me-3 <?php echo $t['type']=='income'?'bg-success':'bg-danger'; ?> bg-opacity-25">
                                    <i class="fas <?php echo $t['type']=='income'?'fa-wallet':'fa-shopping-bag'; ?> text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-bold fs-5"><?php echo $t['note'] ? $t['note'] : $t['category_name']; ?></div>
                                    <small class="text-white-50">
                                        <i class="far fa-clock me-1"></i><?php echo date('d/m H:i', strtotime($t['transaction_date'])); ?> 
                                        • <span class="badge bg-secondary bg-opacity-50"><?php echo $t['wallet_name']; ?></span>
                                    </small>
                                </div>
                            </div>
                            <span class="fs-5 fw-bold <?php echo $t['type']=='income'?'text-success':'text-danger'; ?>">
                                <?php echo $t['type']=='income'?'+':'-'; ?> <?php echo number_format($t['amount'], 2); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-5 text-center text-white-50">
                        <i class="fas fa-ghost fa-3x mb-3"></i>
                        <p>ยังไม่มีรายการบันทึก เงียบเหงาจัง...</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold">📝 บันทึกรายการ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="save_transaction.php" method="POST">
                        <div class="btn-group w-100 mb-4">
                            <input type="radio" class="btn-check" name="type" id="t-exp" value="expense" checked>
                            <label class="btn btn-outline-danger" for="t-exp">รายจ่าย</label>
                            <input type="radio" class="btn-check" name="type" id="t-inc" value="income">
                            <label class="btn btn-outline-success" for="t-inc">รายรับ</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="number" name="amount" class="form-control" id="floatingAmount" placeholder="0.00" required step="0.01">
                            <label for="floatingAmount" class="text-secondary">จำนวนเงิน (บาท)</label>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="text-white-50 small mb-1">หมวดหมู่</label>
                                <select name="category_id" class="form-select py-2">
                                    <option value="1">🍔 ค่าอาหาร</option>
                                    <option value="2">🚕 ค่าเดินทาง</option>
                                    <option value="3">💰 เงินเดือน</option>
                                    <option value="4">🛍️ ช้อปปิ้ง</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="text-white-50 small mb-1">กระเป๋า</label>
                                <select name="wallet_id" class="form-select py-2">
                                    <?php foreach($my_wallets as $wallet): ?>
                                        <option value="<?php echo $wallet['wallet_id']; ?>"><?php echo $wallet['wallet_name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <input type="datetime-local" name="date" class="form-control" required value="<?php echo date('Y-m-d\TH:i'); ?>">
                        </div>
                        
                        <div class="mb-4">
                            <input type="text" name="note" class="form-control" placeholder="บันทึกช่วยจำ (เช่น ข้าวมันไก่)">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">ยืนยันการบันทึก</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>