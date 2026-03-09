<?php
require_once '../config/db.php';

$conn->set_charset("utf8mb4");

// Hàm chuyển hướng
function redirectWithMsg($msg) {
    $_SESSION['msg'] = $msg;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
$u = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$ho_ten = isset($_SESSION['ho_ten']) ? $_SESSION['ho_ten'] : 'Người dùng';
$user_role = $role;
$msg = "";
if(isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}

// --- XỬ LÝ TÌM KIẾM VÀ LỌC ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterStatus = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';

$whereCondition = "";
if ($search != '') {
    $key = $conn->real_escape_string($search);
    $whereCondition .= " AND (pm.SoThe LIKE '%$key%' OR d.TenSach LIKE '%$key%' OR doc.HoTen LIKE '%$key%' OR b.MaBanSach LIKE '%$key%') ";
}
if ($filterStatus == 'late') {
    $whereCondition .= " AND pm.NgayHenTra < CURDATE() ";
} elseif ($filterStatus == 'valid') {
    $whereCondition .= " AND pm.NgayHenTra >= CURDATE() ";
}

// --- XỬ LÝ TRẢ SÁCH ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_tra_modal'])) {
    $maBanSach = $_POST['ma_ban_sach_hide'];
    $tinhTrang = $_POST['tinh_trang_option'];
    $lyDo = $_POST['ly_do_note'];

    $sqlInfo = "SELECT d.GiaSach, pm.NgayHenTra 
                FROM BanSach b 
                JOIN DauSach d ON b.MaDauSach = d.MaDauSach 
                JOIN ChiTietMuonTra ct ON b.MaBanSach = ct.MaBanSach
                JOIN PhieuMuon pm ON ct.MaPhieuMuon = pm.MaPhieuMuon
                WHERE b.MaBanSach = '$maBanSach' AND ct.NgayTraThucTe IS NULL";

    $resInfo = $conn->query($sqlInfo);
    if ($resInfo->num_rows > 0) {
        $info = $resInfo->fetch_assoc();
        $giaSach = floatval($info['GiaSach']);
        $ngayHenTra = $info['NgayHenTra'];
    } else {
        $giaSach = 0;
        $ngayHenTra = date('Y-m-d');
    }

    $tienPhat = 0;
    $trangThaiSachKho = 1;
    $textTinhTrang = "Bình thường";

    // A. Phạt hư hỏng
    switch ($tinhTrang) {
        case 'Mat':
            $tienPhat += $giaSach;
            $trangThaiSachKho = 2;
            $textTinhTrang = "Mất";
            break;
        case 'Hong':
            $tienPhat += $giaSach * 0.5;
            $trangThaiSachKho = 3;
            $textTinhTrang = "Hỏng";
            break;
        case 'Rach':
            $tienPhat += $giaSach * 0.2;
            $trangThaiSachKho = 1;
            $textTinhTrang = "Rách";
            break;
    }

    // B. Phạt quá hạn
    $date1 = new DateTime($ngayHenTra);
    $date2 = new DateTime(date('Y-m-d'));

    if ($date2 > $date1) {
        $diff = $date2->diff($date1);
        $soNgayMuon = $diff->days;

        $phanTramPhat = 0.1; // 10%
        $tienPhatQuaHan = ($giaSach * $phanTramPhat) * $soNgayMuon;
        $tienPhat += $tienPhatQuaHan;

        $ghiChuQuaHan = "Muộn $soNgayMuon ngày (Phạt " . number_format($tienPhatQuaHan) . "đ)";

        if ($textTinhTrang == "Bình thường") {
            $textTinhTrang = $ghiChuQuaHan;
        } else {
            $textTinhTrang = $ghiChuQuaHan . " - " . $textTinhTrang;
        }
    }

    // Cập nhật ChiTietMuonTra
    $updateCT = $conn->prepare("UPDATE ChiTietMuonTra SET NgayTraThucTe = CURDATE(), TinhTrangSach = ?, TienPhat = ?, GhiChu = ? WHERE MaBanSach = ? AND NgayTraThucTe IS NULL");
    $updateCT->bind_param("sdss", $textTinhTrang, $tienPhat, $lyDo, $maBanSach);

    if ($updateCT->execute()) {
        // Lưu lịch sử vào BanSach
        $thoiGianTra = date('d/m/Y H:i');
        $logMoi = "$thoiGianTra - Trạng thái: $textTinhTrang";
        if (!empty($lyDo)) {
            $logMoi .= " (Ghi chú: $lyDo)";
        }
        $logMoi .= "\n";

        $stmtUpBS = $conn->prepare("UPDATE BanSach 
                                    SET TinhTrang = ?, 
                                        TinhTrangVatLy = ?, 
                                        GhiChuTinhTrang = ?,
                                        LichSuTinhTrang = CONCAT(IFNULL(LichSuTinhTrang, ''), ?) 
                                    WHERE MaBanSach = ?");
        $stmtUpBS->bind_param("issss", $trangThaiSachKho, $textTinhTrang, $lyDo, $logMoi, $maBanSach);
        $stmtUpBS->execute();

        $msgRes = "Đã trả sách: $textTinhTrang.";
        if ($tienPhat > 0) $msgRes .= " Tổng nộp: " . number_format($tienPhat) . "đ";

        redirectWithMsg("<div class='alert success'>$msgRes</div>");
    } else {
        redirectWithMsg("<div class='alert error'>Lỗi hệ thống: " . $conn->error . "</div>");
    }
}
?>