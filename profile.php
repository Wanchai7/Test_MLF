<?php
ob_start();
require 'db.php';

// เช็คล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

// ==========================================
// ส่วน Logic (ลบรายการโปรด)
// ==========================================
if (isset($_GET['remove_fav'])) {
    $place_id = $_GET['remove_fav'];
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND place_id = ?");
    $stmt->execute([$uid, $place_id]);

    $_SESSION['alert'] = ['type' => 'success', 'title' => 'ลบสำเร็จ', 'text' => 'ลบรายการโปรดแล้ว'];
    header("Location: profile.php");
    exit;
}

// ==========================================
// ส่วน Logic (กรองประวัติการเดินทาง)
// ==========================================
$filter_month = $_GET['filter_month'] ?? '';
$filter_year = $_GET['filter_year'] ?? '';

// สร้าง SQL พื้นฐาน
$sql_history = "SELECT p.*, h.action_date 
                FROM user_history h 
                JOIN places p ON h.place_id = p.id 
                WHERE h.user_id = ?";
$params = [$uid];

// ถ้ามีการเลือกเดือน
if ($filter_month != '') {
    $sql_history .= " AND MONTH(h.action_date) = ?";
    $params[] = $filter_month;
}

// ถ้ามีการเลือกปี
if ($filter_year != '') {
    $sql_history .= " AND YEAR(h.action_date) = ?";
    $params[] = $filter_year;
}

$sql_history .= " ORDER BY h.action_date DESC";

// รัน Query ประวัติ
$stmt = $pdo->prepare($sql_history);
$stmt->execute($params);
$history = $stmt->fetchAll();

// ==========================================
// เตรียมข้อมูลสำหรับ Dropdown (ดึงปีที่มีประวัติ)
// ==========================================
// ดึงรายการโปรดปกติ
$stmt_fav = $pdo->prepare("SELECT p.* FROM favorites f JOIN places p ON f.place_id = p.id WHERE f.user_id = ?");
$stmt_fav->execute([$uid]);
$favorites = $stmt_fav->fetchAll();

// ดึงปีที่มีใน Database ของ User คนนี้ (เพื่อไม่ให้ Dropdown ว่างเปล่า)
$stmt_years = $pdo->prepare("SELECT DISTINCT YEAR(action_date) as year FROM user_history WHERE user_id = ? ORDER BY year DESC");
$stmt_years->execute([$uid]);
$available_years = $stmt_years->fetchAll(PDO::FETCH_COLUMN);

// ชื่อเดือนภาษาไทย
$thai_months = [
    1 => 'มกราคม',
    2 => 'กุมภาพันธ์',
    3 => 'มีนาคม',
    4 => 'เมษายน',
    5 => 'พฤษภาคม',
    6 => 'มิถุนายน',
    7 => 'กรกฎาคม',
    8 => 'สิงหาคม',
    9 => 'กันยายน',
    10 => 'ตุลาคม',
    11 => 'พฤศจิกายน',
    12 => 'ธันวาคม'
];
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>โปรไฟล์ - ประวัติการเดินทาง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100 bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <div class="card p-4 mb-4 shadow-sm border-0">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-3"
                    style="width: 60px; height: 60px; font-size: 24px;">
                    <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                </div>
                <div>
                    <h2 class="mb-0">สวัสดี, <?= htmlspecialchars($_SESSION['username']) ?></h2>
                    <span class="badge bg-secondary"><?= ucfirst($_SESSION['role'] ?? 'user') ?></span>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link" id="fav-tab" data-bs-toggle="tab" data-bs-target="#fav" type="button">❤️
                    รายการโปรด</button>
            </li>
            <li class="nav-item">
                <button class="nav-link active" id="hist-tab" data-bs-toggle="tab" data-bs-target="#hist"
                    type="button">📍 ประวัติการเดินทาง</button>
            </li>
        </ul>

        <div class="tab-content bg-white p-4 border border-top-0 shadow-sm mb-5" id="myTabContent">

            <div class="tab-pane fade" id="fav">
                <?php if (empty($favorites)): ?>
                    <p class="text-muted text-center py-4">ยังไม่มีรายการโปรด</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($favorites as $f): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="<?= htmlspecialchars($f['image_url']) ?>"
                                        style="width: 60px; height: 60px; object-fit: cover;" class="rounded me-3"
                                        onerror="this.src='https://via.placeholder.com/60'">
                                    <div>
                                        <h5 class="mb-1 fw-bold"><?= htmlspecialchars($f['name']) ?></h5>
                                        <p class="mb-0 text-muted small text-truncate" style="max-width: 300px;">
                                            <?= htmlspecialchars($f['address']) ?>
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <a href="index.php?go_place=<?= $f['id'] ?>" class="btn btn-sm btn-success me-2">นำทาง</a>
                                    <a href="?remove_fav=<?= $f['id'] ?>" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('ยืนยันลบออกจากรายการโปรด?');">❌ ลบออก</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tab-pane fade show active" id="hist">

                <form method="get" class="row g-2 mb-4 align-items-end bg-light p-3 rounded">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">เลือกเดือน</label>
                        <select name="filter_month" class="form-select form-select-sm">
                            <option value="">-- ทุกเดือน --</option>
                            <?php foreach ($thai_months as $num => $name): ?>
                                <option value="<?= $num ?>" <?= $filter_month == $num ? 'selected' : '' ?>>
                                    <?= $name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">เลือกปี</label>
                        <select name="filter_year" class="form-select form-select-sm">
                            <option value="">-- ทุกปี --</option>
                            <?php foreach ($available_years as $y): ?>
                                <option value="<?= $y ?>" <?= $filter_year == $y ? 'selected' : '' ?>>
                                    <?= $y ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">🔍 ค้นหา</button>
                    </div>
                    <div class="col-md-2">
                        <a href="profile.php" class="btn btn-outline-secondary btn-sm w-100">ล้างค่า</a>
                    </div>
                </form>

                <?php if (empty($history)): ?>
                    <p class="text-muted text-center py-4">ไม่พบประวัติการเดินทางในช่วงเวลานี้</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($history as $h):
                            $date = strtotime($h['action_date']);
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="me-3 text-center border rounded p-2 bg-light" style="min-width: 60px;">
                                        <div class="fw-bold text-primary" style="font-size: 1.2rem;"><?= date('d', $date) ?>
                                        </div>
                                        <div class="small text-muted" style="font-size: 0.75rem;">
                                            <?= $thai_months[date('n', $date)] ?>
                                        </div>
                                        <div class="small text-muted" style="font-size: 0.75rem;"><?= date('Y', $date) ?></div>
                                    </div>
                                    <div>
                                        <span class="fw-bold d-block">ไปที่: <?= htmlspecialchars($h['name']) ?></span>
                                        <small class="text-muted"><?= htmlspecialchars($h['address']) ?></small>
                                    </div>
                                </div>
                                <span class="badge bg-secondary rounded-pill">
                                    <?= date('H:i', $date) ?> น.
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <?php include 'footer.php'; ?>

    <?php if ($filter_month != '' || $filter_year != ''): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                var triggerEl = document.querySelector('#hist-tab');
                var tab = new bootstrap.Tab(triggerEl);
                tab.show();
            });
        </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>