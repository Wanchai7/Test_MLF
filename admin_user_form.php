<?php
ob_start();
require 'db.php';

// เช็คสิทธิ์: เฉพาะ Owner เท่านั้น
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'owner') {
    $_SESSION['alert'] = ['type' => 'error', 'title' => 'ไม่อนุญาต', 'text' => 'เฉพาะเจ้าของระบบเท่านั้นที่เพิ่มแอดมินได้'];
    header("Location: admin_users.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Hash Password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password_hash, $role]);

        $_SESSION['alert'] = ['type' => 'success', 'title' => 'สำเร็จ', 'text' => "เพิ่มบัญชี $username ($role) เรียบร้อยแล้ว"];
        header("Location: admin_users.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'ล้มเหลว', 'text' => 'ชื่อผู้ใช้หรืออีเมลอาจซ้ำกัน'];
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เพิ่มผู้ใช้งานใหม่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100 bg-light">
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow p-4">
                    <h3 class="mb-4 text-center">เพิ่มผู้ใช้งาน / แอดมินใหม่</h3>

                    <form method="post">
                        <div class="mb-3">
                            <label>ชื่อผู้ใช้ (Username)</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>อีเมล (Email)</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>รหัสผ่าน (Password)</label>
                            <input type="text" name="password" class="form-control" placeholder="ตั้งรหัสผ่าน..."
                                required>
                        </div>

                        <div class="mb-4">
                            <label class="fw-bold">เลือกตำแหน่ง (Role)</label>
                            <select name="role" class="form-select">
                                <option value="user">User (ผู้ใช้ทั่วไป)</option>
                                <option value="admin">Admin (ผู้ดูแลระบบ)</option>
                                <option value="owner">Owner (เจ้าของร่วม)</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">บันทึกข้อมูล</button>
                            <a href="admin_users.php" class="btn btn-secondary">ยกเลิก</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>