<?php 
ob_start();
require 'db.php'; 

// เช็คล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

// ดึงข้อมูล User ปัจจุบัน
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

// ==========================================
// 1. Logic ลบรายการโปรด
// ==========================================
if (isset($_GET['remove_fav'])) {
    $place_id = $_GET['remove_fav'];
    $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND place_id = ?")->execute([$uid, $place_id]);
    $_SESSION['alert'] = ['type' => 'success', 'title' => 'ลบสำเร็จ', 'text' => 'ลบรายการโปรดแล้ว'];
    header("Location: profile.php");
    exit;
}

// ==========================================
// 2. Logic อัปโหลดรูปโปรไฟล์
// ==========================================
if (isset($_FILES['profile_img'])) {
    $target_dir = "uploads/";
    // สร้างชื่อไฟล์ใหม่เพื่อป้องกันชื่อซ้ำ (เช่น user_1_timestamp.jpg)
    $ext = pathinfo($_FILES["profile_img"]["name"], PATHINFO_EXTENSION);
    $new_name = "user_" . $uid . "_" . time() . "." . $ext;
    $target_file = $target_dir . $new_name;
    $uploadOk = 1;

    // เช็คว่าเป็นรูปจริงไหม
    $check = getimagesize($_FILES["profile_img"]["tmp_name"]);
    if($check === false) {
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'ผิดพลาด', 'text' => 'ไฟล์ไม่ใช่รูปภาพ'];
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["profile_img"]["tmp_name"], $target_file)) {
            // อัปเดต Database
            $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmt->execute([$new_name, $uid]);
            $_SESSION['alert'] = ['type' => 'success', 'title' => 'สำเร็จ', 'text' => 'เปลี่ยนรูปโปรไฟล์เรียบร้อย'];
            header("Location: profile.php");
            exit;
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'title' => 'ผิดพลาด', 'text' => 'อัปโหลดไฟล์ไม่สำเร็จ'];
        }
    }
}

// ==========================================
// 3. Logic เปลี่ยนรหัสผ่าน
// ==========================================
if (isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // 1. ตรวจสอบรหัสผ่านเก่า
    if (!password_verify($old_pass, $user['password_hash'])) {
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'ไม่ผ่าน', 'text' => 'รหัสผ่านเดิมไม่ถูกต้อง'];
    } 
    // 2. ตรวจสอบรหัสใหม่กับยืนยัน
    elseif ($new_pass !== $confirm_pass) {
        $_SESSION['alert'] = ['type' => 'warning', 'title' => 'ไม่ตรงกัน', 'text' => 'รหัสผ่านใหม่ไม่ตรงกัน'];
    } 
    // 3. บันทึก
    else {
        $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$new_hash, $uid]);
        $_SESSION['alert'] = ['type' => 'success', 'title' => 'เรียบร้อย', 'text' => 'เปลี่ยนรหัสผ่านสำเร็จ'];
    }
    header("Location: profile.php");
    exit;
}

// ==========================================
// ดึงข้อมูลแสดงผล
// ==========================================
// ดึงประวัติ
$hist = $pdo->query("SELECT p.*, h.action_date FROM user_history h JOIN places p ON h.place_id = p.id WHERE h.user_id = $uid ORDER BY h.action_date DESC")->fetchAll();
// ดึงรายการโปรด
$favs = $pdo->query("SELECT p.* FROM favorites f JOIN places p ON f.place_id = p.id WHERE f.user_id = $uid")->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>โปรไฟล์ของฉัน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; }
        .profile-header {
            background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .avatar-circle {
            width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 4px solid #f4ecf7;
        }
        .avatar-placeholder {
            width: 100px; height: 100px; background: #8e44ad; color: white;
            font-size: 40px; display: flex; align-items: center; justify-content: center;
            border-radius: 50%; border: 4px solid #f4ecf7;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include 'navbar.php'; ?>
    
    <div class="container mt-5 mb-5">
        
        <div class="profile-header p-4 mb-4 d-flex align-items-center flex-column flex-md-row gap-4">
            <div class="position-relative">
                <?php if (!empty($user['profile_image'])): ?>
                    <img src="uploads/<?= htmlspecialchars($user['profile_image']) ?>" class="avatar-circle">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <?= strtoupper(substr($user['username'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                
                <button class="btn btn-sm btn-light position-absolute bottom-0 end-0 rounded-circle border shadow-sm" 
                        data-bs-toggle="modal" data-bs-target="#editPhotoModal" title="เปลี่ยนรูป">
                    📷
                </button>
            </div>
            
            <div class="text-center text-md-start flex-grow-1">
                <h2 class="mb-0 fw-bold"><?= htmlspecialchars($user['username']) ?></h2>
                <p class="text-muted mb-1"><?= htmlspecialchars($user['email']) ?></p>
                <span class="badge bg-<?= $user['role']=='owner'?'danger':($user['role']=='admin'?'info':'secondary') ?>">
                    <?= ucfirst($user['role']) ?>
                </span>
            </div>

            <div>
                 <button class="btn btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#changePassModal">
                    🔑 เปลี่ยนรหัสผ่าน
                 </button>
            </div>
        </div>
        
        <ul class="nav nav-tabs" id="profileTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#fav">❤️ รายการโปรด</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#hist">📍 ประวัติการเดินทาง</button>
            </li>
        </ul>
        
        <div class="tab-content bg-white p-4 border border-top-0 rounded-bottom shadow-sm">
            
            <div class="tab-pane fade show active" id="fav">
                <?php if(empty($favs)): ?>
                    <p class="text-muted text-center py-4">ยังไม่มีรายการโปรด</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($favs as $f): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="<?= htmlspecialchars($f['image_url']) ?>" style="width: 60px; height: 60px; object-fit: cover;" class="rounded me-3">
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($f['name']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($f['address']) ?></small>
                                    </div>
                                </div>
                                <div>
                                    <a href="index.php?go_place=<?= $f['id'] ?>" class="btn btn-sm btn-success me-1">ไป</a>
                                    <a href="?remove_fav=<?= $f['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('ลบ?');">ลบ</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tab-pane fade" id="hist">
                <?php if(empty($hist)): ?>
                    <p class="text-muted text-center py-4">ยังไม่มีประวัติการเดินทาง</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($hist as $h): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold">ไปที่: <?= htmlspecialchars($h['name']) ?></span>
                                    <div class="text-muted small"><?= htmlspecialchars($h['address']) ?></div>
                                </div>
                                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($h['action_date'])) ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            
        </div>
    </div>

    <div class="modal fade" id="editPhotoModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="post" enctype="multipart/form-data" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">เปลี่ยนรูปโปรไฟล์</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">เลือกรูปภาพใหม่</label>
                        <input type="file" name="profile_img" class="form-control" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึกรูปภาพ</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="changePassModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="post" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">เปลี่ยนรหัสผ่าน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">รหัสผ่านเดิม (ยืนยันตัวตน)</label>
                        <input type="password" name="old_password" class="form-control" required>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">รหัสผ่านใหม่</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" name="change_password" class="btn btn-warning">ยืนยันการเปลี่ยนรหัส</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>