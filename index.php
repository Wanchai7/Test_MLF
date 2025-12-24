<?php
ob_start();
require 'db.php';

// ==========================================
// ส่วน Logic การทำงาน
// ==========================================

// 1. บันทึกประวัติ (History) - ไม่จำกัด
if (isset($_GET['go_place']) && isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("INSERT INTO user_history (user_id, place_id) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $_GET['go_place']]);
    $_SESSION['alert'] = ['type' => 'success', 'title' => 'เริ่มการเดินทาง!', 'text' => 'บันทึกประวัติแล้ว'];
    header("Location: index.php");
    exit;
}

// 2. บันทึกรายการโปรด (Favorites) - [แก้ไข] จำกัด 10 รายการ
if (isset($_GET['fav_place']) && isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $pid = $_GET['fav_place'];

    // [ใหม่] เช็คจำนวนรายการโปรดปัจจุบันก่อน
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
    $countStmt->execute([$uid]);
    $currentFavs = $countStmt->fetchColumn();

    // ถ้ามี 10 รายการขึ้นไป ให้แจ้งเตือนและห้ามเพิ่ม
    if ($currentFavs >= 10) {
        $_SESSION['alert'] = [
            'type' => 'warning',
            'title' => 'รายการโปรดเต็ม!',
            'text' => 'คุณบันทึกได้สูงสุด 10 รายการ (ต้องลบของเก่าออกก่อน)'
        ];
    } else {
        // ถ้ายังไม่เต็ม ให้เช็คว่าซ้ำไหม แล้วบันทึกตามปกติ
        $check = $pdo->prepare("SELECT * FROM favorites WHERE user_id = ? AND place_id = ?");
        $check->execute([$uid, $pid]);

        if (!$check->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO favorites (user_id, place_id) VALUES (?, ?)");
            $stmt->execute([$uid, $pid]);
            $_SESSION['alert'] = ['type' => 'success', 'title' => 'ถูกใจ!', 'text' => 'เพิ่มในรายการโปรดแล้ว'];
        } else {
            $_SESSION['alert'] = ['type' => 'info', 'title' => 'มีอยู่แล้ว', 'text' => 'คุณบันทึกสถานที่นี้แล้ว'];
        }
    }

    header("Location: index.php");
    exit;
}

// 3. เตรียมข้อมูล Dropdown
$moods = $pdo->query("SELECT * FROM moods")->fetchAll();
$coping_methods = $pdo->query("SELECT * FROM coping_methods")->fetchAll();

// 4. ระบบค้นหา (Search)
$places = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mood_id'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['alert'] = ['type' => 'warning', 'title' => 'กรุณาเข้าสู่ระบบ', 'text' => 'ต้องล็อกอินก่อนค้นหา'];
        header("Location: login.php");
        exit;
    }

    $mood_id = $_POST['mood_id'];
    $selected_coping = $_POST['coping_method'] ?? '';

    $sql = "SELECT p.* FROM places p 
            JOIN place_moods pm ON p.id = pm.place_id 
            WHERE pm.mood_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mood_id]);
    $places = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Mood Location Finder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://source.unsplash.com/random/1600x900/?nature,calm');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
            margin-bottom: 30px;
                }
    </style>
</head>

<body class="d-flex flex-column min-vh-100 bg-light">

    <?php include 'navbar.php'; ?>

    <main class="flex-shrink-0">
        <div class="hero-section text-center">
            <h1>วันนี้คุณรู้สึกอย่างไร?</h1>
            <p class="lead">ค้นหาสถานที่ที่เหมาะกับอารมณ์ของคุณ</p>
        </div>

        <div class="container mb-5">
            <div class="card p-4 shadow-sm mb-5" style="margin-top: -50px; position: relative; z-index: 10;">
                <form method="post">
                    <div class=" row align-items-end"> <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">1. ความรู้สึก</label>
                    <select name="mood_id" id="mood_select" class="form-select" required
                        onchange="updateCopingOptions()">
                        <option value="">-- เลือก --</option>
                        <?php foreach ($moods as $m): ?>
                            <option value="<?= $m['id'] ?>">
                                <?= $m['mood_name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">2. วิธีจัดการ</label>
                <select name="coping_method" id="coping_select" class="form-select" required>
                    <option value="">-- กรุณาเลือกความรู้สึกก่อน --</option>
                </select>
            </div>
                        <div class="col-md-4 mb-3">
                        <button type="submit" class="btn btn-primary w-100 p-2">🔍 ค้นหา</button>
        </div>
        </div>
        </form>
        </div>

        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
                    <?php if (!empty($places)): ?>
                <h4 class="mb-3">สถานที่แนะนำ: <span class="text-primary"><?= htmlspecialchars($selected_coping) ?></span>
                </h4>
                <div class="row">
                    <?php foreach ($places as $place): ?>
                                        <div class="col-md-4 mb-4">
                                            <div class="card h-100 shadow-sm">
                                                <img src="<?= htmlspecialchars($place['image_url']) ?>" class="card-img-top"
                        style="height: 200px; object-fit: cover;"
                        onerror="this.src='https://via.placeholder.com/300'">
                                    <div class="card-body">
                        <h5>
                            <?= htmlspecialchars($place['name']) ?>
                        </h5>
                          
                        <p class="text-muted small">
                            <?= htmlspecialchars($place['description']) ?>
                        </p>
                        <div class="d-flex mt-3">
                                            <a href="?go_place=<?= $place['id'] ?>"          class="btn btn-success flex-grow-1
                    me-2">นำทาง</a>
                            <a href="?fav_place=<?= $place['id'] ?>" class="btn btn-outline-danger">❤️</a>
                        </div>
                    </div>
                                                </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                            <div class="alert alert-warning text-center">ยังไม่พบสถานที่ที่ตรงกับอารมณ์นี้ในระบบ</div>
                    <?php endif; ?>
        <?php endif; ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const copingData = <?= json_encode($coping_methods) ?>;
        function updateCopingOptions() {
            const moodSelect = document.getElementById('mood_select');
            const copingSelect = document.getElementById('coping_select');
            const selectedMoodId = moodSelect.value;
            copingSelect.innerHTML = '<option value="">-- เลือกวิธีจัดการ --</option>';

            if (selectedMoodId) {
                const filtered = copingData.filter(item => item.mood_id == selectedMoodId);
                if (filtered.length > 0) {
                    filtered.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.method_name;
                        option.text = item.method_name;
                        copingSelect.appendChild(option);
                    });
                } else {
                    copingSelect.innerHTML = '<option value="">ไม่มีข้อมูลวิธีจัดการสำหรับอารมณ์นี้</option>';
                }
            } else {
                copingSelect.innerHTML = '<option value="">-- กรุณาเลือกความรู้สึกก่อน --</option>';
            }
        }
    </script>
</body>

</html>