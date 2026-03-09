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
        if($conn->query("INSERT INTO TheLoai (TenTheLoai) VALUES ('$ten')")) {
            $_SESSION['msg'] = "<div class='alert alert-success'><i class='fas fa-check-circle'></i> Thêm thể loại mới thành công!</div>";
        } else {
            $_SESSION['msg'] = "<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> Lỗi: " . $conn->error . "</div>";
        }
    }
    
    if (isset($_POST['delete'])) {
        $id = (int)$_POST['id_del'];
        // Kiểm tra ràng buộc dữ liệu: Thể loại có đang được dùng cho sách nào không
        $check = $conn->query("SELECT * FROM DauSach WHERE MaTheLoai='$id'");
        if ($check->num_rows > 0) {
            $_SESSION['msg'] = "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle'></i> Không thể xóa! Hiện đang có sách thuộc thể loại này.</div>";
        } else {
            $conn->query("DELETE FROM TheLoai WHERE MaTheLoai='$id'");
            $_SESSION['msg'] = "<div class='alert alert-info'><i class='fas fa-info-circle'></i> Đã xóa thể loại thành công.</div>";
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
    <title>Quản Lý Thể Loại - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --bg-color: #f8f9fc;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--bg-color); color: #333; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }

        .btn-back {
            display: inline-block;
            text-decoration: none;
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 20px;
            transition: 0.3s;
        }
        .btn-back:hover { color: var(--primary-color); }

        /* Card Style đồng bộ hệ thống */
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
            background: #fff;
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
        .form-inline {
            display: flex;
            gap: 10px;
        }

        .form-control {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #d1d3e2;
            border-radius: 8px;
            outline: none;
            transition: 0.3s;
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
        td { padding: 12px 15px; border-bottom: 1px solid #eee; font-size: 0.95rem; }
        tr:hover { background-color: #fcfdff; }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: 0.3s;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary { background: var(--primary-color); color: white; }
        .btn-danger-link { background: none; color: var(--danger-color); font-weight: 600; padding: 0; }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }

        /* Alert */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border-left: 5px solid;
        }
        .alert-success { background: #f6ffed; color: var(--success-color); border-left-color: var(--success-color); }
        .alert-danger { background: #fff1f0; color: var(--danger-color); border-left-color: var(--danger-color); }
        .alert-info { background: #e6f7ff; color: #1890ff; border-left-color: #1890ff; }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Quay lại trang chủ</a>

    <?php if ($msg) echo $msg; ?>

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-plus-circle"></i> Thêm thể loại sách mới</h2>
        </div>
        <div class="form-body">
            <form method="POST" class="form-inline">
                <input type="text" name="ten" class="form-control" placeholder="Nhập tên thể loại (VD: Khoa học viễn tưởng...)" required>
                <button type="submit" name="add" class="btn btn-primary">
                    <i class="fas fa-save"></i> LƯU THỂ LOẠI
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-tags"></i> Danh sách các thể loại</h2>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th width="100">Mã Số</th>
                        <th>Tên Thể Loại</th>
                        <th style="text-align: center;" width="120">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM TheLoai ORDER BY MaTheLoai DESC";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td><code>#{$row['MaTheLoai']}</code></td>
                                <td><strong>{$row['TenTheLoai']}</strong></td>
                                <td style='text-align: center;'>
                                    <form method='POST' onsubmit=\"return confirm('Bạn có chắc chắn muốn xóa thể loại này?')\" style='display:inline;'>
                                        <input type='hidden' name='id_del' value='{$row['MaTheLoai']}'>
                                        <button type='submit' name='delete' class='btn btn-danger-link'>
                                            <i class='fas fa-trash-alt'></i> Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' style='text-align:center; padding:30px; color:#aaa;'>Chưa có dữ liệu thể loại.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Chống submit lại khi tải lại trang (F5)
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>

</body>
</html>