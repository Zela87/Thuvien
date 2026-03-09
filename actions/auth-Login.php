<?php
session_start();
require_once __DIR__ . '/../config/db.php';
$conn->set_charset("utf8mb4");

if (isset($_POST['login'])) {
    $u = $conn->real_escape_string($_POST['username']);
    $p = $_POST['password']; // Nên sử dụng password_hash/verify nếu có thể

    $sql = "SELECT t.*, n.TenNhom 
            FROM TaiKhoan t 
            JOIN NhomNguoiDung n ON t.MaNhom = n.MaNhom 
            WHERE t.TenDangNhap = '$u' AND t.MatKhau = '$p' AND t.TrangThai = 1";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $row['TenDangNhap'];
        $_SESSION['ho_ten'] = isset($row['HoTen']) ? $row['HoTen'] : $row['TenDangNhap'];
        $_SESSION['role'] = $row['TenNhom']; 

        header("Location: ../views/index.php");
        exit();
    } else {
        $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
    }
}
?>