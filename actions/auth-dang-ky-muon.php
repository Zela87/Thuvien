<?php
session_start();
require_once __DIR__ . '/../config/db.php';
$conn->set_charset("utf8mb4");

// Kiểm tra đăng nhập
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'DocGia') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$user_role = $role;
$ho_ten = isset($_SESSION['ho_ten']) ? $_SESSION['ho_ten'] : 'Người dùng';

// 1. Lấy thông tin độc giả và thẻ
$sqlDocGia = "SELECT doc.MaDocGia, doc.HoTen, ttv.SoThe, ttv.SoSachMuonToiDa, ttv.TrangThai, ttv.NgayHetHan
              FROM DocGia doc 
              LEFT JOIN TheThuVien ttv ON doc.MaDocGia = ttv.MaDocGia
              WHERE doc.TenDangNhap = '$username'";
$resDocGia = $conn->query($sqlDocGia);
$docGia = $resDocGia->fetch_assoc();

// 2. Kiểm tra trạng thái thẻ
$today = date("Y-m-d");
$error_card = "";
if (!$docGia || !$docGia['SoThe']) $error_card = "Bạn chưa có thẻ thư viện.";
else if ($docGia['TrangThai'] == 0) $error_card = "Thẻ của bạn đang bị khóa.";
else if ($docGia['NgayHetHan'] < $today) $error_card = "Thẻ của bạn đã hết hạn.";

// 3. Tính toán số lượng sách có thể mượn
$maDocGia = $docGia['MaDocGia'] ?? 0;
$maxSach = $docGia['SoSachMuonToiDa'] ?? 0;

$sqlDangMuon = "SELECT COUNT(*) as DangMuon FROM PhieuMuon pm 
                JOIN ChiTietMuonTra ct ON pm.MaPhieuMuon = ct.MaPhieuMuon 
                WHERE pm.SoThe = '{$docGia['SoThe']}' AND ct.NgayTraThucTe IS NULL";
$soSachDangMuon = $conn->query($sqlDangMuon)->fetch_assoc()['DangMuon'] ?? 0;

$sqlChoDuyet = "SELECT COUNT(*) as ChoDuyet FROM yeucaumuon WHERE MaDocGia = $maDocGia AND TrangThai = 'ChoDuyet'";
$soSachChoDuyet = $conn->query($sqlChoDuyet)->fetch_assoc()['ChoDuyet'] ?? 0;

$soSachCoTheMuon = $maxSach - $soSachDangMuon - $soSachChoDuyet;

// 4. Xử lý Đăng ký mượn
$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_dang_ky'])) {
    $selected = $_POST['selected_books'] ?? [];
    if (count($selected) > 0 && count($selected) <= $soSachCoTheMuon) {
        foreach ($selected as $maBanSach) {
            $maBanSach = $conn->real_escape_string($maBanSach);
            $conn->query("INSERT INTO yeucaumuon (MaDocGia, MaBanSach, TrangThai) VALUES ($maDocGia, '$maBanSach', 'ChoDuyet')");
        }
        $msg = "success";
    }
}

// 5. Xử lý Hủy yêu cầu
if (isset($_GET['cancel'])) {
    $id = intval($_GET['cancel']);
    $conn->query("DELETE FROM yeucaumuon WHERE MaYeuCau = $id AND MaDocGia = $maDocGia AND TrangThai = 'ChoDuyet'");
    header("Location: ../pages/dang-ky-muon.php");
    exit();
}
?>