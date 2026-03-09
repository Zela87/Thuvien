<?php
session_start();
require_once __DIR__ . '/../config/db.php';
$conn->set_charset("utf8mb4");

if (!isset($_SESSION['role'])) {
    header("Location: Index.php");
    exit();
}

$u = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$ho_ten = isset($_SESSION['ho_ten']) ? $_SESSION['ho_ten'] : 'Người dùng';
$user_role = $role;


// 1. Lấy Lịch sử hoạt động chung (Dành cho Quản trị viên)
$sql_logs = "SELECT * FROM LichSuHoatDong ORDER BY ThoiGian DESC LIMIT 50";
$res_logs = $conn->query($sql_logs);

// 2. Lấy Lịch sử mượn trả (Tùy theo Role)
if ($role == 'DocGia') {
    $sql_borrow = "SELECT pm.MaPhieuMuon, d.TenSach, ct.NgayMuon, ct.NgayTraDuKien, ct.NgayTraThucTe, ct.TienPhat
                   FROM PhieuMuon pm
                   JOIN ChiTietMuonTra ct ON pm.MaPhieuMuon = ct.MaPhieuMuon
                   JOIN BanSach b ON ct.MaBanSach = b.MaBanSach
                   JOIN DauSach d ON b.MaDauSach = d.MaDauSach
                   JOIN DocGia dg ON pm.SoThe = (SELECT SoThe FROM TheThuVien WHERE MaDocGia = dg.MaDocGia)
                   WHERE dg.TenDangNhap = '$u'
                   ORDER BY ct.NgayMuon DESC";
} else {
    $sql_borrow = "SELECT pm.MaPhieuMuon, d.TenSach, ct.NgayMuon, ct.NgayTraThucTe, pm.SoThe
                   FROM PhieuMuon pm
                   JOIN ChiTietMuonTra ct ON pm.MaPhieuMuon = ct.MaPhieuMuon
                   JOIN BanSach b ON ct.MaBanSach = b.MaBanSach
                   JOIN DauSach d ON b.MaDauSach = d.MaDauSach
                   ORDER BY ct.NgayMuon DESC LIMIT 50";
}
$res_borrow = $conn->query($sql_borrow);

// 3. Lấy Lịch sử yêu cầu (Dành cho Độc giả)
$res_req = null;
if ($role == 'DocGia') {
    $sql_req = "SELECT yc.*, d.TenSach 
                FROM yeucaumuon yc 
                JOIN bansach b ON yc.MaBanSach = b.MaBanSach 
                JOIN dausach d ON b.MaDauSach = d.MaDauSach
                JOIN DocGia dg ON yc.MaDocGia = dg.MaDocGia
                WHERE dg.TenDangNhap = '$u' 
                ORDER BY yc.MaYeuCau DESC";
    $res_req = $conn->query($sql_req);
}
?>