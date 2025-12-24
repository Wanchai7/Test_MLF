<?php
ob_start();
require 'db.php';

// เช็คสิทธิ์: ต้องเป็น admin หรือ owner เท่านั้นถึงจะเข้าหน้านี้ได้
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'owner')) {
    $_SESSION['alert'] = ['type' => 'error', 'title' => 'Access Denied', 'text' => 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้'];
    header("Location: index.php");
    exit;
}

// Logic ลบ User (อนุญาตเฉพาะ Owner เท่านั้น!)
if (isset($_GET['delete_user'])) {
    if ($_SESSION['role'] != 'owner') {
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'ไม่อนุญาต', 'text' => 'Admin ไม่สามารถลบผู้ใช้งานได้ (ต้องเป็น Owner เท่านั้น)'];
    } else {
        $id = $_GET['delete_user'];
        // ป้องกันการลบตัวเอง
        if ($id == $_SESSION['user_id']) {
            $_SESSION['alert'] = ['type' => 'warning', 'title' => 'ทำไม่ได้', 'text' => 'คุณไม่สามารถลบบัญชีตัวเองได้'];
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['alert'] = ['type' => 'success', 'title' => 'เรียบร้อย', 'text' => 'ลบผู้ใช้งานแล้ว'];
        }
    }
    // รีเฟรชหน้าเพื่อเคลียร์ URL
    header("Location: admin_users.php");
    exit;
}

// ดึงข้อมูล User ทั้งหมดจากฐานข้อมูล
$stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการผู้ใช้งาน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100 bg-light">
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <div class="card p-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>👮‍♂️ รายชื่อผู้ใช้งานในระบบ</h2>

                <?php if ($_SESSION['role'] == 'owner'): ?>
                    <a href="admin_user_form.php" class="btn btn-success">+ เพิ่มผู้ใช้ / แอดมินใหม่</a>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?= $u['id'] ?></td>
                                    <td><?= htmlspecialchars($u['username']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <span
                                            class="badge bg-<?= $u['role'] == 'owner' ? 'danger' : ($u['role'] == 'admin' ? 'info' : 'secondary') ?>">
                                            <?= ucfirst($u['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($_SESSION['role'] == 'owner' && $u['role'] != 'owner'): ?>
                                            <a href="?delete_user=<?= $u['id'] ?>" class="btn btn-sm btn-danger"
                                                onclick="return confirm('ยืนยันการลบ?');">ลบ</a>
                                        <?php elseif ($_SESSION['role'] == 'admin'): ?>
                                            <span class="text-muted small">ดูได้เท่านั้น</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">ยังไม่มีข้อมูลผู้ใช้งาน</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>