<?php
ob_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        // [ใหม่] เก็บยศลง Session ด้วย
        $_SESSION['role'] = $user['role'];

        $_SESSION['alert'] = ['type' => 'success', 'title' => 'ยินดีต้อนรับ', 'text' => 'เข้าสู่ระบบสำเร็จ (' . ucfirst($user['role']) . ')'];
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'ผิดพลาด', 'text' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'];
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100 bg-light">
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow p-4">
                    <h3 class="text-center mb-3">เข้าสู่ระบบ</h3>
                    <form method="post">
                        <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control"
                                required></div>
                        <div class="mb-3"><label>Password</label><input type="password" name="password"
                                class="form-control" required></div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <p class="mt-3 text-center">ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิก</a></p>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>