<?php
ob_start();
require 'db.php';

// เช็คสิทธิ์
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'owner' && $_SESSION['role'] != 'admin')) {
    header("Location: index.php");
    exit;
}

$place = ['id' => '', 'name' => '', 'description' => '', 'address' => '', 'image_url' => ''];
$selected_moods = [];

// ถ้าเป็นการแก้ไข (Edit)
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM places WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $place = $stmt->fetch();

    // ดึง mood เก่า
    $stmt_m = $pdo->prepare("SELECT mood_id FROM place_moods WHERE place_id = ?");
    $stmt_m->execute([$_GET['edit']]);
    $selected_moods = $stmt_m->fetchAll(PDO::FETCH_COLUMN);
}

// เมื่อกด Submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $addr = $_POST['address'];
    $img = $_POST['image_url'];
    $moods = isset($_POST['moods']) ? $_POST['moods'] : []; // Array ของ Mood ID
    $moods_json = json_encode($moods);

    $action = ($place['id'] != '') ? 'EDIT' : 'ADD';
    $target_place_id = ($place['id'] != '') ? $place['id'] : NULL;

    // --- 🚨 แยกการทำงานตาม Role ---
    if ($_SESSION['role'] == 'admin') {
        // [ADMIN] -> ส่งคำขอลงตาราง place_requests
        $stmt = $pdo->prepare("INSERT INTO place_requests (admin_id, place_id, action_type, name, description, address, image_url, moods_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $target_place_id, $action, $name, $desc, $addr, $img, $moods_json]);

        $_SESSION['alert'] = ['type' => 'success', 'title' => 'ส่งคำขอแล้ว', 'text' => 'กรุณารอ Owner อนุมัติการเปลี่ยนแปลงนี้'];
        header("Location: owner_places.php");
        exit;
    } else {
        // [OWNER] -> บันทึกเลย (เหมือนเดิม)
        if ($action == 'ADD') {
            $stmt = $pdo->prepare("INSERT INTO places (owner_id, name, description, address, image_url) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $name, $desc, $addr, $img]);
            $pid = $pdo->lastInsertId();
        } else {
            $stmt = $pdo->prepare("UPDATE places SET name=?, description=?, address=?, image_url=? WHERE id=?");
            $stmt->execute([$name, $desc, $addr, $img, $target_place_id]);
            $pid = $target_place_id;
            // ลบ mood เก่า
            $pdo->prepare("DELETE FROM place_moods WHERE place_id = ?")->execute([$pid]);
        }

        // บันทึก Moods
        if (!empty($moods)) {
            $stmt_pm = $pdo->prepare("INSERT INTO place_moods (place_id, mood_id) VALUES (?, ?)");
            foreach ($moods as $mid)
                $stmt_pm->execute([$pid, $mid]);
        }

        $_SESSION['alert'] = ['type' => 'success', 'title' => 'เรียบร้อย', 'text' => 'บันทึกข้อมูลแล้ว'];
        header("Location: owner_places.php");
        exit;
    }
}

// ดึง Moods ทั้งหมดมาแสดงใน Form
$all_moods = $pdo->query("SELECT * FROM moods")->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ฟอร์มจัดการสถานที่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style>body{font-family:'Prompt',sans-serif;}</style>
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <div class="card p-4 shadow-sm mx-auto" style="max-width: 700px;">
            <h3 class="mb-4 text-center text-primary"><?= $place['id'] ? 'แก้ไขสถานที่' : 'เพิ่มสถานที่ใหม่' ?></h3>
            
            <?php if ($_SESSION['role'] == 'admin'): ?>
                    <div class="alert alert-warning">⚠️ คุณเป็น Admin การเปลี่ยนแปลงจะต้องรอการอนุมัติจาก Owner</div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label>ชื่อสถานที่</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($place['name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label>คำอธิบาย</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($place['description']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label>ที่อยู่</label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($place['address']) ?>">
                </div>
                <div class="mb-3">
                    <label>ลิงก์รูปภาพ (URL)</label>
                    <input type="text" name="image_url" class="form-control" value="<?= htmlspecialchars($place['image_url']) ?>">
                </div>
                
                <div class="mb-3">
                    <label class="fw-bold mb-2">เลือกอารมณ์ที่เหมาะสม (เลือกได้หลายข้อ)</label>
                    <div class="row">
                        <?php foreach ($all_moods as $m): ?>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="moods[]" value="<?= $m['id'] ?>" id="m_<?= $m['id'] ?>"
                                            <?= in_array($m['id'], $selected_moods) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="m_<?= $m['id'] ?>">
                                            <?= $m['mood_name'] ?>
                                        </label>
                                    </div>
                                </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <?= $_SESSION['role'] == 'admin' ? 'ส่งคำขออนุมัติ' : 'บันทึกข้อมูล' ?>
                    </button>
                    <a href="owner_places.php" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>