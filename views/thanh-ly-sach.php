<?php
session_start();
require '../config/db.php'; // Đảm bảo đường dẫn này đúng với cấu trúc thư mục của bạn
$conn->set_charset("utf8mb4");

// 1. KIỂM TRA QUYỀN HẠN
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'QuanTriVien' && $_SESSION['role'] != 'ThuThu')) { 
    header("Location: index.php"); 
    exit(); 
}

// Hàm hỗ trợ chuyển hướng và thông báo
function redirectWithMsg($message) {
    $_SESSION['flash_msg'] = $message;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// 2. LẤY THÔNG BÁO TỪ SESSION
$msg = "";
if (isset($_SESSION['flash_msg'])) {
    $msg = $_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']);
}

// 3. XỬ LÝ THANH LÝ SÁCH
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_thanhly'])) {
    $maBanSach = $conn->real_escape_string($_POST['maBanSach']); 
    $lydo = $conn->real_escape_string($_POST['lydo']);
    
    // Kiểm tra tình trạng sách
    $check = $conn->query("SELECT TinhTrang FROM BanSach WHERE MaBanSach='$maBanSach'")->fetch_assoc();
    
    if ($check['TinhTrang'] == 0) {
        redirectWithMsg("<div class='alert error'><i class='fas fa-exclamation-triangle'></i> LỖI: Sách này đang có người mượn, không thể thanh lý!</div>");
    } else {
        // Ghi log vào lịch sử hệ thống
        $stmt = $conn->prepare("INSERT INTO LichSuHoatDong (NguoiThucHien, HanhDong, ChiTiet) VALUES (?, ?, ?)");
        $hanhDong = "Thanh lý sách $maBanSach";
        $u = $_SESSION['username'];
        $stmt->bind_param("sss", $u, $hanhDong, $lydo);
        $stmt->execute();
        
        // Cập nhật Tình trạng = 3 (Đã thanh lý)
        if($conn->query("UPDATE BanSach SET TinhTrang = 3 WHERE MaBanSach = '$maBanSach'")) {
            redirectWithMsg("<div class='alert success'><i class='fas fa-check-circle'></i> Đã thanh lý mã sách <b>$maBanSach</b> thành công!</div>");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Lý Sách - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;      /* Blue chính */
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --bg-color: #f8f9fc;
            --dark-blue: #2e59d9;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--bg-color); color: #333; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }

        /* Nút quay lại */
        .btn-back {
            display: inline-block;
            text-decoration: none;
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 20px;
            transition: 0.3s;
        }
        .btn-back:hover { color: var(--primary-color); }

        /* Card Style - Blue Theme */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 30px;
            border-top: 4px solid var(--primary-color);
            overflow: hidden;
        }

        .card-header {
            padding: 15px 25px;
            border-bottom: 1px solid #e3e6f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }

        .card-header h2 {
            color: var(--primary-color);
            font-size: 1.1rem;
            text-transform: uppercase;
            font-weight: 700;
            margin: 0;
        }

        /* Search Box */
        .search-box {
            padding: 20px;
            background: #fff;
            border-bottom: 1px solid #e3e6f0;
            display: flex;
            gap: 10px;
        }

        /* Table Style */
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th {
            background-color: #f8f9fc;
            color: var(--secondary-color);
            text-align: left;
            padding: 12px 15px;
            font-size: 0.75rem;
            text-transform: uppercase;
            border-bottom: 2px solid #e3e6f0;
        }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 0.9rem; vertical-align: middle; }
        tr:hover { background-color: #fcfdff; }

        /* Inputs & Buttons */
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

        .btn {
            padding: 8px 18px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary { background: var(--primary-color); color: white; }
        .btn-primary:hover { background: var(--dark-blue); }
        .btn-outline-primary { border: 1px solid var(--primary-color); color: var(--primary-color); background: none; }
        .btn-outline-primary:hover { background: var(--primary-color); color: white; }
        .btn-secondary { background: var(--secondary-color); color: white; }

        /* Badge/Tags */
        .barcode-tag {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #eaecf4;
            color: #5a5c69;
        }
        .tag-blue { background: #e0e7ff; color: #4e73df; }

        /* Alerts */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 5px solid;
            font-weight: 500;
        }
        .success { background: #e5f9f0; color: #1cc88a; border-left-color: #1cc88a; }
        .error { background: #ffe2e5; color: #e74a3b; border-left-color: #e74a3b; }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0; top: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(4px);
        }
        .modal-content {
            background: white;
            width: 90%;
            max-width: 450px;
            margin: 10% auto;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.175);
            border-top: 5px solid var(--primary-color);
            position: relative;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Quay lại trang chủ</a>

    <?php if ($msg) echo $msg; ?>

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-file-export"></i> Thanh lý sách ra khỏi kho</h2>
        </div>

        <div class="search-box">
            <input type="text" id="searchInput" class="form-control" placeholder="Tìm tên sách hoặc mã vạch cần thanh lý..." style="flex: 1;">
            <button class="btn btn-outline-primary"><i class="fas fa-search"></i> Lọc dữ liệu</button>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th width="120">Mã Vạch</th>
                        <th>Thông Tin Đầu Sách</th>
                        <th>Tình Trạng Vật Lý</th>
                        <th style="text-align: center;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody id="bookTable">
                    <?php
                    $sql = "SELECT b.*, d.TenSach FROM BanSach b JOIN DauSach d ON b.MaDauSach = d.MaDauSach WHERE b.TinhTrang != 3 ORDER BY b.MaBanSach DESC";
                    $res = $conn->query($sql);
                    if ($res->num_rows > 0) {
                        while ($row = $res->fetch_assoc()) {
                            $ten_js = addslashes($row['TenSach']);
                            echo "<tr>
                                <td><code style='color:var(--primary-color); font-weight:bold;'>{$row['MaBanSach']}</code></td>
                                <td><strong>{$row['TenSach']}</strong></td>
                                <td><span class='barcode-tag tag-blue'><i class='fas fa-info-circle'></i> {$row['TinhTrangVatLy']}</span></td>
                                <td style='text-align: center;'>
                                    <button class='btn btn-primary' onclick=\"openModal('{$row['MaBanSach']}', '$ten_js')\">
                                        <i class='fas fa-check-square'></i> Thanh lý
                                    </button>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align:center; padding:40px; color:#aaa;'>Hiện không có sách nào trong danh mục sẵn sàng thanh lý.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="lydoModal" class="modal">
    <div class="modal-content">
        <h3 style="color: var(--primary-color); margin-bottom: 15px;"><i class="fas fa-question-circle"></i> Xác nhận thanh lý</h3>
        
        <div style="background: #f8f9fc; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px dashed #d1d3e2;">
            <div style="font-size: 0.8rem; color: #858796; text-transform: uppercase;">Mã bản sách:</div>
            <div id="maSachModal" style="font-weight: bold; color: #333; margin-bottom: 8px;"></div>
            <div style="font-size: 0.8rem; color: #858796; text-transform: uppercase;">Tên sách:</div>
            <div id="tenSachModal" style="font-weight: 500; color: #333;"></div>
        </div>
        
        <form method="POST">
            <input type="hidden" name="maBanSach" id="inputMaSach">
            <label style="font-size: 0.85rem; font-weight: 600; color: #5a5c69;">Chọn lý do thanh lý:</label>
            <select name="lydo" class="form-control" style="margin-top: 8px; margin-bottom: 20px;" required>
                <option value="Sách cũ nát, rách hỏng">Sách cũ nát, rách hỏng</option>
                <option value="Lỗi nội dung / Không còn sử dụng">Lỗi nội dung / Không còn sử dụng</option>
                <option value="Mất sách (Đã làm thủ tục đền bù)">Mất sách (Đã làm thủ tục đền bù)</option>
                <option value="Chuyển mục đích sử dụng khác">Chuyển mục đích sử dụng khác</option>
            </select>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Hủy</button>
                <button type="submit" name="confirm_thanhly" class="btn btn-primary">XÁC NHẬN</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Tìm kiếm nhanh (giống trang quản lý sách)
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toUpperCase();
        let rows = document.querySelectorAll('#bookTable tr');
        rows.forEach(row => {
            let text = row.innerText.toUpperCase();
            row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
        });
    });

    function openModal(ma, ten) {
        document.getElementById('lydoModal').style.display = "block";
        document.getElementById('maSachModal').innerText = ma;
        document.getElementById('tenSachModal').innerText = ten;
        document.getElementById('inputMaSach').value = ma;
    }

    function closeModal() {
        document.getElementById('lydoModal').style.display = "none";
    }

    // Đóng khi click ngoài modal
    window.onclick = function(event) {
        if (event.target == document.getElementById('lydoModal')) closeModal();
    }

    // Chống reload form khi F5
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>

</body>
</html>