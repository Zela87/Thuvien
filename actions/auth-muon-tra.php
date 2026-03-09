<?php
session_start();
require_once __DIR__ . '/../config/db.php';
$conn->set_charset("utf8mb4");

$msg = "";
$u = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$ho_ten = isset($_SESSION['ho_ten']) ? $_SESSION['ho_ten'] : 'Người dùng';
$user_role = $role;
$transaction_info = null;
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'muon_truc_tiep';

// Biến quản lý các bước mượn sách
$step = 1; // 1: Nhập thẻ, 2: Nhập sách, 3: Xác nhận
$card_data = null;
$book_data = null;
$errors = [];

// --- XỬ LÝ DUYỆT YÊU CẦU (Giữ nguyên) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_duyet'])) {
    $maYeuCau = $_POST['ma_yeu_cau'];
    $nguoiTao = isset($_SESSION['username']) ? $_SESSION['username'] : 'thuthu';
    
    $sqlYC = "SELECT yc.*, doc.*, ttv.SoThe, ttv.SoSachMuonToiDa, ttv.TrangThai as TrangThaiThe, ttv.NgayHetHan
              FROM yeucaumuon yc
              JOIN DocGia doc ON yc.MaDocGia = doc.MaDocGia
              JOIN TheThuVien ttv ON doc.MaDocGia = ttv.MaDocGia
              WHERE yc.MaYeuCau = $maYeuCau";
    $resYC = $conn->query($sqlYC);
    
    if ($resYC->num_rows > 0) {
        $yc = $resYC->fetch_assoc();
        $soThe = $yc['SoThe'];
        $maBanSach = $yc['MaBanSach'];
        $ngaytra = date('Y-m-d', strtotime('+14 days'));
        
        $errYC = [];
        if ($yc['TrangThaiThe'] == 0) $errYC[] = "Thẻ đã bị khóa";
        if (strtotime($yc['NgayHetHan']) < time()) $errYC[] = "Thẻ đã hết hạn";
        
        $sqlQuaHan = "SELECT COUNT(*) as CoSachQuaHan FROM PhieuMuon pm JOIN ChiTietMuonTra ct ON pm.MaPhieuMuon = ct.MaPhieuMuon WHERE pm.SoThe = '$soThe' AND ct.NgayTraThucTe IS NULL AND pm.NgayHenTra < CURDATE()";
        if ($conn->query($sqlQuaHan)->fetch_assoc()['CoSachQuaHan'] > 0) $errYC[] = "Độc giả đang có sách quá hạn";
        
        $sqlDangMuon = "SELECT COUNT(*) as DangMuon FROM PhieuMuon pm JOIN ChiTietMuonTra ct ON pm.MaPhieuMuon = ct.MaPhieuMuon WHERE pm.SoThe = '$soThe' AND ct.NgayTraThucTe IS NULL";
        $dangMuon = $conn->query($sqlDangMuon)->fetch_assoc()['DangMuon'];
        if ($dangMuon >= $yc['SoSachMuonToiDa']) $errYC[] = "Đã đạt giới hạn mượn";
        
        $checkSach = $conn->query("SELECT TinhTrang FROM BanSach WHERE MaBanSach = '$maBanSach'");
        if ($checkSach->num_rows == 0) {
            $errYC[] = "Không tìm thấy sách";
        } else {
            $sachInfo = $checkSach->fetch_assoc();
            if ($sachInfo['TinhTrang'] != 1) $errYC[] = "Sách không còn sẵn sàng";
        }
        
        if (empty($errYC)) {
            $stmt = $conn->prepare("INSERT INTO PhieuMuon (SoThe, NgayHenTra, NguoiTao, GhiChu) VALUES (?, ?, ?, 'Từ đăng ký online')");
            $stmt->bind_param("sss", $soThe, $ngaytra, $nguoiTao);
            if ($stmt->execute()) {
                $idPhieu = $conn->insert_id;
                $conn->query("INSERT INTO ChiTietMuonTra (MaPhieuMuon, MaBanSach) VALUES ('$idPhieu', '$maBanSach')");
                $conn->query("UPDATE BanSach SET TinhTrang=0 WHERE MaBanSach='$maBanSach'");
                $conn->query("UPDATE yeucaumuon SET TrangThai='DaDuyet' WHERE MaYeuCau=$maYeuCau");
                $msg = "<div class='alert success'><i class='fas fa-check'></i> Đã duyệt yêu cầu #$idPhieu thành công!</div>";
            }
        } else {
            $msg = "<div class='alert error'>Không thể duyệt: " . implode(", ", $errYC) . "</div>";
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_tu_choi'])) {
    $maYeuCau = $_POST['ma_yeu_cau'];
    $conn->query("UPDATE yeucaumuon SET TrangThai='TuChoi' WHERE MaYeuCau=$maYeuCau");
    $msg = "<div class='alert success'>Đã từ chối yêu cầu!</div>";
}

// --- LOGIC MỚI: QUY TRÌNH MƯỢN TRỰC TIẾP ---

// Hàm bổ trợ: Lấy thông tin thẻ
function getCardInfo($conn, $the) {
    $sqlDG = "SELECT doc.*, ttv.SoThe, ttv.NgayHetHan, ttv.TrangThai as TrangThaiThe, ttv.SoSachMuonToiDa 
              FROM TheThuVien ttv 
              JOIN DocGia doc ON ttv.MaDocGia = doc.MaDocGia 
              WHERE ttv.SoThe = '$the'";
    return $conn->query($sqlDG)->fetch_assoc();
}

// Hàm bổ trợ: Lấy thông tin sách
function getBookInfo($conn, $maBanSach) {
    $sqlSach = "SELECT b.MaBanSach, b.TinhTrang as TinhTrangSach, d.TenSach, tl.TenTheLoai, 
                GROUP_CONCAT(DISTINCT tg.TenTacGia SEPARATOR ', ') AS TacGia
                FROM BanSach b 
                JOIN DauSach d ON b.MaDauSach = d.MaDauSach 
                LEFT JOIN TheLoai tl ON d.MaTheLoai = tl.MaTheLoai
                LEFT JOIN TacGia_Sach tgs ON d.MaDauSach = tgs.MaDauSach
                LEFT JOIN TacGia tg ON tgs.MaTacGia = tg.MaTacGia
                WHERE b.MaBanSach = '$maBanSach' GROUP BY b.MaBanSach";
    return $conn->query($sqlSach)->fetch_assoc();
}

// 1. XỬ LÝ KHI NHẬP THẺ (BƯỚC 1 -> 2)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['btn_check_card']) || isset($_POST['btn_check_book']))) {
    $the = $_POST['the'];
    $card_data = getCardInfo($conn, $the);

    // Validate Thẻ ngay lập tức
    if (!$card_data) {
        $errors[] = "Không tìm thấy thẻ thư viện số: <b>$the</b>";
        $step = 1; // Quay lại bước 1
    } else {
        // Kiểm tra các điều kiện của thẻ
        if ($card_data['TrangThaiThe'] == 0) $errors[] = "Thẻ này <b>ĐÃ BỊ KHÓA</b>.";
        if (strtotime($card_data['NgayHetHan']) < time()) $errors[] = "Thẻ đã <b>HẾT HẠN</b>.";
        
        $sqlQuaHan = "SELECT COUNT(*) as CoSachQuaHan FROM PhieuMuon pm JOIN ChiTietMuonTra ct ON pm.MaPhieuMuon = ct.MaPhieuMuon WHERE pm.SoThe = '$the' AND ct.NgayTraThucTe IS NULL AND pm.NgayHenTra < CURDATE()";
        if ($conn->query($sqlQuaHan)->fetch_assoc()['CoSachQuaHan'] > 0) $errors[] = "Độc giả đang giữ sách <b>QUÁ HẠN</b>.";
        
        $sqlDangMuon = "SELECT COUNT(*) as DangMuon FROM PhieuMuon pm JOIN ChiTietMuonTra ct ON pm.MaPhieuMuon = ct.MaPhieuMuon WHERE pm.SoThe = '$the' AND ct.NgayTraThucTe IS NULL";
        $dangMuon = $conn->query($sqlDangMuon)->fetch_assoc()['DangMuon'];
        if ($dangMuon >= $card_data['SoSachMuonToiDa']) $errors[] = "Đạt giới hạn mượn ($dangMuon/" . $card_data['SoSachMuonToiDa'] . " cuốn).";

        // Nếu có lỗi thẻ -> Dừng ở Bước 1 nhưng hiện info để biết lỗi
        // Nếu không lỗi -> Chuyển sang Bước 2 (Nhập sách)
        if (empty($errors)) {
            $step = 2; 
        } else {
            $step = 1; // Vẫn hiện info nhưng không cho nhập sách
        }
    }
}

// 2. XỬ LÝ KHI NHẬP SÁCH (BƯỚC 2 -> 3)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_check_book'])) {
    // Đã có $card_data và $errors của thẻ ở trên rồi
    // Nếu thẻ OK thì mới check sách
    if ($step == 2) {
        $maBanSach = $_POST['masach'];
        $book_data = getBookInfo($conn, $maBanSach);

        if (!$book_data) {
            $errors[] = "Không tìm thấy sách mã: <b>$maBanSach</b>";
        } else {
            if ($book_data['TinhTrangSach'] != 1) {
                $statusText = ($book_data['TinhTrangSach'] == 0) ? "Đang được mượn" : "Hỏng/Mất";
                $errors[] = "Sách không sẵn sàng: <b>$statusText</b>.";
            }
        }

        if (empty($errors)) {
            $step = 3; // Sẵn sàng để xác nhận
        }
        // Nếu lỗi sách -> Vẫn ở step 2 (Hiện info thẻ + form sách + lỗi sách)
    }
}

// 3. XỬ LÝ KHI BẤM NÚT "XÁC NHẬN MƯỢN"
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_confirm'])) {
    $the = $_POST['the_confirm']; 
    $maBanSach = $_POST['masach_confirm']; 
    $ngaytra = $_POST['ngaytra_confirm'];
    $ghiChuMuon = $_POST['ghi_chu_confirm']; 
    $nguoiTao = isset($_SESSION['username']) ? $_SESSION['username'] : 'admin';
    
    $stmt = $conn->prepare("INSERT INTO PhieuMuon (SoThe, NgayHenTra, NguoiTao, GhiChu) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $the, $ngaytra, $nguoiTao, $ghiChuMuon);
    
    if ($stmt->execute()) {
        $idPhieu = $conn->insert_id;
        $conn->query("INSERT INTO ChiTietMuonTra (MaPhieuMuon, MaBanSach) VALUES ('$idPhieu', '$maBanSach')");
        $conn->query("UPDATE BanSach SET TinhTrang=0 WHERE MaBanSach='$maBanSach'");
        
        $msg = "<div class='alert success'><i class='fas fa-check-circle'></i> Mượn thành công! Sách <b>$maBanSach</b> cho thẻ <b>$the</b>.</div>";
        // Reset về ban đầu
        $step = 1; 
        $card_data = null; 
        $book_data = null;
    } else {
        $msg = "<div class='alert error'>Lỗi hệ thống: " . $conn->error . "</div>";
    }
}
?>