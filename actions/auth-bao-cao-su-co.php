<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$u = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$ho_ten = isset($_SESSION['ho_ten']) ? $_SESSION['ho_ten'] : 'Người dùng';
$user_role = $role;

// Xử lý gửi báo cáo (Độc giả)
if (isset($_POST['send_report']) && $role == 'DocGia') {
    $tieude = $_POST['tieude']; 
    $noidung = $_POST['noidung'];
    $loai = $_POST['loai_su_co']; 
    $stmt = $conn->prepare("INSERT INTO BaoCaoSuCo (NguoiBaoCao, TieuDe, LoaiSuCo, NoiDung, TrangThai) VALUES (?, ?, ?, ?, 'Chờ xử lý')");
    $stmt->bind_param("ssss", $u, $tieude, $loai, $noidung);
    $stmt->execute();
    header("Location: ../pages/bao-cao-su-co.php"); 
    exit();
}

// Xử lý phản hồi (Quản trị viên/Thủ thư)
if (isset($_POST['reply_report']) && ($role == 'QuanTriVien' || $role == 'ThuThu')) {
    $id = $_POST['id_baocao']; 
    $phanhoi = $_POST['phanhoi'];
    $stmt = $conn->prepare("UPDATE BaoCaoSuCo SET PhanHoi=?, TrangThai='Đã xong' WHERE MaBaoCao=?");
    $stmt->bind_param("si", $phanhoi, $id);
    $stmt->execute();
    
    // Ghi lại lịch sử
    $stmt_log = $conn->prepare("INSERT INTO LichSuHoatDong (NguoiThucHien, HanhDong, ChiTiet) VALUES (?, 'Phản hồi hỗ trợ', ?)");
    $detail = "Mã BC: " . $id;
    $stmt_log->bind_param("ss", $u, $detail);
    $stmt_log->execute();
    
    header("Location: ../pages/bao-cao-su-co.php"); 
    exit();
}
?>