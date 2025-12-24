<?php
ob_start();
require 'db.php';

// เช็คสิทธิ์: เข้าได้ทั้ง admin และ owner
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'owner' && $_SESSION['role'] != 'admin')) {
    header("Location: index.php");
    exit;
}

// ... (ส่วน Logic การรับค่า POST และ Form HTML เหมือนเดิมทุกประการ ไม่ต้องแก้เพิ่ม) ...
// ให้ Copy เนื้อหาเดิมของไฟล์ place_form.php มาใส่ต่อท้ายบรรทัดนี้ได้เลยครับ 
// เพราะเราแก้แค่เงื่อนไข if บรรทัดบนสุดก็พอ