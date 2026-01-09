<?php
session_start();
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
    $stmt->execute([':username' => $username, ':password' => $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit();
    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านผิด! ลองใหม่นะ";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ohm System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            /* พื้นหลังไล่สีขยับได้ */
            background: linear-gradient(-45deg, #0f0c29, #302b63, #24243e, #000000);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* กล่อง Login แบบกระจกฝ้า (Glassmorphism) */
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            color: white;
            position: relative;
            z-index: 10;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 30px;
            padding: 12px 20px;
            color: white;
            margin-bottom: 20px;
        }
        
        .form-control::placeholder { color: rgba(255, 255, 255, 0.6); }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
            box-shadow: none;
            color: white;
        }

        .btn-login {
            background: linear-gradient(45deg, #00c6ff, #0072ff);
            border: none;
            border-radius: 30px;
            padding: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 198, 255, 0.4);
            color: white;
        }

        /* ตกแต่งวงกลมลอยๆ */
        .circle {
            position: absolute;
            border-radius: 50%;
            z-index: 1;
        }
        .c1 {
            width: 200px; height: 200px;
            background: linear-gradient(#ff00cc, #333399);
            top: 10%; left: 20%;
            animation: float 6s ease-in-out infinite;
        }
        .c2 {
            width: 150px; height: 150px;
            background: linear-gradient(#00c6ff, #0072ff);
            bottom: 10%; right: 20%;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body>

    <div class="circle c1"></div>
    <div class="circle c2"></div>

    <div class="glass-card">
        <div class="text-center mb-4">
            <i class="fas fa-fingerprint fa-3x mb-3 text-info"></i>
            <h2 class="fw-bold" style="font-family: 'Poppins', sans-serif;">WELCOME</h2>
            <p class="text-white-50">เข้าสู่ระบบการเงินของคุณโอม</p>
        </div>

        <?php if(isset($error)) echo "<div class='alert alert-danger bg-danger bg-opacity-75 text-white border-0 text-center rounded-pill'>$error</div>"; ?>

        <form method="POST">
            <div class="input-group mb-3">
                <input type="text" name="username" class="form-control" placeholder="ชื่อผู้ใช้ (Username)" required autocomplete="off">
            </div>
            
            <div class="input-group mb-4">
                <input type="password" name="password" class="form-control" placeholder="รหัสผ่าน (Password)" required>
            </div>

            <button type="submit" class="btn btn-login w-100 text-white">
                LOGIN <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </form>

        <div class="text-center mt-4">
            <small class="text-white-50">© 2025 Ohm Development</small>
        </div>
    </div>

</body>
</html>