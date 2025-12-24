<?php
ob_start();
require 'db.php';

// ==========================================
// 1. ส่วนเตรียมตัวแปร (เก็บค่าการค้นหาปัจจุบันไว้)
// ==========================================
$current_mood = isset($_GET['mood_id']) ? $_GET['mood_id'] : '';
$current_coping = isset($_GET['coping_method']) ? $_GET['coping_method'] : '';

// สร้าง Query String สำหรับส่งกลับไปหน้าเดิม (เพื่อให้หลังกดปุ่มต่างๆ ยังอยู่ที่หน้าค้นหาเดิม)
$redirect_params = "";
if ($current_mood != '') {
    $redirect_params = "?mood_id=" . $current_mood . "&coping_method=" . urlencode($current_coping);
}

// ==========================================
// 2. ส่วน Logic การทำงาน (History & Favorites)
// ==========================================

// --- บันทึกประวัติ (History) ---
if (isset($_GET['go_place']) && isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("INSERT INTO user_history (user_id, place_id) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $_GET['go_place']]);
    $_SESSION['alert'] = ['type' => 'success', 'title' => '🗺️ เริ่มการเดินทาง!', 'text' => 'ระบบบันทึกประวัติเรียบร้อยแล้ว'];

    header("Location: index.php" . $redirect_params);
    exit;
}

// --- บันทึก/ยกเลิก รายการโปรด (Favorites Toggle) ---
if (isset($_GET['fav_place']) && isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $pid = $_GET['fav_place'];

    // เช็คว่ามีอยู่แล้วหรือไม่?
    $check = $pdo->prepare("SELECT * FROM favorites WHERE user_id = ? AND place_id = ?");
    $check->execute([$uid, $pid]);

    if ($check->fetch()) {
        // [A] ถ้ามีอยู่แล้ว -> ให้ "ลบออก"
        $del = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND place_id = ?");
        $del->execute([$uid, $pid]);
        $_SESSION['alert'] = ['type' => 'success', 'title' => '💔 ยกเลิก', 'text' => 'ลบออกจากรายการโปรดแล้ว'];
    } else {
        // [B] ถ้ายังไม่มี -> เช็คโควต้า 10 รายการ -> แล้ว "เพิ่ม"
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
        $countStmt->execute([$uid]);
        $currentFavs = $countStmt->fetchColumn();

        if ($currentFavs >= 10) {
            $_SESSION['alert'] = ['type' => 'warning', 'title' => '⚠️ เต็มแล้ว!', 'text' => 'รายการโปรดเต็ม 10 รายการ (กรุณาลบของเก่าออกก่อน)'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO favorites (user_id, place_id) VALUES (?, ?)");
            $stmt->execute([$uid, $pid]);
            $_SESSION['alert'] = ['type' => 'success', 'title' => '❤️ ถูกใจ!', 'text' => 'เพิ่มในรายการโปรดแล้ว'];
        }
    }

    header("Location: index.php" . $redirect_params);
    exit;
}

// ==========================================
// 3. ส่วนดึงข้อมูล (Fetch Data)
// ==========================================
$moods = $pdo->query("SELECT * FROM moods")->fetchAll();
$coping_methods = $pdo->query("SELECT * FROM coping_methods")->fetchAll();
$places = [];

// ดึงรายการ ID สถานที่ที่ User คนนี้เคยกด Fav ไว้ (เพื่อเอาไปเปลี่ยนสีปุ่มหัวใจ)
$user_fav_ids = [];
if (isset($_SESSION['user_id'])) {
    $stmt_favs = $pdo->prepare("SELECT place_id FROM favorites WHERE user_id = ?");
    $stmt_favs->execute([$_SESSION['user_id']]);
    $user_fav_ids = $stmt_favs->fetchAll(PDO::FETCH_COLUMN);
}

// ค้นหาสถานที่ (เมื่อมี mood_id)
if ($current_mood != '') {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['alert'] = ['type' => 'warning', 'title' => '🔒 กรุณาเข้าสู่ระบบ', 'text' => 'ต้องล็อกอินก่อนจึงจะค้นหาได้'];
        header("Location: login.php");
        exit;
    }

    $sql = "SELECT p.* FROM places p 
            JOIN place_moods pm ON p.id = pm.place_id 
            WHERE pm.mood_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$current_mood]);
    $places = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mood Location Finder</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        /* ตั้งค่าฟ้อนต์หลัก */
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f8f9fa;
        }

        /* Hero Section สีม่วงไล่เฉด */
        .hero-section {
            background: linear-gradient(135deg, rgba(142, 68, 173, 0.9), rgba(44, 62, 80, 0.8)), url('https://source.unsplash.com/random/1600x900/?landscape,purple');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0 120px 0; /* เพิ่ม padding ด้านล่างเผื่อกล่องค้นหา */
            border-bottom-left-radius: 50px;
            border-bottom-right-radius: 50px;
            box-shadow: 0 10px 30px rgba(142, 68, 173, 0.2);
            margin-bottom: 60px;
        }

        /* กล่องค้นหาลอยเด่น */
        .search-card {
            margin-top: -80px; /* ดึงขึ้นไปทับ Hero */
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            background: rgba(255, 255, 255, 0.98);
        }

        /* แต่งปุ่มและ Input */
        .form-select-lg {
            border-radius: 50px;
            border: 2px solid #e9ecef;
            font-size: 1rem;
        }
        .form-select-lg:focus {
            border-color: #8e44ad;
            box-shadow: 0 0 0 0.25rem rgba(142, 68, 173, 0.25);
        }
        
        .btn-search {
            background-color: #8e44ad;
            border-color: #8e44ad;
            color: white;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-search:hover {
            background-color: #732d91;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(142, 68, 173, 0.3);
        }

        /* การ์ดสถานที่ */
        .place-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            background: white;
        }
        .place-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        }
        .place-img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }

        /* สี Text ม่วง */
        .text-purple {
            color: #8e44ad !important;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <?php include 'navbar.php'; ?>

    <main class="flex-shrink-0">
        
        <div class="hero-section text-center position-relative">
            <div class="container">
                <h1 class="display-4 fw-bold mb-3">✨ วันนี้คุณรู้สึกอย่างไร? ✨</h1>
                <p class="lead fs-5 opacity-75">ให้เราช่วยหาสถานที่ฮีลใจ หรือที่ที่เหมาะกับอารมณ์ของคุณ 💜</p>
            </div>
        </div>

        <div class="container mb-5">
            <div class="card p-4 p-md-5 search-card mb-5">
                <form method="get">
                    <div class="row align-items-end g-3">
                        
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-purple ps-2">1. 🎭 ความรู้สึก</label>
                            <select name="mood_id" id="mood_select" class="form-select form-select-lg" required onchange="updateCopingOptions()">
                                <option value="">-- เลือกความรู้สึก --</option>
                                <?php foreach ($moods as $m): ?>
                                        <option value="<?= $m['id'] ?>" <?= ($current_mood == $m['id']) ? 'selected' : '' ?>>
                                            <?= $m['mood_name'] ?>
                                        </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-purple ps-2">2. 🛠️ วิธีจัดการ</label>
                            <input type="hidden" id="old_coping_value" value="<?= htmlspecialchars($current_coping) ?>">
                            <select name="coping_method" id="coping_select" class="form-select form-select-lg" required>
                                <option value="">-- กรุณาเลือกความรู้สึกก่อน --</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <button type="submit" class="btn btn-search w-100 btn-lg">
                                🔍 ค้นหาสถานที่
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <?php if ($current_mood != ''): ?>
                
                    <?php if (!empty($places)): ?>
                            <div class="d-flex align-items-center mb-4">
                                <h4 class="mb-0 fw-bold text-secondary">สถานที่แนะนำ:</h4>
                                <span class="badge bg-primary ms-2 fs-6 rounded-pill px-3 py-2">
                                    <?= htmlspecialchars($current_coping) ?>
                                </span>
                            </div>

                            <div class="row g-4">
                                <?php foreach ($places as $place): ?>
                        
                                    <?php
                                    // Logic ปุ่มหัวใจ
                                    $is_fav = in_array($place['id'], $user_fav_ids);
                                    $fav_btn_class = $is_fav ? 'btn-danger' : 'btn-outline-danger';
                                    $fav_icon = $is_fav ? '❤️' : '🤍';
                                    $fav_title = $is_fav ? 'ลบออกจากรายการโปรด' : 'เพิ่มในรายการโปรด';
                                    ?>

                                    <div class="col-md-4">
                                        <div class="place-card shadow-sm h-100">
                                            <div class="position-relative">
                                                <img src="<?= htmlspecialchars($place['image_url']) ?>" class="place-img" onerror="this.src='https://via.placeholder.com/400x300?text=No+Image'">
                                                <span class="position-absolute top-0 end-0 m-2 badge bg-light text-dark opacity-75">
                                                    📍 <?= mb_substr($place['address'], 0, 15) ?>...
                                                </span>
                                            </div>
                                
                                            <div class="card-body d-flex flex-column">
                                                <h5 class="card-title fw-bold text-dark"><?= htmlspecialchars($place['name']) ?></h5>
                                                <p class="card-text text-muted small flex-grow-1">
                                                    <?= htmlspecialchars($place['description']) ?>
                                                </p>
                                    
                                                <div class="d-flex gap-2 mt-3 pt-3 border-top">
                                                    <a href="?go_place=<?= $place['id'] ?>&mood_id=<?= $current_mood ?>&coping_method=<?= urlencode($current_coping) ?>" 
                                                       class="btn btn-success flex-grow-1 rounded-pill fw-bold btn-sm py-2">
                                                        🗺️ นำทาง
                                                    </a>
                                        
                                                    <a href="?fav_place=<?= $place['id'] ?>&mood_id=<?= $current_mood ?>&coping_method=<?= urlencode($current_coping) ?>" 
                                                       class="btn <?= $fav_btn_class ?> rounded-circle d-flex align-items-center justify-content-center" 
                                                       style="width: 40px; height: 40px;"
                                                       title="<?= $fav_title ?>">
                                                        <?= $fav_icon ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                    <?php else: ?>
                            <div class="alert alert-light text-center py-5 shadow-sm rounded-4">
                                <h1 class="display-1">🤔</h1>
                                <h4 class="mt-3 text-muted">ไม่พบสถานที่ที่ตรงกับอารมณ์นี้ในระบบ</h4>
                                <p>ลองเปลี่ยนอารมณ์หรือวิธีจัดการดูนะครับ</p>
                            </div>
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
            const oldValue = document.getElementById('old_coping_value').value; 

            // เคลียร์ค่าเก่า
            copingSelect.innerHTML = '<option value="">-- เลือกวิธีจัดการ --</option>';

            if (selectedMoodId) {
                // กรองเฉพาะ Coping Methods ของ Mood ที่เลือก
                const filtered = copingData.filter(item => item.mood_id == selectedMoodId);
                
                if (filtered.length > 0) {
                    filtered.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.method_name;
                        option.text = item.method_name;
                        
                        // Auto Select ค่าเดิม (ถ้ามี)
                        if (item.method_name === oldValue) {
                            option.selected = true;
                        }
                        
                        copingSelect.appendChild(option);
                    });
                } else {
                    copingSelect.innerHTML = '<option value="">ไม่มีข้อมูลวิธีจัดการสำหรับอารมณ์นี้</option>';
                }
            } else {
                copingSelect.innerHTML = '<option value="">-- กรุณาเลือกความรู้สึกก่อน --</option>';
            }
        }

        // เรียกฟังก์ชันทันทีเมื่อโหลดหน้าเว็บ (เพื่อคืนค่า Dropdown หลังกดค้นหา)
        window.onload = updateCopingOptions;
    </script>
</body>
</html>