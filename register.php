<?php
session_start();
require 'db.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['alert'] = ['type' => 'warning', 'title' => 'ข้อมูลไม่ครบ', 'text' => 'กรุณากรอกข้อมูลให้ครบถ้วน'];
    } elseif ($password !== $confirm_password) {
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'รหัสผ่านไม่ตรงกัน', 'text' => 'กรุณายืนยันรหัสผ่านใหม่'];
    } else {
        try {
            // เช็คชื่อผู้ใช้หรืออีเมลซ้ำ
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email OR username = :username");
            $stmt->execute([':email' => $email, ':username' => $username]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['alert'] = ['type' => 'warning', 'title' => 'ข้อมูลซ้ำ', 'text' => 'ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้งานแล้ว'];
            } else {
                $otp = rand(100000, 999999); 
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $role = 'user';
                $is_verified = 0;

                // บันทึกข้อมูล (ใช้ชื่อคอลัมน์ password ให้ตรงกับฐานข้อมูล)
                $sql = "INSERT INTO users (username, email, password, role, otp, is_verified) 
                        VALUES (:username, :email, :password, :role, :otp, :is_verified)";
                
                $stmt = $pdo->prepare($sql);
                $save_success = $stmt->execute([
                    ':username' => $username,
                    ':email' => $email,
                    ':password' => $hashed_password,
                    ':role' => $role,
                    ':otp' => $otp,
                    ':is_verified' => $is_verified
                ]);

                if ($save_success) {
                    $mail_sent = false;
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com'; 
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'your_email@gmail.com'; // <--- แก้ไขอีเมลของคุณ
                        $mail->Password   = 'your_app_password';    // <--- แก้ไข App Password 16 หลัก
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;
                        $mail->CharSet    = 'UTF-8';

                        $mail->setFrom('your_email@gmail.com', 'MoodFinder Admin');
                        $mail->addAddress($email, $username);

                        $mail->isHTML(true);
                        $mail->Subject = 'รหัสยืนยันตัวตน (OTP) - MoodFinder';
                        $mail->Body    = "
                            <div style='font-family: sans-serif; padding: 20px; color: #333;'>
                                <h2 style='color: #8e44ad;'>ยินดีต้อนรับสู่ MoodFinder! 🔮</h2>
                                <p>ขอบคุณที่สมัครสมาชิก กรุณายืนยันตัวตนด้วยรหัสนี้:</p>
                                <div style='background: #f4ecf7; padding: 15px; border-radius: 10px; text-align: center; margin: 20px 0;'>
                                    <span style='font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #8e44ad;'>$otp</span>
                                </div>
                                <p>นำรหัสนี้ไปกรอกที่หน้าเว็บไซต์เพื่อเริ่มใช้งาน</p>
                            </div>";

                        $mail->send();
                        $mail_sent = true;
                    } catch (Exception $e) {
                        $mail_sent = false;
                    }

                    // ปรับ Alert ตามที่คุณต้องการ: ให้ถือว่าสมัครสำเร็จแม้เมลไม่ไป
                    if ($mail_sent) {
                        $_SESSION['alert'] = ['type' => 'success', 'title' => 'สมัครสมาชิกสำเร็จ', 'text' => 'กรุณาตรวจสอบรหัส OTP ในอีเมลของคุณ'];
                    } else {
                        $_SESSION['alert'] = ['type' => 'info', 'title' => 'รอการยืนยันตัวตน', 'text' => 'สมัครสำเร็จ แต่เมลขัดข้อง กรุณาติดต่อแอดมินเพื่อขอรหัส OTP'];
                    }
                    
                    header("Location: verify_otp.php?email=" . urlencode($email));
                    exit();
                }
            }
        } catch (PDOException $e) {
             $_SESSION['alert'] = ['type' => 'error', 'title' => 'System Error', 'text' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล'];
        }
    }
    header("Location: register.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - MoodFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4ecf7; font-family: 'Prompt', sans-serif; }
        .register-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(142, 68, 173, 0.1); overflow: hidden; }
        .register-header { background: linear-gradient(135deg, #8e44ad, #6c3483); color: white; padding: 30px; text-align: center; }
        .btn-register { background: linear-gradient(135deg, #8e44ad, #6c3483); border: none; border-radius: 50px; padding: 12px; font-weight: bold; color: white; transition: 0.3s; }
        .btn-register:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(142, 68, 173, 0.3); color: white; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <?php include 'navbar.php'; ?>

    <div class="container py-5 flex-grow-1 d-flex align-items-center justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card register-card">
                <div class="register-header">
                    <h3 class="fw-bold mb-0">สร้างบัญชีใหม่</h3>
                    <p class="small opacity-75 mb-0 mt-2">เข้าร่วมกับ MoodFinder เพื่อค้นหาที่พักใจ</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form action="register.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">ชื่อผู้ใช้ (Username)</label>
                            <input type="text" name="username" class="form-control" placeholder="ชื่อภาษาอังกฤษ" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">อีเมล (Email)</label>
                            <input type="email" name="email" class="form-control" placeholder="example@mail.com" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">รหัสผ่าน</label>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">ยืนยันรหัสผ่าน</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-register w-100 mt-3">สมัครสมาชิก</button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <span class="text-muted small">เป็นสมาชิกอยู่แล้ว?</span>
                        <a href="login.php" class="text-decoration-none fw-bold ms-1" style="color: #8e44ad;">เข้าสู่ระบบ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

</body>
</html>