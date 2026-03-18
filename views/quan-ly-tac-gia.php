<?php
require_once '../config/db.php'; // Đảm bảo đường dẫn tới file kết nối DB của bạn
session_start();

// Giả định biến $user_role và $ho_ten được lấy từ session sau khi login
$user_role = $_SESSION['role'] ?? 'guest';
$ho_ten = $_SESSION['ho_ten'] ?? 'Nhân viên';

// --- GIỮ NGUYÊN LOGIC XỬ LÝ POST CỦA BẠN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $ten = $_POST['ten']; 
        $chucdanh = $_POST['chucdanh']; 
        $noilamviec = $_POST['noilamviec'];
        $stmt = $conn->prepare("INSERT INTO TacGia (TenTacGia, ChucDanh, NoiLamViec) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $ten, $chucdanh, $noilamviec);
        $stmt->execute();
        $_SESSION['msg'] = "Thêm tác giả thành công!";
    }
    if (isset($_POST['delete'])) {
        $id = $_POST['id_del'];
        if($conn->query("DELETE FROM TacGia WHERE MaTacGia=$id")) {
            $_SESSION['msg'] = "Đã xóa tác giả!";
        } else {
            $_SESSION['msg'] = "Không thể xóa tác giả này (đang có sách liên kết)!";
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tác Giả - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style-main.css">
    <link rel="stylesheet" href="../assets/css/style-quan-ly-tac-gia.css">
</head>
<body>

    <?php include 'Index-sidebar.php' ?>

    <div class="main-content">
        <?php include 'Index-topbar.php' ?>

        <div class="content-body">
            <?php if (isset($_SESSION['msg'])): ?>
                <script>alert("<?php echo $_SESSION['msg']; ?>");</script>
                <?php unset($_SESSION['msg']); ?>
            <?php endif; ?>

            <div class="manage-card">
                <h3><i class="fas fa-user-plus"></i> Thêm Tác Giả Mới</h3>
                <form method="post" class="form-inline">
                    <div class="form-group">
                        <label>Họ và tên</label>
                        <input type="text" name="ten" class="form-control" placeholder="Nhập tên tác giả..." required>
                    </div>
                    <div class="form-group">
                        <label>Chức danh</label>
                        <input type="text" name="chucdanh" class="form-control" placeholder="Ví dụ: PGS. TS">
                    </div>
                    <div class="form-group">
                        <label>Nơi làm việc</label>
                        <input type="text" name="noilamviec" class="form-control" placeholder="Ví dụ: Đại học Bách Khoa">
                    </div>
                    <button type="submit" name="add" class="btn-add">
                        <i class="fas fa-plus"></i> THÊM
                    </button>
                </form>
            </div>

            <div class="manage-card">
                <h3><i class="fas fa-list-ul"></i> Danh Sách Tác Giả</h3>
                <div style="overflow-x: auto;">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th width="80px">Mã số</th>
                                <th>Họ và Tên tác giả</th>
                                <th>Chức danh</th>
                                <th>Nơi làm việc</th>
                                <th width="100px">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res = $conn->query("SELECT * FROM TacGia ORDER BY MaTacGia DESC");
                            if ($res && $res->num_rows > 0):
                                while ($row = $res->fetch_assoc()):
                            ?>
                                <tr>
                                    <td><span style="font-weight: 700; color: #858796;">#<?php echo $row['MaTacGia']; ?></span></td>
                                    <td><b style="color: #4e73df;"><?php echo htmlspecialchars($row['TenTacGia']); ?></b></td>
                                    <td><?php echo htmlspecialchars($row['ChucDanh'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['NoiLamViec'] ?: '-'); ?></td>
                                    <td style="text-align: center;">
                                        <form method='post' onsubmit="return confirm('Bạn có chắc chắn muốn xóa tác giả này?');">
                                            <input type='hidden' name='id_del' value='<?php echo $row['MaTacGia']; ?>'>
                                            <button type='submit' name='delete' class="btn-delete" title="Xóa tác giả">
                                                <i class='fas fa-trash-alt'></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php 
                                endwhile; 
                            else:
                            ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 30px; color: #ccc;">
                                        Chưa có dữ liệu tác giả trong hệ thống.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>