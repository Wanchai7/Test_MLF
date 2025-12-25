<?php
ob_start();
session_start(); 
require 'db.php';

// 1. เช็คสิทธิ์: เฉพาะ Owner เท่านั้น
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'owner') {
    $_SESSION['alert'] = ['type' => 'error', 'title' => 'ไม่อนุญาต', 'text' => 'เฉพาะเจ้าของระบบเท่านั้นที่เพิ่มแอดมินได้'];
    header("Location: admin_users.php");
    exit;
}

// 2. ส่วนการบันทึกข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Hash Password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        // FIXED: แก้ไขชื่อคอลัมน์เป็น password และเพิ่ม is_verified = 1
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, is_verified) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$username, $email, $password_hash, $role]);

        $_SESSION['alert'] = ['type' => 'success', 'title' => 'สำเร็จ', 'text' => "เพิ่มบัญชี $username ($role) เรียบร้อยแล้ว"];
        header("Location: admin_users.php");
        exit;
    } catch (PDOException $e) {
        // กรณีชื่อผู้ใช้หรืออีเมลซ้ำ หรือ Error อื่นๆ
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'ล้มเหลว', 'text' => 'ชื่อผู้ใช้หรืออีเมลนี้มีในระบบแล้ว'];
        header("Location: " . $_SERVER['PHP_SELF']); // กลับมาหน้าเดิมเพื่อโชว์ Alert
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มผู้ใช้งานใหม่ - MoodFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4ecf7; font-family: 'Prompt', sans-serif; }
        .card-custom { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(142, 68, 173, 0.1); }
        .btn-purple { background: linear-gradient(135deg, #8e44ad, #6c3483); color: white; border: none; border-radius: 10px; }
        .btn-purple:hover { color: white; filter: brightness(1.1); }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <?php include 'navbar.php'; ?>

    <div class="container py-5 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card card-custom p-4">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold" style="color: #8e44ad;">เพิ่มผู้ใช้งานใหม่</h3>
                        <p class="text-muted small">สร้างบัญชีสำหรับ User, Admin หรือ Owner ใหม่</p>
                    </div>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">ชื่อผู้ใช้ (Username)</label>
                            <input type="text" name="username" class="form-control" required placeholder="เช่น admin_staff">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">อีเมล (Email)</label>
                            <input type="email" name="email" class="form-control" required placeholder="example@mood.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">รหัสผ่าน (Password)</label>
                            <input type="text" name="password" class="form-control" placeholder="ตั้งรหัสผ่านสำหรับใช้งานครั้งแรก" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">ตำแหน่ง (Role)</label>
                            <select name="role" class="form-select">
                                <option value="user">User (ผู้ใช้ทั่วไป)</option>
                                <option value="admin">Admin (ผู้ดูแลระบบ)</option>
                                <option value="owner">Owner (เจ้าของระบบ)</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-purple btn-lg fw-bold">บันทึกข้อมูล</button>
                            <a href="admin_users.php" class="btn btn-light text-muted">ยกเลิก</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

</body>
</html>