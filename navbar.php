<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">🎭 MoodFinder</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="index.php">หน้าหลัก</a></li>

                <?php if (isset($_SESSION['user_id'])): ?>

                    <?php if ($_SESSION['role'] == 'owner' || $_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item"><a class="nav-link text-warning" href="owner_places.php">จัดการสถานที่</a></li>
                    <?php endif; ?>

                    <?php if ($_SESSION['role'] == 'owner' || $_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item"><a class="nav-link text-info" href="admin_users.php">จัดการผู้ใช้งาน</a></li>
                    <?php endif; ?>

                    <li class="nav-item"><a class="nav-link" href="profile.php">โปรไฟล์</a></li>
                    <li class="nav-item ms-2">
                        <span class="text-light me-2 small">
                            <?= htmlspecialchars($_SESSION['username']) ?>
                            (<?= ucfirst($_SESSION['role']) ?>)
                        </span>
                        <a href="logout.php" class="btn btn-sm btn-danger">ออกจากระบบ</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-2"><a href="login.php" class="btn btn-sm btn-outline-light me-1">เข้าสู่ระบบ</a>
                    </li>
                    <li class="nav-item"><a href="register.php" class="btn btn-sm btn-primary">สมัครสมาชิก</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>