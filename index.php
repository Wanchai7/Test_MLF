<?php
ob_start();
require 'db.php';

// ==========================================
// 1. ส่วนเตรียมตัวแปร & Logic
// ==========================================
$current_mood = isset($_GET['mood_id']) ? $_GET['mood_id'] : '';
$current_coping = isset($_GET['coping_method']) ? $_GET['coping_method'] : '';
$search_query = isset($_GET['q']) ? $_GET['q'] : '';

// --- Logic วิเคราะห์จากคำตอบ (Quiz) ---
if (isset($_GET['quiz_action'])) {
    $q_desire = $_GET['q_desire'] ?? '';
    // Map ความต้องการ -> Mood ID
    $mood_map = [
        'party' => 1,
        'cry' => 2,
        'relax' => 3,
        'sleep' => 4,
        'run' => 5,
        'scream' => 6
    ];

    if (isset($mood_map[$q_desire])) {
        $current_mood = $mood_map[$q_desire];
        $mood_names = [
            1 => 'มีความสุข (Happy)',
            2 => 'เศร้า (Sad)',
            3 => 'เครียด (Stressed)',
            4 => 'เหนื่อยล้า (Tired)',
            5 => 'มีพลัง (Energetic)',
            6 => 'โกรธ (Angry)'
        ];
        $detected_name = $mood_names[$current_mood];
        $_SESSION['alert'] = ['type' => 'success', 'title' => '🤖 วิเคราะห์เสร็จสิ้น!', 'text' => "ดูเหมือนคุณกำลัง \"$detected_name\""];
    }
}

// Query String สำหรับ Redirect
$redirect_params = "";
if ($current_mood != '') {
    $redirect_params = "?mood_id=" . $current_mood . "&coping_method=" . urlencode($current_coping) . "&q=" . urlencode($search_query);
}

// ==========================================
// 2. Logic บันทึก History & Favorites
// ==========================================
if (isset($_GET['go_place']) && isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("INSERT INTO user_history (user_id, place_id) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $_GET['go_place']]);
    $_SESSION['alert'] = ['type' => 'success', 'title' => '🗺️ เริ่มการเดินทาง!', 'text' => 'บันทึกประวัติเรียบร้อยแล้ว'];
    header("Location: index.php" . $redirect_params);
    exit;
}

if (isset($_GET['fav_place']) && isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $pid = $_GET['fav_place'];
    $check = $pdo->prepare("SELECT * FROM favorites WHERE user_id = ? AND place_id = ?");
    $check->execute([$uid, $pid]);

    if ($check->fetch()) {
        $del = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND place_id = ?");
        $del->execute([$uid, $pid]);
        $_SESSION['alert'] = ['type' => 'success', 'title' => '💔 ยกเลิก', 'text' => 'ลบออกจากรายการโปรดแล้ว'];
    } else {
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
        $countStmt->execute([$uid]);
        if ($countStmt->fetchColumn() >= 10) {
            $_SESSION['alert'] = ['type' => 'warning', 'title' => '⚠️ เต็มแล้ว!', 'text' => 'รายการโปรดเต็ม 10 รายการ'];
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
// 3. ดึงข้อมูล (Fetch Data)
// ==========================================
$moods = $pdo->query("SELECT * FROM moods")->fetchAll();
$coping_methods = $pdo->query("SELECT * FROM coping_methods")->fetchAll();
$places = [];
$user_fav_ids = [];

if (isset($_SESSION['user_id'])) {
    $stmt_favs = $pdo->prepare("SELECT place_id FROM favorites WHERE user_id = ?");
    $stmt_favs->execute([$_SESSION['user_id']]);
    $user_fav_ids = $stmt_favs->fetchAll(PDO::FETCH_COLUMN);
}

// Logic การค้นหา
if ($search_query != '') {
    $sql = "SELECT * FROM places WHERE name LIKE ? OR description LIKE ? OR address LIKE ?";
    $term = "%$search_query%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$term, $term, $term]);
    $places = $stmt->fetchAll();

} elseif ($current_mood != '') {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['alert'] = ['type' => 'warning', 'title' => '🔒 กรุณาเข้าสู่ระบบ', 'text' => 'ต้องล็อกอินก่อนจึงจะค้นหาได้'];
        header("Location: login.php");
        exit;
    }
    $sql = "SELECT p.* FROM places p JOIN place_moods pm ON p.id = pm.place_id WHERE pm.mood_id = ?";
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
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f8f9fa;
        }

        .hero-section {
            background: linear-gradient(135deg, rgba(142, 68, 173, 0.9), rgba(44, 62, 80, 0.8)), url('https://source.unsplash.com/random/1600x900/?landscape,purple');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 60px 0 100px 0;
            border-bottom-left-radius: 50px;
            border-bottom-right-radius: 50px;
            margin-bottom: 50px;
        }

        .main-card {
            margin-top: -80px;
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.98);
        }

        /* ปุ่ม Emoji */
        .emoji-btn {
            background: #fff;
            border: 2px solid #f1f1f1;
            border-radius: 15px;
            padding: 10px 5px;
            cursor: pointer;
            transition: all 0.2s;
            height: 100%;
        }

        .emoji-btn:hover {
            border-color: #8e44ad;
            background: #fcf4ff;
            transform: translateY(-3px);
        }

        .emoji-btn.active {
            background: #8e44ad;
            color: white;
            border-color: #8e44ad;
        }

        .emoji-icon {
            font-size: 2.2rem;
            display: block;
            margin-bottom: 5px;
        }

        /* Place Card */
        .place-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s;
            height: 100%;
            background: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .place-card:hover {
            transform: translateY(-5px);
        }

        .place-img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }

        .text-purple {
            color: #8e44ad !important;
        }

        .bg-purple-light {
            background-color: #f8f0fc;
        }

        /* ปรับแต่ง Quiz ให้ดูเป็นส่วนเสริม */
        .quiz-section {
            border-top: 2px dashed #e0e0e0;
            padding-top: 30px;
            margin-top: 30px;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php include 'navbar.php'; ?>

    <main class="flex-shrink-0">
        <div class="hero-section text-center">
            <div class="container">
                <h1 class="display-5 fw-bold mb-3">✨ ค้นหาสถานที่ฮีลใจ ✨</h1>
                <p class="lead opacity-75">พิมพ์ค้นหา หรือเลือกอารมณ์ของคุณด้านล่าง</p>
            </div>
        </div>

        <div class="container mb-5">
            <div class="card p-4 p-md-5 main-card mb-5">

                <div class="mb-4">
                    <form method="get" class="d-flex gap-2">
                        <input type="text" name="q"
                            class="form-control form-control-lg rounded-pill px-4 border-2 border-primary"
                            placeholder="🔍 พิมพ์ชื่อสถานที่ (เช่น สวน, คาเฟ่, วัด)"
                            value="<?= htmlspecialchars($search_query) ?>">
                        <button class="btn btn-primary rounded-pill px-4 fw-bold" type="submit">ค้นหา</button>
                    </form>
                </div>

                <div class="mb-4">
                    <h5 class="fw-bold text-purple mb-3">🎭 เลือกตามอารมณ์ของคุณ</h5>
                    <div class="row g-2">
                        <?php foreach ($moods as $m): ?>
                            <?php
                            $parts = explode(' ', $m['mood_name'], 2);
                            $emoji = $parts[0];
                            $name = isset($parts[1]) ? $parts[1] : $m['mood_name'];
                            $activeClass = ($current_mood == $m['id']) ? 'active' : '';
                            ?>
                            <div class="col-4 col-md-2">
                                <a href="?mood_id=<?= $m['id'] ?>" class="text-decoration-none">
                                    <div class="emoji-btn text-center <?= $activeClass ?>">
                                        <span class="emoji-icon"><?= $emoji ?></span>
                                        <small class="d-block fw-bold text-dark"
                                            style="font-size: 0.8rem;"><?= $name ?></small>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($current_mood != ''): ?>
                    <div class="alert alert-light border-start border-4 border-primary shadow-sm mb-4">
                        <form method="get">
                            <input type="hidden" name="mood_id" value="<?= $current_mood ?>">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <label class="fw-bold text-purple fs-5">🛠️ อยากจัดการแบบไหน?</label>
                                </div>
                                <div class="col-md-6">
                                    <select name="coping_method"
                                        class="form-select form-select-lg rounded-pill border-primary"
                                        onchange="this.form.submit()">
                                        <option value="">-- เลือกวิธีจัดการ (ระบบจะค้นหาทันที) --</option>
                                        <?php
                                        foreach ($coping_methods as $cm) {
                                            if ($cm['mood_id'] == $current_mood) {
                                                $sel = ($current_coping == $cm['method_name']) ? 'selected' : '';
                                                echo "<option value='{$cm['method_name']}' $sel>{$cm['method_name']}</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="quiz-section text-center">
                    <p class="text-muted mb-2">ไม่แน่ใจว่ารู้สึกยังไง?</p>
                    <button class="btn btn-outline-secondary rounded-pill btn-sm px-4" type="button"
                        data-bs-toggle="collapse" data-bs-target="#quizCollapse">
                        🤖 ให้ AI ช่วยวิเคราะห์ (คลิก)
                    </button>

                    <div class="collapse mt-3" id="quizCollapse">
                        <div class="bg-purple-light p-4 rounded-4 text-start">
                            <form method="get">
                                <input type="hidden" name="quiz_action" value="1">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="fw-bold mb-2">ระดับพลังงาน?</label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="q_energy" id="e1" value="low"
                                                checked>
                                            <label class="btn btn-outline-purple" for="e1">ต่ำ</label>
                                            <input type="radio" class="btn-check" name="q_energy" id="e3" value="high">
                                            <label class="btn btn-outline-purple" for="e3">สูง</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="fw-bold mb-2">ความต้องการ?</label>
                                        <select name="q_desire" class="form-select rounded-pill border-purple">
                                            <option value="sleep">อยากนอน / พัก</option>
                                            <option value="relax">อยากนั่งโง่ๆ</option>
                                            <option value="scream">อยากระบาย / ตะโกน</option>
                                            <option value="party">อยากฉลอง</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit"
                                            class="btn btn-primary w-100 rounded-pill">วิเคราะห์</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

            <?php if (!empty($places)): ?>
                <div class="d-flex align-items-center mb-4">
                    <h4 class="mb-0 fw-bold text-secondary">📍 ผลการค้นหา:</h4>
                    <?php if ($current_coping): ?>
                        <span class="badge bg-primary ms-2"><?= htmlspecialchars($current_coping) ?></span>
                    <?php endif; ?>
                </div>

                <div class="row g-4">
                    <?php foreach ($places as $place): ?>
                        <?php
                        $is_fav = in_array($place['id'], $user_fav_ids);
                        $fav_btn_class = $is_fav ? 'btn-danger' : 'btn-outline-danger';
                        ?>
                        <div class="col-md-4">
                            <div class="place-card shadow-sm h-100">
                                <div class="position-relative">
                                    <img src="<?= htmlspecialchars($place['image_url']) ?>" class="place-img"
                                        onerror="this.src='https://via.placeholder.com/400x300'">
                                    <span class="position-absolute top-0 end-0 m-2 badge bg-white text-dark shadow-sm fw-bold">
                                        <?= mb_substr($place['address'], 0, 15) ?>...
                                    </span>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="fw-bold"><?= htmlspecialchars($place['name']) ?></h5>
                                    <p class="text-muted small flex-grow-1"><?= htmlspecialchars($place['description']) ?></p>

                                    <div class="d-flex gap-2 mt-3 pt-3 border-top">
                                        <a href="?go_place=<?= $place['id'] ?>&mood_id=<?= $current_mood ?>&coping_method=<?= urlencode($current_coping) ?>"
                                            class="btn btn-success flex-grow-1 rounded-pill btn-sm py-2 fw-bold">🗺️ นำทาง</a>
                                        <a href="?fav_place=<?= $place['id'] ?>&mood_id=<?= $current_mood ?>&coping_method=<?= urlencode($current_coping) ?>"
                                            class="btn <?= $fav_btn_class ?> rounded-circle d-flex align-items-center justify-content-center"
                                            style="width: 40px; height: 40px;">❤️</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif (($current_mood != '' || $search_query != '') && empty($places)): ?>
                <div class="alert alert-light text-center py-5 shadow-sm rounded-4">
                    <h1 class="display-1">🤷‍♂️</h1>
                    <h4 class="mt-3 text-muted">ไม่พบสถานที่ตามเงื่อนไขนี้</h4>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>