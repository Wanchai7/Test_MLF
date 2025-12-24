<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
<style>
    :root {
        --main-purple: #8e44ad;
        /* ม่วงหลัก */
        --dark-purple: #6c3483;
        /* ม่วงเข้ม (Hover) */
        --light-purple: #f4ecf7;
        /* ม่วงอ่อน (Background) */
        --text-color: #2c3e50;
    }

    body {
        font-family: 'Prompt', sans-serif;
        /* เปลี่ยนฟ้อนต์ทั้งเว็บ */
        background-color: var(--light-purple) !important;
        color: var(--text-color);
    }

    /* Navbar สีม่วง */
    .bg-custom-purple {
        background: linear-gradient(135deg, var(--main-purple), var(--dark-purple));
    }

    /* ปุ่มหลัก (Primary Button) เป็นสีม่วง */
    .btn-primary {
        background-color: var(--main-purple);
        border-color: var(--main-purple);
        box-shadow: 0 4px 6px rgba(142, 68, 173, 0.3);
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: var(--dark-purple);
        border-color: var(--dark-purple);
        transform: translateY(-2px);
    }

    /* ปุ่มรายการโปรด (หัวใจ) */
    .btn-danger {
        background-color: #ff6b6b;
        border: none;
    }

    .btn-outline-danger {
        color: #ff6b6b;
        border-color: #ff6b6b;
    }

    .btn-outline-danger:hover {
        background-color: #ff6b6b;
        color: white;
    }

    /* การ์ดและกล่องต่างๆ */
    .card {
        border: none;
        border-radius: 15px;
        /* มุมมนสวยๆ */
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s;
    }

    .card:hover {
        transform: translateY(-5px);
        /* ลอยขึ้นเมื่อเอาเมาส์ชี้ */
    }

    /* Text สีม่วง */
    .text-primary {
        color: var(--main-purple) !important;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-custom-purple shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="index.php">
            🔮 MoodFinder
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="index.php">🏠 หน้าหลัก</a></li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] == 'owner' || $_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="owner_places.php">🏰 จัดการสถานที่</a></li>
                        <li class="nav-item"><a class="nav-link" href="admin_users.php">👥 จัดการผู้ใช้</a></li>
                    <?php endif; ?>

                    <li class="nav-item"><a class="nav-link" href="profile.php">👤 โปรไฟล์</a></li>
                    <li class="nav-item ms-2">
                        <span class="badge bg-light text-dark rounded-pill px-3 py-2">
                            <?= htmlspecialchars($_SESSION['username']) ?>
                            (<?= ucfirst($_SESSION['role']) ?>)
                        </span>
                        <a href="logout.php" class="btn btn-sm btn-danger ms-2 rounded-pill px-3">🚪 ออกจากระบบ</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-2"><a href="login.php"
                            class="btn btn-sm btn-light text-purple fw-bold rounded-pill px-3 me-2">🔑 เข้าสู่ระบบ</a></li>
                    <li class="nav-item"><a href="register.php" class="btn btn-sm btn-warning rounded-pill px-3">📝
                            สมัครสมาชิก</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>