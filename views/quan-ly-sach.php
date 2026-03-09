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

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Kho Sách - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --bg-color: #f8f9fc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 1300px;
            margin: 0 auto;
        }

        .btn-back {
            display: inline-block;
            text-decoration: none;
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 20px;
            transition: 0.3s;
        }

        .btn-back:hover {
            color: var(--primary-color);
        }

        /* Card Style */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 30px;
            border-top: 4px solid var(--primary-color);
        }

        .card-header {
            padding: 15px 25px;
            border-bottom: 1px solid #e3e6f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            color: var(--primary-color);
            font-size: 1.1rem;
            text-transform: uppercase;
            font-weight: 700;
            margin: 0;
        }

        /* Form Layout */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            padding: 20px;
        }

        .form-group {
            margin-bottom: 10px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--secondary-color);
            font-size: 0.85rem;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d3e2;
            border-radius: 8px;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        /* Table Style */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #f8f9fc;
            color: var(--secondary-color);
            text-align: left;
            padding: 12px 15px;
            font-size: 0.75rem;
            text-transform: uppercase;
            border-bottom: 2px solid #e3e6f0;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
            vertical-align: top;
        }

        tr:hover {
            background-color: #fcfdff;
        }

        /* Barcode & Tags */
        .barcode-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .barcode-tag {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: 1px solid transparent;
        }

        .bc-available {
            background: #e5f9f0;
            color: #1cc88a;
            border-color: #b7eb8f;
        }

        .bc-borrowed {
            background: #ffe2e5;
            color: #e74a3b;
            border-color: #ffa39e;
        }

        /* Action Buttons */
        .btn {
            padding: 8px 15px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            font-size: 0.85rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-outline-primary {
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            background: none;
        }

        /* Search Box */
        .search-box {
            padding: 20px;
            background: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            display: flex;
            gap: 10px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 5px solid;
        }

        .alert-info {
            background: #e6f7ff;
            color: #1890ff;
            border-left-color: #1890ff;
        }
    </style>
</head>

<body>

    <div class="container">
        <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Quay lại trang chủ</a>

        <?php if ($msg) echo "<div class='alert alert-info'>$msg</div>"; ?>

        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-book-medical"></i> Nhập đầu sách mới</h2>
            </div>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Tên đầu sách</label>
                        <input type="text" name="ten" class="form-control" placeholder="Ví dụ: Lập trình PHP" required>
                    </div>
                    <div class="form-group">
                        <label>Tác giả chính</label>
                        <select name="ma_tacgia" class="form-control">
                            <?php while ($tg = $list_tacgia->fetch_assoc()) echo "<option value='{$tg['MaTacGia']}'>{$tg['TenTacGia']}</option>"; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nhà xuất bản</label>
                        <select name="ma_nxb" class="form-control">
                            <?php while ($nxb = $list_nxb->fetch_assoc()) echo "<option value='{$nxb['MaNXB']}'>{$nxb['TenNXB']}</option>"; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Thể loại</label>
                        <input type="text" name="theloai" class="form-control" placeholder="Giáo trình, Tiểu thuyết...">
                    </div>
                    <div class="form-group">
                        <label>Giá bìa (VNĐ)</label>
                        <input type="number" name="gia" class="form-control" value="0">
                    </div>
                    <div class="form-group" style="display: flex; align-items: flex-end;">
                        <button type="submit" name="add_book" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-save"></i> LƯU ĐẦU SÁCH
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-layer-group"></i> Quản lý kho sách & bản sao</h2>
            </div>

            <div class="search-box">
                <input type="text" id="searchInput" class="form-control" placeholder="Tìm kiếm theo tên sách hoặc mã vạch..." style="flex: 1;">
                <button class="btn btn-outline-primary"><i class="fas fa-search"></i> Tìm</button>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th>Thông Tin Đầu Sách</th>
                            <th>Thể Loại / NXB</th>
                            <th>Các Bản Sao (Mã Vạch)</th>
                            <th style="text-align: center;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT d.*, n.TenNXB FROM dausach d LEFT JOIN nhaxuatban n ON d.MaNXB = n.MaNXB ORDER BY d.MaDauSach DESC";
                        $res = $conn->query($sql);
                        while ($row = $res->fetch_assoc()) {
                            $maDau = $row['MaDauSach'];
                        ?>
                            <tr>
                                <td><code>#<?php echo $maDau; ?></code></td>
                                <td>
                                    <strong><?php echo $row['TenSach']; ?></strong><br>
                                    <small class="text-muted"><i class="fas fa-calendar-alt"></i> Năm XB: <?php echo $row['NamXuatBan']; ?></small>
                                </td>
                                <td>
                                    <span class="badge" style="background: #eaecf4; padding: 3px 8px; border-radius: 4px;"><?php echo $row['TheLoai']; ?></span><br>
                                    <small><?php echo $row['TenNXB']; ?></small>
                                </td>
                                <td>
                                    <div class="barcode-container">
                                        <?php
                                        $copies = $conn->query("SELECT * FROM bansach WHERE MaDauSach = $maDau");
                                        if ($copies->num_rows > 0) {
                                            while ($c = $copies->fetch_assoc()) {
                                                $statusClass = $c['TinhTrang'] == 1 ? 'bc-available' : 'bc-borrowed';
                                                $icon = $c['TinhTrang'] == 1 ? 'fa-check-circle' : 'fa-clock';
                                                echo "<span class='barcode-tag $statusClass' title='{$c['TinhTrangVatLy']}'>
                                                    <i class='fas $icon'></i> {$c['MaBanSach']}
                                                  </span>";
                                            }
                                        } else echo "<em class='text-muted'>Chưa có bản sao nào</em>";
                                        ?>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <form method="POST" style="display: flex; gap: 5px; justify-content: center;">
                                        <input type="hidden" name="ma_dausach" value="<?php echo $maDau; ?>">
                                        <button type="submit" name="add_copy" class="btn btn-success" title="Thêm 1 bản sao">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" title="Sửa thông tin">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Chức năng tìm kiếm nhanh
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toUpperCase();
            let rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                let text = row.innerText.toUpperCase();
                row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
            });
        });

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>

</body>

</html>