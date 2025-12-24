<?php
ob_start();
require 'db.php';

// เช็คสิทธิ์: ต้องเป็น Admin เท่านั้น (Owner ลบได้เลยไม่ต้องมาหน้านี้)
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_users.php");
    exit;
}

// รับ ID ของ User ที่จะถูกลบ
$target_id = $_GET['id'] ?? '';
if (!$target_id) {
    header("Location: admin_users.php");
    exit;
}

// ดึงชื่อ User มาแสดง
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$target_id]);
$target_user = $stmt->fetch();

if (!$target_user) {
    echo "ไม่พบผู้ใช้งานนี้";
    exit;
}

// เมื่อกดส่งคำขอ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reason = $_POST['reason'];

    if (trim($reason) == "") {
        $_SESSION['alert'] = ['type' => 'warning', 'title' => 'แจ้งเตือน', 'text' => 'กรุณาระบุเหตุผลด้วยครับ'];
    } else {
        // บันทึกลงตาราง user_requests
        $stmt = $pdo->prepare("INSERT INTO user_requests (admin_id, target_user_id, reason) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $target_id, $reason]);

        $_SESSION['alert'] = ['type' => 'success', 'title' => 'ส่งคำขอแล้ว', 'text' => 'คำขอของคุณถูกส่งไปยัง Owner เรียบร้อยแล้ว'];
        header("Location: admin_users.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ระบุเหตุผลการลบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: #f8f9fa;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-0">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0 fw-bold">🚨 ขออนุมัติลบผู้ใช้งาน</h4>
                    </div>
                    <div class="card-body p-4">
                        <p class="fs-5">คุณกำลังจะขอลบผู้ใช้: <span
                                class="fw-bold text-danger"><?= htmlspecialchars($target_user['username']) ?></span></p>

                        <form method="post">
                            <div class="mb-4">
                                <label class="fw-bold form-label">ระบุเหตุผล (เพื่อให้ Owner ทราบ):</label>
                                <textarea name="reason" class="form-control" rows="4"
                                    placeholder="เช่น ทำผิดกฎร้ายแรง, เป็นบัญชีสแปม, โพสต์ข้อความไม่เหมาะสม..."
                                    required></textarea>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-danger btn-lg">ส่งคำขอลบ</button>
                                <a href="admin_users.php" class="btn btn-secondary">ยกเลิก</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>