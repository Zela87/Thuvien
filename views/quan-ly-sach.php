<?php
session_start();
require_once '../config/db.php'; // Đã đổi đường dẫn cho khớp cấu trúc dự án của bạn
$conn->set_charset("utf8mb4");

// Kiểm tra quyền hạn
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'QuanTriVien' && $_SESSION['role'] != 'ThuThu')) {
    header("Location: quanlythuvien.php");
    exit();
}

// Hàm hỗ trợ chuyển hướng
function redirectWithMsg($message) {
    $_SESSION['flash_msg'] = $message;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Lấy thông báo
$u = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$ho_ten = isset($_SESSION['ho_ten']) ? $_SESSION['ho_ten'] : 'Người dùng';
$user_role = $role;

// Lấy danh sách NXB và Tác giả
$list_nxb = $conn->query("SELECT * FROM NhaXuatBan");
$list_tacgia = $conn->query("SELECT * FROM TacGia");

// --- 1. XỬ LÝ THÊM ĐẦU SÁCH MỚI ---
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

// --- 3. XỬ LÝ XÓA / THANH LÝ CÁC BẢN SAO ---
if (isset($_POST['delete_selected_copies'])) {

    if (!empty($_POST['selected_copies'])) {

        $copies = $_POST['selected_copies'];
        $countDeleted = 0;
        $countLiquidated = 0;
        $countBorrowing = 0;

        foreach ($copies as $maBanSach) {

            $maBanSach = $conn->real_escape_string($maBanSach);

            // Lấy trạng thái sách
            $check = $conn->query("SELECT TinhTrang FROM BanSach WHERE MaBanSach='$maBanSach'");
            $row = $check->fetch_assoc();

            if (!$row) continue;

            // Nếu sách đang mượn
            if ($row['TinhTrang'] == 0) {
                $countBorrowing++;
                continue;
            }

            // Kiểm tra đã từng mượn chưa
            $checkBorrow = $conn->query("
                SELECT MaBanSach 
                FROM ChiTietMuonTra 
                WHERE MaBanSach='$maBanSach'
                LIMIT 1
            ");

            if ($checkBorrow->num_rows > 0) {

                // Đã từng mượn → thanh lý
                if ($conn->query("UPDATE BanSach SET TinhTrang = 3 WHERE MaBanSach='$maBanSach'")) {

                    // ghi log giống code của bạn
                    $stmt = $conn->prepare("INSERT INTO LichSuHoatDong (NguoiThucHien, HanhDong, ChiTiet) VALUES (?, ?, ?)");
                    $hanhDong = "Thanh lý sách $maBanSach";
                    $lydo = "Thanh lý từ quản lý sách";
                    $stmt->bind_param("sss", $_SESSION['username'], $hanhDong, $lydo);
                    $stmt->execute();

                    $countLiquidated++;
                }

            } else {

                // Chưa từng mượn → xóa
                if ($conn->query("DELETE FROM BanSach WHERE MaBanSach='$maBanSach'")) {
                    $countDeleted++;
                }

            }
        }

        redirectWithMsg("
            <div class='alert success'>
            Đã xóa <b>$countDeleted</b> bản sao chưa từng mượn,
            thanh lý <b>$countLiquidated</b> bản sao đã có lịch sử mượn.
            " . ($countBorrowing > 0 ? "<br><b>$countBorrowing</b> sách đang mượn nên không thể xử lý." : "") . "
            </div>
        ");

    } else {

        redirectWithMsg("
            <div class='alert error'>
            Bạn chưa chọn bản sao nào!
            </div>
        ");
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

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sách - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style-main.css">
    <link rel="stylesheet" href="../assets/css/style-quan-ly-sach.css">
</head>
<body>

    <?php include 'Index-sidebar.php' ?>

    <div class="main-content">
        <?php include 'Index-topbar.php' ?>

        <div class="content-body">
            
            <?php if (isset($_SESSION['flash_msg'])) {
                echo $_SESSION['flash_msg'];
                unset($_SESSION['flash_msg']);
            } ?>

            <div class="manage-card">
                <h2><i class="fas fa-book-medical"></i> THÊM ĐẦU SÁCH MỚI</h2>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Tên sách</label>
                            <input type="text" name="ten" class="form-control" required placeholder="Nhập tên sách...">
                        </div>
                        <div class="form-group">
                            <label>Năm xuất bản</label>
                            <select name="nam" class="form-control" required>
                                <?php 
                                $currentYear = date("Y");
                                for ($i = $currentYear; $i >= 1900; $i--) echo "<option value='$i'>$i</option>";
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Giá sách (VNĐ)</label>
                            <input type="number" name="gia" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Thể loại</label>
                            <select name="theloai" class="form-control" required>
                                <option value="">-- Chọn thể loại --</option>
                                <?php 
                                $res_tl = $conn->query("SELECT * FROM TheLoai");
                                while ($row = $res_tl->fetch_assoc()) echo "<option value='{$row['MaTheLoai']}'>{$row['TenTheLoai']}</option>";
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tác giả chính</label>
                            <select name="ma_tacgia" class="form-control" required>
                                <option value="">-- Chọn tác giả --</option>
                                <?php 
                                $list_tacgia->data_seek(0);
                                while ($row = $list_tacgia->fetch_assoc()) echo "<option value='{$row['MaTacGia']}'>{$row['TenTacGia']}</option>";
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nhà xuất bản</label>
                            <select name="ma_nxb_select" class="form-control" required>
                                <option value="">-- Chọn NXB --</option>
                                <?php 
                                $list_nxb->data_seek(0);
                                while ($row = $list_nxb->fetch_assoc()) echo "<option value='{$row['MaNXB']}'>{$row['TenNXB']}</option>";
                                ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="add_book" class="btn-add"><i class="fas fa-plus"></i> LƯU ĐẦU SÁCH</button>
                </form>
            </div>

            <div class="manage-card">
                <h2><i class="fas fa-table"></i> DANH SÁCH ĐẦU SÁCH & BẢN SAO</h2>
                <div style="overflow-x:auto;">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Thông tin sách</th>
                                <th>Tác giả & NXB</th>
                                <th width="35%">Bản sao (Mã vạch)</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT d.MaDauSach, d.TenSach, d.NamXuatBan, d.GiaSach, n.TenNXB, 
                                    GROUP_CONCAT(t.TenTacGia SEPARATOR ', ') AS TenTacGia
                                    FROM dausach d 
                                    LEFT JOIN nhaxuatban n ON d.MaNXB = n.MaNXB
                                    LEFT JOIN tacgia_sach ts ON d.MaDauSach = ts.MaDauSach
                                    LEFT JOIN tacgia t ON ts.MaTacGia = t.MaTacGia
                                    GROUP BY d.MaDauSach
                                    ORDER BY d.MaDauSach DESC";
                            $result = $conn->query($sql);
                            if ($result && $result->num_rows > 0):
                                while($row = $result->fetch_assoc()):
                            ?>
                                <tr>
                                    <td><span class="badge" style="background:#4e73df">#<?php echo $row['MaDauSach']; ?></span></td>
                                    <td>
                                        <div style="font-weight:bold; color:#2e59d9"><?php echo $row['TenSach']; ?></div>
                                        <small class="text-muted">Năm: <?php echo $row['NamXuatBan']; ?> | Giá: <?php echo number_format($row['GiaSach']); ?>đ</small>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.85rem;"><i class="fas fa-pen-nib"></i> <?php echo $row['TenTacGia'] ?: 'N/A'; ?></div>
                                        <div style="font-size: 0.8rem; color: #6e707e;"><i class="fas fa-building"></i> <?php echo $row['TenNXB']; ?></div>
                                    </td>
                                    <td>
                                        <form method='POST' onsubmit="return confirm('Xóa các bản sao đã chọn?');">
                                            <div class="barcode-container">
                                                <?php
                                                $maDS = $row['MaDauSach'];
                                                $res_copies = $conn->query("SELECT * FROM bansach WHERE MaDauSach = $maDS");
                                                if($res_copies->num_rows > 0):
                                                    while($copy = $res_copies->fetch_assoc()):
                                                        $statusColor = ($copy['TinhTrang'] == 1) ? 'bc-green' : 'bc-red';
                                                ?>
                                                        <div class="copy-item">
                                                            <input type='checkbox' name='selected_copies[]' value='<?php echo $copy['MaBanSach']; ?>'>
                                                            <span class='barcode-tag <?php echo $statusColor; ?>' title="Tình trạng: <?php echo $copy['TinhTrangVatLy']; ?>">
                                                                <?php echo $copy['MaBanSach']; ?>
                                                            </span>
                                                        </div>
                                                <?php endwhile; ?>
                                                    <button type='submit' name='delete_selected_copies' class="btn-logout" style="font-size: 0.7rem; border:none; background:none; cursor:pointer;">
                                                        <i class='fas fa-trash'></i> Xóa bản chọn
                                                    </button>
                                                <?php else: ?>
                                                    <span style="font-size: 0.8rem; color:#ccc;">Chưa có bản sao</span>
                                                <?php endif; ?>
                                            </div>
                                        </form>
                                        
                                        <form method='POST' style="margin-top: 8px; display: flex; gap: 5px;">
                                            <input type='hidden' name='maDauSach' value='<?php echo $row['MaDauSach']; ?>'>
                                            <input type='number' name='so_luong' value='1' min="1" style='width:50px; border-radius:4px; border:1px solid #ddd;'>
                                            <button type='submit' name='add_multiple_copies' style="background:none; border:none; color:var(--success-color); cursor:pointer;">
                                                <i class='fas fa-plus-circle'></i> Thêm
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <form method='POST' onsubmit="return confirm('Xóa vĩnh viễn đầu sách và mọi bản sao?');">
                                            <input type='hidden' name='maDauSachDel' value='<?php echo $row['MaDauSach']; ?>'>
                                            <button type='submit' name='delete_book' style="background:none; border:none; color:var(--danger-color); cursor:pointer; font-size: 1.1rem;">
                                                <i class='fas fa-trash-alt'></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="5" style="text-align:center;">Không có dữ liệu sách</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>