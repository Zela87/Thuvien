<?php
session_start();
require_once __DIR__ . '/../config/db.php';
$conn->set_charset("utf8mb4");

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'QuanTriVien' && $_SESSION['role'] != 'ThuThu')) {
    header("Location: Index.php");
    exit();
}
$u = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$ho_ten = isset($_SESSION['ho_ten']) ? $_SESSION['ho_ten'] : 'Người dùng';
$user_role = $role;
// 1. CẤP THẺ TỰ ĐỘNG
if (isset($_POST['auto_card'])) {
    $maDocGia = $_POST['ma_doc_gia_auto']; 
    $tienCoc = $_POST['tien_coc_auto'];
    $soTheAuto = "T" . date("His") . rand(10,99);
    
    $sql = "INSERT INTO TheThuVien (SoThe, MaDocGia, NgayCap, NgayHetHan, TienDatCoc, TrangThai) 
            VALUES ('$soTheAuto', '$maDocGia', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), '$tienCoc', 1)";
    
    $_SESSION['msg'] = $conn->query($sql) ? "success|Cấp thẻ thành công: $soTheAuto" : "error|Lỗi: " . $conn->error;
    header("Location: ../views/quan-ly-doc-gia.php"); exit();
}

// 2. KHÓA/MỞ THẺ
if (isset($_POST['toggle_status'])) {
    $soThe = $_POST['so_the'];
    $status = $_POST['status'];
    $conn->query("UPDATE TheThuVien SET TrangThai = $status WHERE SoThe = '$soThe'");
    $_SESSION['msg'] = "success|Cập nhật trạng thái thẻ thành công!";
    header("Location: ../views/quan-ly-doc-gia.php"); exit();
}

// 3. GIA HẠN THẺ
if (isset($_POST['renew_card'])) {
    $soThe = $_POST['so_the'];
    $conn->query("UPDATE TheThuVien SET NgayHetHan = DATE_ADD(NgayHetHan, INTERVAL 1 YEAR) WHERE SoThe = '$soThe'");
    $_SESSION['msg'] = "success|Đã gia hạn thẻ thêm 1 năm!";
    header("Location: ../views/quan-ly-doc-gia.php"); exit();
}
?>