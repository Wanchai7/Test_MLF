<?php
ob_start();
require 'db.php';

// เช็คสิทธิ์: เข้าได้ทั้ง admin และ owner (เพิ่ม Admin เข้าไปในเงื่อนไข)
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'owner' && $_SESSION['role'] != 'admin')) {
    $_SESSION['alert'] = ['type' => 'error', 'title' => 'Access Denied', 'text' => 'ไม่มีสิทธิ์เข้าถึง'];
    header("Location: index.php");
    exit;
}

// Logic ลบสถานที่ (ทำได้ทั้งคู่)
if (isset($_GET['delete_place'])) {
    $stmt = $pdo->prepare("DELETE FROM places WHERE id = ?");
    $stmt->execute([$_GET['delete_place']]);
    $_SESSION['alert'] = ['type' => 'success', 'title' => 'ลบสำเร็จ', 'text' => 'ลบสถานที่ออกจากระบบแล้ว'];
    header("Location: owner_places.php");
    exit;
}

$places = $pdo->query("SELECT * FROM places ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการสถานที่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100 bg-light">
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>👑 จัดการสถานที่ (<?= ucfirst($_SESSION['role']) ?> Mode)</h2>
            <a href="place_form.php" class="btn btn-success">+ เพิ่มสถานที่ใหม่</a>
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
                                    onclick="return confirm('ยืนยันลบสถานที่นี้?');">ลบ</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>