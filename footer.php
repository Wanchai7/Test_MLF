<footer class="bg-custom-purple text-white text-center py-4 mt-auto">
    <div class="container">
        <h5 class="fw-bold">🔮 Mood Location Finder</h5>
        <p class="small opacity-75">
            ค้นหาสถานที่ที่ใช่ ในวันที่ใจต้องการ <br>
            ออกแบบเพื่อความรู้สึกของคุณ 💜
        </p>
        <div class="mt-3">
            <span class="mx-2">© 2025 All Rights Reserved.</span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (isset($_SESSION['alert'])): ?>
    <script>
        Swal.fire({
            icon: '<?= $_SESSION['alert']['type'] ?>',
            title: '<?= $_SESSION['alert']['title'] ?>',
            text: '<?= $_SESSION['alert']['text'] ?>',
            confirmButtonColor: '#8e44ad', // สีปุ่ม Popup เป็นสีม่วง
            timer: 3000
        });
    </script>
    <?php unset($_SESSION['alert']); ?>
<?php endif; ?>