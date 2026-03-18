<?php
session_start();
require_once __DIR__ . '/../config/db.php';
$conn->set_charset("utf8mb4");

$u = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$ho_ten = isset($_SESSION['ho_ten']) ? $_SESSION['ho_ten'] : 'Người dùng';
$user_role = $role;
// Kiểm tra quyền (Chỉ Admin và Thủ thư)
if (!isset($_SESSION['role']) || $_SESSION['role'] == 'DocGia') { 
    header("Location: Index.php"); 
    exit(); 
}

if (isset($_POST['confirm_thanhly'])) {
    $maBanSach = $_POST['maBanSach']; 
    $lydo = $conn->real_escape_string($_POST['lydo']);
    
    // Kiểm tra xem sách có đang được mượn không (TinhTrang = 0 là đang mượn)
    $check = $conn->query("SELECT TinhTrang FROM BanSach WHERE MaBanSach='$maBanSach'")->fetch_assoc();
    
    if ($check['TinhTrang'] == 0) {
        $_SESSION['msg'] = "error|LỖI: Sách này đang có người mượn, không thể thanh lý!";
    } else {
        $conn->begin_transaction();
        try {
            // 1. Ghi vào lịch sử hệ thống
            $nguoiThucHien = $_SESSION['username'];
            $hanhDong = "Thanh lý sách";
            $chiTiet = "Mã: $maBanSach. Lý do: $lydo";
            $conn->query("INSERT INTO LichSuHoatDong (NguoiThucHien, HanhDong, ChiTiet) VALUES ('$nguoiThucHien', '$hanhDong', '$chiTiet')");
            
            // 2. Cập nhật trạng thái sách thành 'Đã thanh lý' (TinhTrang = 3)
            $conn->query("UPDATE BanSach SET TinhTrang = 3 WHERE MaBanSach = '$maBanSach'");
            
            $conn->commit();
            $_SESSION['msg'] = "success|Đã thanh lý sách $maBanSach thành công!";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['msg'] = "error|Lỗi hệ thống: " . $e->getMessage();
        }
    }
    header("Location: ../views/thanh-ly-sach.php"); exit();
}
?>