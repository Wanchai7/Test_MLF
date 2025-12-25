<?php
require 'db.php';
$email = $_GET['email'] ?? ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_post = $_POST['email'];
    $otp_code = trim($_POST['otp_code']);

    // เช็ค OTP ในฐานข้อมูล
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND otp = :otp");
    $stmt->bindParam(':email', $email_post);
    $stmt->bindParam(':otp', $otp_code);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // ถูกต้อง: ยืนยันตัวตน + ลบ OTP
        $update = $pdo->prepare("UPDATE users SET is_verified = 1, otp = NULL WHERE email = :email");
        $update->bindParam(':email', $email_post);
        $update->execute();

        $_SESSION['alert'] = ['type' => 'success', 'title' => 'ยืนยันสำเร็จ!', 'text' => 'บัญชีของคุณพร้อมใช้งานแล้ว'];
        header("Location: index.php"); // ส่งไปหน้า Login
        exit();
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'รหัสผิด', 'text' => 'รหัส OTP ไม่ถูกต้อง กรุณาลองใหม่'];
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยัน OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --main-purple: #8e44ad; --light-purple: #f4ecf7; }
        body { font-family: 'Prompt', sans-serif; background-color: var(--light-purple); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .btn-purple { background-color: var(--main-purple); color: white; width: 100%; border-radius: 50px; }
        .btn-purple:hover { background-color: #6c3483; color: white; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card p-4 text-center">
                <h3 class="fw-bold" style="color: var(--main-purple);">🔒 ยืนยันรหัส OTP</h3>
                <p class="text-muted small">รหัสถูกส่งไปที่: <b><?= htmlspecialchars($email) ?></b></p>
                
                <form action="verify_otp.php" method="POST">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                    <div class="my-4">
                        <input type="text" name="otp_code" class="form-control form-control-lg text-center" 
                               placeholder="X X X X X X" maxlength="6" required style="letter-spacing: 5px; font-size: 24px;">
                    </div>
                    <button type="submit" class="btn btn-purple py-2 fw-bold">ยืนยันตัวตน</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (isset($_SESSION['alert'])): ?>
    <script>
        Swal.fire({
            icon: '<?= $_SESSION['alert']['type'] ?>',
            title: '<?= $_SESSION['alert']['title'] ?>',
            text: '<?= $_SESSION['alert']['text'] ?>',
            confirmButtonColor: '#8e44ad'
        });
    </script>
    <?php unset($_SESSION['alert']); ?>
<?php endif; ?>
</body>
</html>