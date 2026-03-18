<?php
session_start();
require '../config/db.php';
$conn->set_charset("utf8mb4");

// Kiểm tra quyền hạn
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'QuanTriVien' && $_SESSION['role'] != 'ThuThu')) {
    header("Location: index.php");
    exit();
}

// Lấy thông báo từ session
$u = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$ho_ten = isset($_SESSION['ho_ten']) ? $_SESSION['ho_ten'] : 'Người dùng';
$user_role = $role;
$msg = "";
if (isset($_SESSION['flash_msg'])) {
    $msg = $_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']);
}

// Lấy dữ liệu danh mục
$list_nxb = $conn->query("SELECT * FROM NhaXuatBan");
$list_tacgia = $conn->query("SELECT * FROM TacGia");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_book'])) {
    $ten = $_POST['ten'];
    $nam = $_POST['nam']; 
    $gia = $_POST['gia'];
    $theloai = $_POST['theloai'];
    $ma_tacgia = $_POST['ma_tacgia']; 
    $ma_nxb = $_POST['ma_nxb_select']; 
    
    $stmt = $conn->prepare("INSERT INTO dausach (TenSach, NamXuatBan, GiaSach, MaTheLoai, MaNXB) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdis", $ten, $nam, $gia, $theloai, $ma_nxb);

    if ($stmt->execute()) {
        $new_book_id = $conn->insert_id; 
        $stmt_tg = $conn->prepare("INSERT INTO tacgia_sach (MaDauSach, MaTacGia) VALUES (?, ?)");
        $stmt_tg->bind_param("ii", $new_book_id, $ma_tacgia);
        
        if($stmt_tg->execute()){
             redirectWithMsg("<div class='alert success'><i class='fas fa-check-circle'></i> Thêm đầu sách thành công!</div>");
        } else {
             redirectWithMsg("<div class='alert error'><i class='fas fa-exclamation-triangle'></i> Lỗi thêm tác giả: " . $conn->error . "</div>");
        }
    } else {
        redirectWithMsg("<div class='alert error'><i class='fas fa-exclamation-triangle'></i> Lỗi: " . $conn->error . "</div>");
    }
}

// --- 2. XỬ LÝ THÊM BẢN SAO ---
if (isset($_POST['add_multiple_copies'])) {
    $maDauSach = $_POST['maDauSach'];
    $soLuong = (int)$_POST['so_luong'];

    if ($soLuong > 0) {
        $countSuccess = 0;
        for ($i = 0; $i < $soLuong; $i++) {
            $maVach = "MV" . $maDauSach . "-" . rand(1000, 9999);
            while($conn->query("SELECT * FROM bansach WHERE MaBanSach='$maVach'")->num_rows > 0){
                $maVach = "MV" . $maDauSach . "-" . rand(1000, 9999);
            }
            // Mặc định tình trạng vật lý là Bình thường khi tạo mới
            if($conn->query("INSERT INTO bansach (MaBanSach, MaDauSach, TinhTrang, TinhTrangVatLy) VALUES ('$maVach', $maDauSach, 1, 'Bình thường')")){
                $countSuccess++;
            }
        }
        redirectWithMsg("<div class='alert success'><i class='fas fa-plus-circle'></i> Đã thêm <b>$countSuccess</b> bản sao mới.</div>");
    }
}

// --- 3. XỬ LÝ XÓA CÁC BẢN SAO ĐÃ CHỌN ---
if (isset($_POST['delete_selected_copies'])) {
    if (!empty($_POST['selected_copies'])) {
        $copies = $_POST['selected_copies']; 
        $countDeleted = 0;
        
        foreach ($copies as $maVach) {
            $maVach = $conn->real_escape_string($maVach);
            if($conn->query("DELETE FROM bansach WHERE MaBanSach = '$maVach'")) {
                $countDeleted++;
            }
        }
        
        if ($countDeleted > 0) {
            redirectWithMsg("<div class='alert success'><i class='fas fa-trash-alt'></i> Đã xóa <b>$countDeleted</b> bản sao đã chọn.</div>");
        } else {
            redirectWithMsg("<div class='alert error'><i class='fas fa-exclamation-triangle'></i> Không xóa được bản sao nào (có thể do lỗi DB).</div>");
        }
    } else {
        redirectWithMsg("<div class='alert error'><i class='fas fa-mouse-pointer'></i> Bạn chưa chọn bản sao nào để xóa!</div>");
    }
}

// --- 4. XỬ LÝ XÓA ĐẦU SÁCH ---
if (isset($_POST['delete_book'])) {
    $maDauSach = $_POST['maDauSachDel'];
    
    $conn->query("DELETE FROM bansach WHERE MaDauSach = $maDauSach");
    $conn->query("DELETE FROM tacgia_sach WHERE MaDauSach = $maDauSach");
    
    if ($conn->query("DELETE FROM dausach WHERE MaDauSach = $maDauSach")) {
        redirectWithMsg("<div class='alert success'><i class='fas fa-trash-alt'></i> Đã xóa đầu sách và toàn bộ bản sao liên quan.</div>");
    } else {
        redirectWithMsg("<div class='alert error'><i class='fas fa-exclamation-circle'></i> Lỗi xóa sách: " . $conn->error . "</div>");
    }
}
?>