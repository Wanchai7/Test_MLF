<?php
ob_start();
require 'db.php';

// เช็คสิทธิ์: ต้องเป็น admin หรือ owner เท่านั้น
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'owner')) {
    $_SESSION['alert'] = ['type' => 'error', 'title' => 'Access Denied', 'text' => 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้'];
    header("Location: index.php");
    exit;
}

// Logic ลบ User
if (isset($_GET['delete_user'])) {
    if ($_SESSION['role'] != 'owner') {
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'ไม่อนุญาต', 'text' => 'Admin ต้องส่งคำขอลบพร้อมเหตุผลเท่านั้น'];
    } else {
        $id = $_GET['delete_user'];
        if ($id == $_SESSION['user_id']) {
            $_SESSION['alert'] = ['type' => 'warning', 'title' => 'ทำไม่ได้', 'text' => 'คุณไม่สามารถลบบัญชีตัวเองได้'];
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['alert'] = ['type' => 'success', 'title' => 'เรียบร้อย', 'text' => 'ลบผู้ใช้งานแล้ว'];
        }
    }
    header("Location: admin_users.php");
    exit;
}

// [แก้ไขตรงนี้] เรียงตามยศ (Owner -> Admin -> User) และถ้า role เหมือนกันให้เรียงตามชื่อ
$sql = "SELECT * FROM users 
        ORDER BY FIELD(role, 'owner', 'admin', 'user'), id ASC";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการผู้ใช้งาน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style>body{font-family:'Prompt',sans-serif; background:#f8f9fa;}</style>
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include 'navbar.php'; ?>
    
    <div class="container mt-5 mb-5">
        <div class="card p-4 shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-primary">👮‍♂️ รายชื่อผู้ใช้งานในระบบ</h2>
                
                <?php if($_SESSION['role'] == 'owner'): ?>
                    <a href="admin_user_form.php" class="btn btn-success">+ เพิ่มผู้ใช้ / แอดมินใหม่</a>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th width="10%">ลำดับ</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php 
                                // วนลูปพร้อมตัวนับ $index (เริ่มที่ 0)
                                foreach ($users as $index => $u): 
                            ?>
                            <tr>
                                <td><?= $index + 1 ?></td> 
                                
                                <td>
                                    <?= htmlspecialchars($u['username']) ?>
                                    <span class="text-muted small ms-1">(#<?= $u['id'] ?>)</span>
                                </td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $u['role'] == 'owner' ? 'danger' : ($u['role'] == 'admin' ? 'info' : 'secondary') ?>">
                                        <?= ucfirst($u['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($_SESSION['role'] == 'owner'): ?>
                                        <?php if($u['role'] != 'owner'): ?>
                                            <a href="?delete_user=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('ยืนยันลบถาวร?');">ลบถาวร</a>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    <?php elseif($_SESSION['role'] == 'admin'): ?>
                                        <?php if($u['role'] == 'user'): ?>
                                            <a href="request_delete_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-warning">⚠️ ขอลบ</a>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted">ยังไม่มีข้อมูลผู้ใช้งาน</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>