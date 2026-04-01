<?php
session_start();
require_once __DIR__ . '/../config/db.php';
$conn->set_charset("utf8mb4");

// Chặn quyền truy cập nếu không phải Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'QuanTriVien') { 
    header("Location: Index.php"); 
    exit(); 
}
$u = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$ho_ten = isset($_SESSION['ho_ten']) ? $_SESSION['ho_ten'] : 'Người dùng';
$user_role = $role;
// 1. XỬ LÝ TẠO TÀI KHOẢN MỚI
if(isset($_POST['create_user'])){
    $u = $conn->real_escape_string($_POST['user']); 
    $p = $_POST['pass']; 
    $name = $conn->real_escape_string($_POST['name']); 
    $role = $_POST['role'];
    
    $check = $conn->query("SELECT * FROM TaiKhoan WHERE TenDangNhap='$u'");
    if($check->num_rows > 0){
        $_SESSION['msg'] = "error|Tên đăng nhập đã tồn tại!";
    } else {
        $conn->begin_transaction();
        try {
            $conn->query("INSERT INTO TaiKhoan (TenDangNhap, MatKhau, MaNhom, TrangThai) VALUES ('$u', '$p', '$role', 1)");
            if ($role == 'DocGia') {
                $conn->query("INSERT INTO DocGia (HoTen, TenDangNhap) VALUES ('$name', '$u')");
            }
            $conn->commit();
            $_SESSION['msg'] = "success|Tạo tài khoản thành công!";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['msg'] = "error|Lỗi hệ thống: " . $e->getMessage();
        }
    }
    header("Location: ../pages/quan-ly-nguoi-dung.php"); exit();
}

// 2. KHÓA/MỞ TÀI KHOẢN
if(isset($_POST['toggle_status'])){
    $u = $_POST['target_user'];
    $new_status = $_POST['current_status'] == 1 ? 0 : 1;
    $conn->query("UPDATE TaiKhoan SET TrangThai = $new_status WHERE TenDangNhap = '$u'");
    $_SESSION['msg'] = "success|Cập nhật trạng thái thành công!";
    header("Location: ../views/quan-ly-nguoi-dung.php"); exit();
}
?>