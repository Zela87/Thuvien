<?php
// ============================================================
//  auth-lich-su-he-thong.php
//  Xử lý dữ liệu – KHÔNG chứa HTML
//  Logic truy vấn giữ nguyên 100% từ file gốc lich-su-he-thong-1.php
// ============================================================
include '../config/db.php';
session_start();

// ----- Bảo vệ trang -----
if (!isset($_SESSION['role'])) {
    header("Location: index.php");
    exit;
}

// ============================================================
//  1. Nhật Ký Hoạt Động
//     Nguồn gốc: SELECT * FROM LichSuHoatDong ORDER BY ThoiGian DESC
//     Cột dùng trong view: ThoiGian, NguoiThucHien, HanhDong, ChiTiet
// ============================================================
function getActivityLog($conn): array {
    $rows = [];
    $res = $conn->query(
        "SELECT ThoiGian, NguoiThucHien, HanhDong, ChiTiet
         FROM LichSuHoatDong
         ORDER BY ThoiGian DESC"
    );
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    return $rows;
}

// ============================================================
//  2. Lịch Sử Mượn Trả
//     Nguồn gốc: JOIN 6 bảng, LIMIT 50, ORDER BY NgayMuon DESC
//     Cột dùng trong view: NgayMuon, HoTenDocGia, TenSach,
//                          NgayTraThucTe, TinhTrangSach
//     Thêm: MaBanSach, NgayHenTra (view mới cần, lấy thêm không ảnh hưởng)
// ============================================================
function getBorrowHistory($conn): array {
    $rows = [];
    $sql = "SELECT
                ct.MaBanSach,
                ds.TenSach,
                dg.HoTen        AS HoTenDocGia,
                p.NgayMuon,
                p.NgayHenTra,
                ct.NgayTraThucTe,
                ct.TinhTrangSach
            FROM ChiTietMuonTra ct
            JOIN PhieuMuon   p  ON ct.MaPhieuMuon = p.MaPhieuMuon
            JOIN BanSach     b  ON ct.MaBanSach   = b.MaBanSach
            JOIN DauSach     ds ON b.MaDauSach    = ds.MaDauSach
            JOIN TheThuVien  t  ON p.SoThe        = t.SoThe
            JOIN DocGia      dg ON t.MaDocGia     = dg.MaDocGia
            ORDER BY p.NgayMuon DESC
            LIMIT 50";
    $res = $conn->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    return $rows;
}

// ============================================================
//  3. Sách Đã Thanh Lý
//     Nguồn gốc: BanSach JOIN DauSach WHERE TinhTrang = 3
//     Cột dùng trong view: MaBanSach, TenSach
// ============================================================
function getLiquidatedBooks($conn): array {
    $rows = [];
    $res = $conn->query(
        "SELECT b.MaBanSach, ds.TenSach
         FROM BanSach  b
         JOIN DauSach ds ON b.MaDauSach = ds.MaDauSach
         WHERE b.TinhTrang = 3"
    );
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    return $rows;
}

// ============================================================
//  Gọi cả 3 hàm – kết quả được dùng trong file view
// ============================================================
$history_nhat_ky    = getActivityLog($conn);
$history_muon_tra   = getBorrowHistory($conn);
$history_thanh_ly   = getLiquidatedBooks($conn);