<?php
require '../config/db.php';
session_start();
$conn->set_charset("utf8mb4");

// Kiểm tra quyền truy cập (Admin và Thủ thư)
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'QuanTriVien' && $_SESSION['role'] != 'ThuThu')) { 
    header("Location: index.php"); 
    exit(); 
}

// Xử lý Logic PHP
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $ten = $conn->real_escape_string($_POST['ten']); 
        $chucdanh = $conn->real_escape_string($_POST['chucdanh']); 
        $noilamviec = $conn->real_escape_string($_POST['noilamviec']);
        
        $stmt = $conn->prepare("INSERT INTO TacGia (TenTacGia, ChucDanh, NoiLamViec) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $ten, $chucdanh, $noilamviec);
        $stmt->execute();
        $_SESSION['msg'] = "<div class='alert alert-success'><i class='fas fa-check-circle'></i> Thêm tác giả thành công!</div>";
    }
    
    if (isset($_POST['delete'])) {
        $id = (int)$_POST['id_del'];
        // Kiểm tra xem tác giả có đang liên kết với sách nào không
        $check = $conn->query("SELECT COUNT(*) as count FROM tacgia_sach WHERE MaTacGia=$id");
        $has_books = $check->fetch_assoc()['count'];
        
        if($has_books > 0) {
            $_SESSION['msg'] = "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle'></i> Không thể xóa tác giả này vì đang có sách liên kết!</div>";
        } else {
            $conn->query("DELETE FROM TacGia WHERE MaTacGia=$id");
            $_SESSION['msg'] = "<div class='alert alert-info'><i class='fas fa-info-circle'></i> Đã xóa thông tin tác giả.</div>";
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']); 
    exit();
}

$msg = isset($_SESSION['msg']) ? $_SESSION['msg'] : "";
unset($_SESSION['msg']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tác Giả - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --bg-color: #f8f9fc;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background-color: var(--bg-color); color: #333; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }

        .btn-back {
            display: inline-block;
            text-decoration: none;
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 20px;
            transition: 0.3s;
        }
        .btn-back:hover { color: var(--primary-color); }

        /* Card Style */
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
        }

        .card-header h2 {
            color: var(--primary-color);
            font-size: 1.1rem;
            text-transform: uppercase;
            font-weight: 700;
            margin: 0;
        }

        /* Form Layout */
        .form-body { padding: 25px; }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--secondary-color); font-size: 0.85rem; }
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #d1d3e2;
            border-radius: 8px;
            outline: none;
        }
        .form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25); }

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
        td { padding: 12px 15px; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        tr:hover { background-color: #fcfdff; }

        /* Buttons */
        .btn {
            padding: 10px 20px;
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
        .btn-danger-link { background: none; color: var(--danger-color); padding: 0; font-weight: 600; }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }

        /* Alert */
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid; }
        .alert-success { background: #f6ffed; color: var(--success-color); border-left-color: var(--success-color); }
        .alert-danger { background: #fff1f0; color: var(--danger-color); border-left-color: var(--danger-color); }
        .alert-info { background: #e6f7ff; color: #1890ff; border-left-color: #1890ff; }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Quay lại trang chủ</a>

    <?php echo $msg; ?>

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-user-plus"></i> Thêm tác giả mới</h2>
        </div>
        <div class="form-body">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Họ tên tác giả</label>
                        <input type="text" name="ten" class="form-control" placeholder="Ví dụ: Ngô Tất Tố" required>
                    </div>
                    <div class="form-group">
                        <label>Chức danh / Học vị</label>
                        <input type="text" name="chucdanh" class="form-control" placeholder="Ví dụ: Nhà văn, Tiến sĩ...">
                    </div>
                    <div class="form-group">
                        <label>Nơi làm việc / Quê quán</label>
                        <input type="text" name="noilamviec" class="form-control" placeholder="Địa chỉ hoặc cơ quan...">
                    </div>
                </div>
                <div style="text-align: right; margin-top: 10px;">
                    <button type="submit" name="add" class="btn btn-primary">
                        <i class="fas fa-save"></i> LƯU THÔNG TIN
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-users"></i> Danh sách tác giả hệ thống</h2>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th width="80">Mã TG</th>
                        <th>Họ Tên Tác Giả</th>
                        <th>Chức Danh</th>
                        <th>Nơi Làm Việc</th>
                        <th style="text-align: center;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM TacGia ORDER BY MaTacGia DESC";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td><code>#{$row['MaTacGia']}</code></td>
                                <td><strong>{$row['TenTacGia']}</strong></td>
                                <td><span class='text-muted'>{$row['ChucDanh']}</span></td>
                                <td>{$row['NoiLamViec']}</td>
                                <td style='text-align: center;'>
                                    <form method='POST' onsubmit=\"return confirm('Xóa tác giả này?')\" style='display:inline;'>
                                        <input type='hidden' name='id_del' value='{$row['MaTacGia']}'>
                                        <button type='submit' name='delete' class='btn btn-danger-link'>
                                            <i class='fas fa-trash-alt'></i> Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center; padding:30px; color:#aaa;'>Chưa có dữ liệu tác giả.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Ngăn chặn submit lại khi refresh trang
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>

</body>
</html>