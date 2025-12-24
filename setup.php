<?php
// ตั้งค่าการเชื่อมต่อ MySQL
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // 1. เชื่อมต่อ MySQL
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // สร้าง Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS moodlocationfinder CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE moodlocationfinder");

    // =========================================================
    // ส่วนสร้างตาราง (Create Tables)
    // =========================================================

    // ตาราง Users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('user', 'owner', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // ตาราง Moods
    $pdo->exec("CREATE TABLE IF NOT EXISTS moods (
        id INT AUTO_INCREMENT PRIMARY KEY,
        mood_name VARCHAR(50) NOT NULL,
        icon_url VARCHAR(255)
    )");

    // ตาราง Places
    $pdo->exec("CREATE TABLE IF NOT EXISTS places (
        id INT AUTO_INCREMENT PRIMARY KEY,
        owner_id INT NOT NULL,
        name VARCHAR(150) NOT NULL,
        description TEXT,
        address TEXT,
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // ตาราง Place_Moods
    $pdo->exec("CREATE TABLE IF NOT EXISTS place_moods (
        place_id INT NOT NULL,
        mood_id INT NOT NULL,
        PRIMARY KEY (place_id, mood_id),
        FOREIGN KEY (place_id) REFERENCES places(id) ON DELETE CASCADE,
        FOREIGN KEY (mood_id) REFERENCES moods(id) ON DELETE CASCADE
    )");

    // ตาราง Favorites
    $pdo->exec("CREATE TABLE IF NOT EXISTS favorites (
        user_id INT NOT NULL,
        place_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, place_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (place_id) REFERENCES places(id) ON DELETE CASCADE
    )");

    // ตาราง User_History
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        place_id INT NOT NULL,
        action_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (place_id) REFERENCES places(id) ON DELETE CASCADE
    )");

    echo "✅ ตรวจสอบ/สร้างตารางเรียบร้อย<br>";

    // =========================================================
    // ส่วนล้างข้อมูลเก่าและเพิ่ม User ใหม่ (Admin/Owner)
    // =========================================================

    // [แก้ไขจุดที่ Error] ปิด Foreign Key Check ชั่วคราว เพื่อให้ล้างข้อมูลได้
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    $pdo->exec("TRUNCATE TABLE users");
    $pdo->exec("TRUNCATE TABLE moods");
    $pdo->exec("TRUNCATE TABLE places");
    $pdo->exec("TRUNCATE TABLE place_moods");
    $pdo->exec("TRUNCATE TABLE favorites");
    $pdo->exec("TRUNCATE TABLE user_history");

    // เปิด Foreign Key Check กลับคืน
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // รหัสผ่าน 1234 (Hash)
    $password_1234 = password_hash('1234', PASSWORD_DEFAULT);

    // เพิ่ม Admin
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', 'admin@mood.com', $password_1234, 'admin']);
    echo "👤 เพิ่มผู้ใช้: <b>admin</b> (Pass: 1234) สถานะ: Admin <br>";

    // เพิ่ม Owner
    $stmt->execute(['owner', 'owner@mood.com', $password_1234, 'owner']);
    echo "👤 เพิ่มผู้ใช้: <b>owner</b> (Pass: 1234) สถานะ: Owner <br>";

    // เพิ่ม User
    $stmt->execute(['user', 'user@mood.com', $password_1234, 'user']);
    echo "👤 เพิ่มผู้ใช้: <b>user</b> (Pass: 1234) สถานะ: User <br>";

    // เพิ่มข้อมูล Moods
    $moods_data = ['มีความสุข (Happy)', 'เศร้า / เหงา (Sad)', 'เครียด (Stressed)', 'เหนื่อยล้า (Tired)', 'ตื่นเต้น (Energetic)'];
    $stmt_m = $pdo->prepare("INSERT INTO moods (mood_name) VALUES (?)");
    foreach ($moods_data as $m) {
        $stmt_m->execute([$m]);
    }
    echo "🎭 เพิ่มรายการอารมณ์เรียบร้อย<br>";

    // เพิ่มสถานที่ตัวอย่าง (ให้ owner เป็นเจ้าของ)
    // ดึง ID ของ owner ที่เพิ่งสร้าง
    $stmt_get_owner = $pdo->query("SELECT id FROM users WHERE username = 'owner'");
    $owner_id = $stmt_get_owner->fetchColumn();

    $stmt_p = $pdo->prepare("INSERT INTO places (owner_id, name, description, address, image_url) VALUES (?, ?, ?, ?, ?)");

    // สถานที่ 1
    $stmt_p->execute([$owner_id, 'สวนเบญจกิติ', 'สวนสาธารณะขนาดใหญ่ใจกลางเมือง', 'คลองเตย', 'https://images.unsplash.com/photo-1596422846543-75c6fc197f07?w=500']);
    $pid1 = $pdo->lastInsertId();

    // สถานที่ 2
    $stmt_p->execute([$owner_id, 'Silent Library', 'ห้องสมุดเงียบสงบ', 'สุขุมวิท', 'https://images.unsplash.com/photo-1507842217158-a359ce938b5b?w=500']);
    $pid2 = $pdo->lastInsertId();

    // จับคู่สถานที่กับอารมณ์
    $stmt_pm = $pdo->prepare("INSERT INTO place_moods (place_id, mood_id) VALUES (?, ?)");
    $stmt_pm->execute([$pid1, 1]); // สวน -> Happy
    $stmt_pm->execute([$pid1, 3]); // สวน -> Stressed
    $stmt_pm->execute([$pid2, 2]); // ห้องสมุด -> Sad
    $stmt_pm->execute([$pid2, 4]); // ห้องสมุด -> Tired

    echo "<hr><h3>🎉 ติดตั้งระบบเสร็จสมบูรณ์! (เวอร์ชันแก้ไขแล้ว)</h3>";
    echo "<a href='login.php'>คลิกที่นี่เพื่อเข้าสู่ระบบ</a>";

} catch (PDOException $e) {
    die("❌ เกิดข้อผิดพลาด: " . $e->getMessage());
}
?>