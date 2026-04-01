<?php
include_once __DIR__ . '/../actions/auth-index.php';
require_once '../config/db.php';
$conn->set_charset("utf8mb4");

$u    = $_SESSION['username'];
$role = $_SESSION['role'];
$msg  = "";

// --- XỬ LÝ CẬP NHẬT THÔNG TIN ---
if (isset($_POST['update_info'])) {
    $hoten    = $_POST['hoten'];
    $cmnd     = $_POST['cmnd'];
    $ngaysinh = $_POST['ngaysinh'];
    $diachi   = $_POST['diachi'];

    $_SESSION['ho_ten'] = $hoten;

    if ($role == 'DocGia') {
        $stmt = $conn->prepare("UPDATE DocGia SET HoTen=?, CMND_CCCD=?, NgaySinh=?, DiaChi=? WHERE TenDangNhap=?");
    } else {
        $stmt = $conn->prepare("UPDATE NhanVien SET HoTen=?, CMND_CCCD=?, NgaySinh=?, DiaChi=? WHERE TenDangNhap=?");
    }
    $stmt->bind_param("sssss", $hoten, $cmnd, $ngaysinh, $diachi, $u);

    if ($stmt->execute()) {
        $msg = "<div class='alert-msg success'><i class='fas fa-check-circle'></i> Cập nhật thông tin thành công!</div>";
    } else {
        $msg = "<div class='alert-msg error'><i class='fas fa-times-circle'></i> Lỗi cập nhật: " . $conn->error . "</div>";
    }
}

// --- LẤY THÔNG TIN HIỂN THỊ ---
if ($role == 'DocGia') {
    $info     = $conn->query("SELECT * FROM DocGia WHERE TenDangNhap='$u'")->fetch_assoc();
    $sub_text = "Mã độc giả: " . ($info['MaDocGia'] ?? 'N/A');
} else {
    $info     = $conn->query("SELECT * FROM NhanVien WHERE TenDangNhap='$u'")->fetch_assoc();
    $sub_text = "Chức vụ: " . ($role == 'QuanTriVien' ? 'Quản trị viên' : 'Thủ thư');
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Tin Cá Nhân - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style-main.css">
    <link rel="stylesheet" href="../assets/css/style-thong-tin-ca-nhan.css">
</head>
<body>

    <?php include 'Index-sidebar.php'; ?>

    <div class="main-content">
        <?php include 'Index-topbar.php'; ?>

        <div class="content-body">

            <div class="page-header">
                <h1><i class="fas fa-user-circle" style="color: var(--primary-color);"></i> Thông Tin Cá Nhân</h1>
                <p>Xem và cập nhật thông tin hồ sơ của bạn</p>
            </div>

            <?php if ($msg != "") echo $msg; ?>

            <div class="profile-wrap">

                <!-- CỘT TRÁI: AVATAR & TÓM TẮT -->
                <div class="profile-sidebar-card">
                    <div class="avatar-circle">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-name"><?php echo htmlspecialchars($info['HoTen'] ?? 'Chưa cập nhật'); ?></div>
                    <div class="user-login">@<?php echo htmlspecialchars($u); ?></div>
                    <div class="profile-badge"><?php echo $sub_text; ?></div>
                    <a href="doi-mat-khau.php" class="sidebar-link">
                        <i class="fas fa-key"></i> Đổi mật khẩu
                    </a>
                </div>

                <!-- CỘT PHẢI: FORM CẬP NHẬT -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-edit"></i> Cập Nhật Hồ Sơ
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label>Họ và Tên</label>
                                <input type="text" name="hoten" class="form-control"
                                    value="<?php echo htmlspecialchars($info['HoTen'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Số CMND / CCCD</label>
                                <input type="text" name="cmnd" class="form-control"
                                    value="<?php echo htmlspecialchars($info['CMND_CCCD'] ?? ''); ?>"
                                    placeholder="Nhập số CMND/CCCD">
                            </div>

                            <div class="form-group">
                                <label>Ngày Sinh</label>
                                <input type="date" name="ngaysinh" class="form-control"
                                    value="<?php echo $info['NgaySinh'] ?? ''; ?>">
                            </div>

                            <div class="form-group">
                                <label>Địa Chỉ Liên Hệ</label>
                                <input type="text" name="diachi" class="form-control"
                                    value="<?php echo htmlspecialchars($info['DiaChi'] ?? ''); ?>"
                                    placeholder="Số nhà, tên đường, phường/xã...">
                            </div>

                            <div class="form-group">
                                <label>Tên Đăng Nhập <span style="color:#aaa; font-weight:400;">(Không thể thay đổi)</span></label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($u); ?>" disabled>
                            </div>

                            <button type="submit" name="update_info" class="btn-submit">
                                <i class="fas fa-save"></i> LƯU THAY ĐỔI
                            </button>
                        </form>
                    </div>
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