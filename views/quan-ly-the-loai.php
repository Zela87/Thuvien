<?php
include_once __DIR__ . '/../actions/auth-index.php';
require_once '../config/db.php';
$conn->set_charset("utf8mb4");

// Kiểm tra quyền truy cập
if ($user_role != 'QuanTriVien' && $user_role != 'ThuThu') {
    header("Location: index.php");
    exit();
}

// --- XỬ LÝ LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $ten = $conn->real_escape_string($_POST['ten']);
        if ($conn->query("INSERT INTO TheLoai (TenTheLoai) VALUES ('$ten')")) {
            $_SESSION['msg'] = "<div class='alert-msg success'><i class='fas fa-check-circle'></i> Thêm thể loại mới thành công!</div>";
        } else {
            $_SESSION['msg'] = "<div class='alert-msg error'><i class='fas fa-exclamation-circle'></i> Lỗi: " . $conn->error . "</div>";
        }
    }

    if (isset($_POST['delete'])) {
        $id    = (int)$_POST['id_del'];
        $check = $conn->query("SELECT * FROM DauSach WHERE MaTheLoai='$id'");
        if ($check->num_rows > 0) {
            $_SESSION['msg'] = "<div class='alert-msg error'><i class='fas fa-exclamation-triangle'></i> Không thể xóa! Hiện đang có sách thuộc thể loại này.</div>";
        } else {
            $conn->query("DELETE FROM TheLoai WHERE MaTheLoai='$id'");
            $_SESSION['msg'] = "<div class='alert-msg info'><i class='fas fa-info-circle'></i> Đã xóa thể loại thành công.</div>";
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
    <link rel="stylesheet" href="../assets/css/style-main.css">
    <link rel="stylesheet" href="../assets/css/style-quan-ly-the-loai.css">
</head>
<body>

    <?php include 'Index-sidebar.php'; ?>

    <div class="main-content">
        <?php include 'Index-topbar.php'; ?>

        <div class="content-body">

            <div class="page-header">
                <h1><i class="fas fa-tags" style="color: var(--primary-color);"></i> Quản Lý Thể Loại</h1>
                <p>Thêm, xem và xóa các thể loại sách trong hệ thống</p>
            </div>

            <?php if ($msg) echo $msg; ?>

            <!-- FORM THÊM THỂ LOẠI -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-plus-circle"></i> Thêm Thể Loại Mới
                </div>
                <div class="card-body">
                    <form method="POST" class="form-inline">
                        <input type="text" name="ten"
                            placeholder="Nhập tên thể loại (VD: Khoa học viễn tưởng...)" required>
                        <button type="submit" name="add" class="btn-add">
                            <i class="fas fa-save"></i> Lưu Thể Loại
                        </button>
                    </form>
                </div>
            </div>

            <!-- DANH SÁCH THỂ LOẠI -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list"></i> Danh Sách Thể Loại
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th width="120">Mã Số</th>
                                <th>Tên Thể Loại</th>
                                <th width="120" style="text-align:center;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("SELECT * FROM TheLoai ORDER BY MaTheLoai DESC");
                            if ($result->num_rows > 0):
                                while ($row = $result->fetch_assoc()):
                            ?>
                                <tr>
                                    <td><span class="code-badge">#<?php echo $row['MaTheLoai']; ?></span></td>
                                    <td><strong><?php echo htmlspecialchars($row['TenTheLoai']); ?></strong></td>
                                    <td style="text-align:center;">
                                        <form method="POST" style="display:inline;"
                                            onsubmit="return confirm('Bạn có chắc chắn muốn xóa thể loại này?')">
                                            <input type="hidden" name="id_del" value="<?php echo $row['MaTheLoai']; ?>">
                                            <button type="submit" name="delete" class="btn-delete">
                                                <i class="fas fa-trash-alt"></i> Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php
                                endwhile;
                            else:
                            ?>
                                <tr class="empty-row">
                                    <td colspan="3">
                                        <i class="fas fa-inbox" style="font-size:2rem; display:block; margin-bottom:8px; color:#ddd;"></i>
                                        Chưa có dữ liệu thể loại nào.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- /.content-body -->
    </div><!-- /.main-content -->

    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>