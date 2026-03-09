<?php
session_start();
require_once __DIR__ . '/../config/db.php';
$conn->set_charset("utf8mb4");


if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../views/index.php"); 
    exit();
}


if (isset($_SESSION['username'])) {
    $user_login = $_SESSION['username'];
    $conn->query("UPDATE TaiKhoan SET LastActive = NOW() WHERE TenDangNhap = '$user_login'");
}


$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$ho_ten = isset($_SESSION['ho_ten']) ? $_SESSION['ho_ten'] : 'Khách';


$unread_support = 0;
if ($user_role == 'QuanTriVien') {
    $sql_noti = "SELECT COUNT(*) as cnt FROM BaoCaoSuCo WHERE TrangThai='Chờ xử lý' AND LoaiSuCo IN ('Web lỗi', 'Khác')";
    $res_noti = $conn->query($sql_noti);
    $unread_support = $res_noti->fetch_assoc()['cnt'];
} 
elseif ($user_role == 'ThuThu') {
    $sql_noti = "SELECT COUNT(*) as cnt FROM BaoCaoSuCo WHERE TrangThai='Chờ xử lý' AND LoaiSuCo IN ('Vấn đề về thẻ', 'Hết sách', 'Khác')";
    $res_noti = $conn->query($sql_noti);
    $unread_support = $res_noti->fetch_assoc()['cnt'];
}
?>