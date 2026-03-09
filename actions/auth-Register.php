<?php
session_start();
require_once __DIR__ . '/../config/db.php';
$conn->set_charset("utf8mb4");

$msg = "";

if (isset($_POST['register'])) {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password']; 
    $repass = $_POST['repassword'];
    $hoten = $conn->real_escape_string($_POST['hoten']);
    $ngaysinh = $_POST['ngaysinh'];
    $diachi = $conn->real_escape_string($_POST['diachi']);
    $cccd = $conn->real_escape_string($_POST['cccd']);

    if ($pass != $repass) {
        $msg = "<div class='alert error'>Mật khẩu nhập lại không khớp!</div>";
    } else {
        $check = $conn->query("SELECT * FROM TaiKhoan WHERE TenDangNhap='$user'");
        if ($check->num_rows > 0) {
            $msg = "<div class='alert error'>Tên đăng nhập '$user' đã tồn tại!</div>";
        } else {
          
            $resNhom = $conn->query("SELECT MaNhom FROM NhomNguoiDung WHERE TenNhom = 'DocGia' LIMIT 1");
            $maNhom = ($resNhom->num_rows > 0) ? $resNhom->fetch_assoc()['MaNhom'] : 'N03';

         
            $stmt = $conn->prepare("INSERT INTO TaiKhoan (TenDangNhap, MatKhau, MaNhom, TrangThai) VALUES (?, ?, ?, 1)");
            $stmt->bind_param("sss", $user, $pass, $maNhom);
            
            if ($stmt->execute()) {
              
                $maDocGia = "DG" . time(); 
                $stmtDG = $conn->prepare("INSERT INTO DocGia (MaDocGia, HoTen, NgaySinh, DiaChi, CMND_CCCD, TenDangNhap) VALUES (?, ?, ?, ?, ?, ?)");
                $stmtDG->bind_param("ssssss", $maDocGia, $hoten, $ngaysinh, $diachi, $cccd, $user);
                
                if ($stmtDG->execute()) {
                    $msg = "<div class='alert success'>Đăng ký thành công! <a href='login.php'>Đăng nhập ngay</a></div>";
                } else {
                    $conn->query("DELETE FROM TaiKhoan WHERE TenDangNhap='$user'");
                    $msg = "<div class='alert error'>Lỗi hồ sơ: " . $conn->error . "</div>";
                }
            } else {
                $msg = "<div class='alert error'>Lỗi hệ thống: " . $conn->error . "</div>";
            }
        }
    }
}
?>