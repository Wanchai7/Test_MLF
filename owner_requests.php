<?php
ob_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'owner') {
    header("Location: index.php");
    exit;
}

// ==========================================
// PART 1: จัดการคำขอ "สถานที่" (Place)
// ==========================================
if (isset($_GET['approve_id'])) {
    $req_id = $_GET['approve_id'];
    $stmt = $pdo->prepare("SELECT * FROM place_requests WHERE id = ?");
    $stmt->execute([$req_id]);
    $req = $stmt->fetch();

    if ($req) {
        if ($req['action_type'] == 'ADD') {
            $stmt = $pdo->prepare("INSERT INTO places (owner_id, name, description, address, image_url) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $req['name'], $req['description'], $req['address'], $req['image_url']]);
            $new_place_id = $pdo->lastInsertId();

            $moods = json_decode($req['moods_json'], true);
            if (!empty($moods)) {
                $stmt_m = $pdo->prepare("INSERT INTO place_moods (place_id, mood_id) VALUES (?, ?)");
                foreach ($moods as $mid)
                    $stmt_m->execute([$new_place_id, $mid]);
            }
        } elseif ($req['action_type'] == 'EDIT') {
            $stmt = $pdo->prepare("UPDATE places SET name=?, description=?, address=?, image_url=? WHERE id=?");
            $stmt->execute([$req['name'], $req['description'], $req['address'], $req['image_url'], $req['place_id']]);

            $pdo->prepare("DELETE FROM place_moods WHERE place_id = ?")->execute([$req['place_id']]);
            $moods = json_decode($req['moods_json'], true);
            if (!empty($moods)) {
                $stmt_m = $pdo->prepare("INSERT INTO place_moods (place_id, mood_id) VALUES (?, ?)");
                foreach ($moods as $mid)
                    $stmt_m->execute([$req['place_id'], $mid]);
            }
        } elseif ($req['action_type'] == 'DELETE') {
            $pdo->prepare("DELETE FROM places WHERE id = ?")->execute([$req['place_id']]);
        }
        $pdo->prepare("DELETE FROM place_requests WHERE id = ?")->execute([$req_id]);
        $_SESSION['alert'] = ['type' => 'success', 'title' => 'อนุมัติสำเร็จ', 'text' => 'ดำเนินการกับสถานที่แล้ว'];
    }
    header("Location: owner_requests.php");
    exit;
}

if (isset($_GET['reject_id'])) {
    $pdo->prepare("DELETE FROM place_requests WHERE id = ?")->execute([$_GET['reject_id']]);
    $_SESSION['alert'] = ['type' => 'info', 'title' => 'ปฏิเสธ', 'text' => 'ลบคำขอสถานที่แล้ว'];
    header("Location: owner_requests.php");
    exit;
}

// ==========================================
// PART 2: จัดการคำขอ "ลบผู้ใช้" (User)
// ==========================================
if (isset($_GET['approve_user_del'])) {
    $req_id = $_GET['approve_user_del'];
    $stmt = $pdo->prepare("SELECT target_user_id FROM user_requests WHERE id = ?");
    $stmt->execute([$req_id]);
    $request = $stmt->fetch();

    if ($request) {
        // ลบ User ตัวจริง
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$request['target_user_id']]);
        // ลบคำขอ
        $pdo->prepare("DELETE FROM user_requests WHERE id = ?")->execute([$req_id]);
        $_SESSION['alert'] = ['type' => 'success', 'title' => 'ลบเรียบร้อย', 'text' => 'ผู้ใช้งานถูกลบออกจากระบบแล้ว'];
    }
    header("Location: owner_requests.php");
    exit;
}

if (isset($_GET['reject_user_del'])) {
    $pdo->prepare("DELETE FROM user_requests WHERE id = ?")->execute([$_GET['reject_user_del']]);
    $_SESSION['alert'] = ['type' => 'info', 'title' => 'ปฏิเสธ', 'text' => 'ยกเลิกคำขอลบผู้ใช้แล้ว'];
    header("Location: owner_requests.php");
    exit;
}

// --- ดึงข้อมูลมาแสดง ---
$place_requests = $pdo->query("SELECT r.*, u.username FROM place_requests r JOIN users u ON r.admin_id = u.id ORDER BY r.created_at DESC")->fetchAll();

$user_requests = $pdo->query("
    SELECT r.*, 
           admin.username AS admin_name, 
           target.username AS target_name,
           target.role AS target_role
    FROM user_requests r 
    JOIN users admin ON r.admin_id = admin.id
    JOIN users target ON r.target_user_id = target.id
    ORDER BY r.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายการคำขออนุมัติ</title>
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
    <div class="container mt-5 mb-5">
        <h2 class="mb-4 fw-bold text-primary">📩 ศูนย์รวมคำขออนุมัติ</h2>

        <ul class="nav nav-tabs mb-4" id="reqTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="place-tab" data-bs-toggle="tab" data-bs-target="#place-req"
                    type="button">
                    🏰 คำขอสถานที่
                    <?php if (count($place_requests) > 0)
                        echo '<span class="badge bg-danger ms-1">' . count($place_requests) . '</span>'; ?>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="user-tab" data-bs-toggle="tab" data-bs-target="#user-req" type="button">
                    👤 คำขอลบผู้ใช้งาน
                    <?php if (count($user_requests) > 0)
                        echo '<span class="badge bg-danger ms-1">' . count($user_requests) . '</span>'; ?>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="reqTabContent">

            <div class="tab-pane fade show active" id="place-req">
                <?php if (empty($place_requests)): ?>
                    <div class="alert alert-secondary text-center py-5">ไม่มีคำขอสถานที่รออนุมัติ</div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($place_requests as $r): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card shadow-sm border-0 h-100">
                                    <div
                                        class="card-header d-flex justify-content-between align-items-center 
                                        <?= $r['action_type'] == 'DELETE' ? 'bg-danger text-white' : ($r['action_type'] == 'ADD' ? 'bg-success text-white' : 'bg-warning') ?>">
                                        <span class="fw-bold"><?= $r['action_type'] ?> (โดย: <?= $r['username'] ?>)</span>
                                        <small><?= date('d/m H:i', strtotime($r['created_at'])) ?></small>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($r['action_type'] == 'DELETE'): ?>
                                            <p>ต้องการลบสถานที่ ID: <b><?= $r['place_id'] ?></b></p>
                                        <?php else: ?>
                                            <h5 class="fw-bold"><?= htmlspecialchars($r['name']) ?></h5>
                                            <p class="small text-muted"><?= htmlspecialchars($r['description']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer bg-white border-0 d-flex gap-2">
                                        <a href="?approve_id=<?= $r['id'] ?>" class="btn btn-success flex-grow-1"
                                            onclick="return confirm('ยืนยันอนุมัติ?');">✅ อนุมัติ</a>
                                        <a href="?reject_id=<?= $r['id'] ?>" class="btn btn-outline-secondary flex-grow-1"
                                            onclick="return confirm('ปฏิเสธ?');">❌ ปฏิเสธ</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tab-pane fade" id="user-req">
                <?php if (empty($user_requests)): ?>
                    <div class="alert alert-secondary text-center py-5">ไม่มีคำขอลบผู้ใช้งานรออนุมัติ</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover border shadow-sm bg-white rounded align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Admin ผู้แจ้ง</th>
                                    <th>ต้องการลบใคร</th>
                                    <th>เหตุผล</th>
                                    <th>เวลาแจ้ง</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($user_requests as $ur): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($ur['admin_name']) ?></td>
                                        <td>
                                            <div class="fw-bold text-danger"><?= htmlspecialchars($ur['target_name']) ?></div>
                                            <span class="badge bg-secondary small"><?= ucfirst($ur['target_role']) ?></span>
                                        </td>
                                        <td style="width: 40%;">
                                            <div class="alert alert-light border border-danger mb-0 text-danger p-2 small">
                                                <?= nl2br(htmlspecialchars($ur['reason'])) ?>
                                            </div>
                                        </td>
                                        <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($ur['created_at'])) ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="?approve_user_del=<?= $ur['id'] ?>" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('ยืนยันลบ User นี้ออกจากระบบ? (กู้คืนไม่ได้)');">
                                                    ✅ ลบจริง
                                                </a>
                                                <a href="?reject_user_del=<?= $ur['id'] ?>"
                                                    class="btn btn-outline-secondary btn-sm"
                                                    onclick="return confirm('ปฏิเสธคำขอ?');">
                                                    ❌ ปฏิเสธ
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>