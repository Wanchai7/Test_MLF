<?php
session_start();
require 'db.php';

// 1. ถ้าล็อกอินอยู่แล้วให้ไปหน้าหลัก
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_input = trim($_POST['username']);
    $password_input = trim($_POST['password']);

    if (empty($username_input) || empty($password_input)) {
        $_SESSION['alert'] = ['type' => 'warning', 'title' => 'ข้อมูลไม่ครบ', 'text' => 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน'];
    } else {
        try {
            // ค้นหาผู้ใช้ (รองรับทั้ง Username และ Email)
            $sql = "SELECT * FROM users WHERE username = :u OR email = :e";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':u' => $username_input, ':e' => $username_input]);
            $user = $stmt->fetch();

            if ($user && password_verify($password_input, $user['password'])) {
                if ($user['is_verified'] == 1) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    $_SESSION['alert'] = ['type' => 'success', 'title' => 'สำเร็จ', 'text' => "ยินดีต้อนรับคุณ {$user['username']}"];
                    header("Location: index.php");
                    exit();
                } else {
                    $_SESSION['alert'] = ['type' => 'warning', 'title' => 'ยังไม่ยืนยันตัวตน', 'text' => 'กรุณายืนยันรหัส OTP ก่อนใช้งาน'];
                    header("Location: verify_otp.php?email=" . urlencode($user['email']));
                    exit();
                }
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'title' => 'เข้าสู่ระบบไม่สำเร็จ', 'text' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'];
            }
        } catch (PDOException $e) {
            $_SESSION['alert'] = ['type' => 'error', 'title' => 'System Error', 'text' => $e->getMessage()];
        }
    }
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - MoodFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* ตกแต่งเพิ่มเติมจากสไตล์หลักของคุณ */
        .login-section {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(142, 68, 173, 0.15);
            background: #ffffff;
            overflow: hidden;
            width: 100%;
            max-width: 400px;
        }
        .login-card-header {
            background: linear-gradient(135deg, #8e44ad, #6c3483);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .form-label-custom {
            font-weight: 600;
            color: #666;
            font-size: 0.9rem;
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
            color: #8e44ad;
        }
        .form-control-custom {
            border-left: none;
            padding: 12px;
            border-radius: 0 10px 10px 0 !important;
        }
        .form-control-custom:focus {
            box-shadow: none;
            border-color: #dee2e6;
        }
        .btn-login-submit {
            background: linear-gradient(135deg, #8e44ad, #6c3483);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-login-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(142, 68, 173, 0.4);
            filter: brightness(1.1);
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <?php include 'navbar.php'; ?>

    <main class="login-section container py-5">
        <div class="login-card">
            <div class="login-card-header">
                <i class="fas fa-lock-open fa-3x mb-3"></i>
                <h4 class="fw-bold mb-0">เข้าสู่ระบบ</h4>
                <p class="small opacity-75 mb-0">ยินดีต้อนรับกลับมาสู่ MoodFinder</p>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label-custom">ชื่อผู้ใช้ หรือ อีเมล</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="username" class="form-control form-control-custom" placeholder="Username or Email" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label-custom">รหัสผ่าน</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password" name="password" class="form-control form-control-custom" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login-submit text-white w-100 mb-3">
                        Login <i class="fas fa-arrow-right ms-2"></i>
                    </button>

                    <div class="text-center">
                        <p class="small text-muted mb-0">ยังไม่มีบัญชีใช่ไหม?</p>
                        <a href="register.php" class="text-primary fw-bold text-decoration-none">สมัครสมาชิกใหม่</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>