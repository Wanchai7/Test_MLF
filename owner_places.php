<?php
ob_start();
require 'db.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'owner' && $_SESSION['role'] != 'admin')) {
    header("Location: index.php");
    exit;
}

// Logic ลบสถานที่
if (isset($_GET['delete_place'])) {
    $pid = $_GET['delete_place'];

    if ($_SESSION['role'] == 'admin') {
        // [ADMIN] -> ส่งคำขอลบ
        $stmt = $pdo->prepare("INSERT INTO place_requests (admin_id, place_id, action_type) VALUES (?, ?, 'DELETE')");
        $stmt->execute([$_SESSION['user_id'], $pid]);
        $_SESSION['alert'] = ['type' => 'warning', 'title' => 'ส่งคำขอลบแล้ว', 'text' => 'รอ Owner อนุมัติการลบนี้'];
    } else {
        // [OWNER] -> ลบเลย
        $stmt = $pdo->prepare("DELETE FROM places WHERE id = ?");
        $stmt->execute([$pid]);
        $_SESSION['alert'] = ['type' => 'success', 'title' => 'ลบสำเร็จ', 'text' => 'ลบสถานที่ออกจากระบบแล้ว'];
    }
    header("Location: owner_places.php");
    exit;
}

$places = $pdo->query("SELECT * FROM places ORDER BY id DESC")->fetchAll();
// นับจำนวนคำขอรออนุมัติ (เฉพาะ Owner ถึงจะเห็น)
$req_count = 0;
if ($_SESSION['role'] == 'owner') {
    $req_count = $pdo->query("SELECT COUNT(*) FROM place_requests")->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการสถานที่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: #f8f9fa;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>🏰 จัดการสถานที่ (<?= ucfirst($_SESSION['role']) ?>)</h2>
            <div class="d-flex gap-2">
                <?php if ($_SESSION['role'] == 'owner'): ?>
                    <a href="owner_requests.php" class="btn btn-warning position-relative">
                        📩 คำขออนุมัติ
                        <?php if ($req_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $req_count ?>
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <a href="place_form.php" class="btn btn-success">+ เพิ่มสถานที่</a>
            </div>
        </div>

        <div class="row">
            <?php foreach ($places as $p): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?= htmlspecialchars($p['image_url']) ?>" class="card-img-top"
                            style="height: 200px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/300'">
                        <div class="card-body">
                            <h5><?= htmlspecialchars($p['name']) ?></h5>
                            <p class="text-muted small text-truncate"><?= htmlspecialchars($p['description']) ?></p>
                            <div class="d-flex justify-content-between mt-3">
                                <a href="place_form.php?edit=<?= $p['id'] ?>"
                                    class="btn btn-warning btn-sm w-50 me-1">แก้ไข</a>
                                <a href="?delete_place=<?= $p['id'] ?>" class="btn btn-danger btn-sm w-50"
                                    onclick="return confirm('ยืนยันลบ?');">ลบ</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>