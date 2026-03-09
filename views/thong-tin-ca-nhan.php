<?php
session_start();
require '../config/db.php';
$conn->set_charset("utf8mb4");

// Chặn truy cập nếu chưa đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$u = $_SESSION['username'];
$role = $_SESSION['role']; 
$msg = "";

// --- XỬ LÝ CẬP NHẬT THÔNG TIN ---
if (isset($_POST['update_info'])) {
    $hoten = $_POST['hoten'];
    $cmnd = $_POST['cmnd'];
    $ngaysinh = $_POST['ngaysinh'];
    $diachi = $_POST['diachi'];
    
    $_SESSION['ho_ten'] = $hoten; 

    if ($role == 'DocGia') {
        $stmt = $conn->prepare("UPDATE DocGia SET HoTen=?, CMND_CCCD=?, NgaySinh=?, DiaChi=? WHERE TenDangNhap=?");
        $stmt->bind_param("sssss", $hoten, $cmnd, $ngaysinh, $diachi, $u);
    } else {
        $stmt = $conn->prepare("UPDATE NhanVien SET HoTen=?, CMND_CCCD=?, NgaySinh=?, DiaChi=? WHERE TenDangNhap=?");
        $stmt->bind_param("sssss", $hoten, $cmnd, $ngaysinh, $diachi, $u);
    }
    
    if ($stmt->execute()) {
        $msg = "<div class='alert success'><i class='fas fa-check-circle'></i> Cập nhật thông tin thành công!</div>";
    } else {
        $msg = "<div class='alert error'><i class='fas fa-times-circle'></i> Lỗi cập nhật: " . $conn->error . "</div>";
    }
}

// --- LẤY THÔNG TIN HIỂN THỊ ---
if ($role == 'DocGia') {
    $info = $conn->query("SELECT * FROM DocGia WHERE TenDangNhap='$u'")->fetch_assoc();
    $sub_text = "Mã độc giả: " . ($info['MaDocGia'] ?? 'N/A');
} else {
    $info = $conn->query("SELECT * FROM NhanVien WHERE TenDangNhap='$u'")->fetch_assoc();
    $sub_text = "Chức vụ: " . ($role == 'QuanTriVien' ? 'Quản trị viên' : 'Thủ thư');
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #2563eb;
            --dark-blue: #1e40af;
            --light-blue: #eff6ff;
            --border-color: #e2e8f0;
            --text-main: #1e293b;
            --text-sub: #64748b;
            --bg-body: #f1f5f9;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', 'Segoe UI', sans-serif; }
        body { background-color: var(--bg-body); color: var(--text-main); padding: 40px 20px; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }

        /* Nút quay lại */
        .btn-back {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            color: var(--text-sub);
            font-weight: 500;
            margin-bottom: 20px;
            transition: 0.2s;
            gap: 8px;
        }
        .btn-back:hover { color: var(--primary-blue); }

        /* Profile Card */
        .profile-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
            display: flex;
            overflow: hidden;
        }

        /* Sidebar màu xanh */
        .profile-sidebar {
            background: var(--primary-blue);
            color: white;
            padding: 40px 30px;
            width: 35%;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .avatar-circle {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin-bottom: 20px;
            border: 4px solid rgba(255, 255, 255, 0.3);
        }

        .profile-sidebar h2 { font-size: 1.25rem; margin-bottom: 5px; }
        .profile-sidebar p { font-size: 0.85rem; opacity: 0.8; }

        /* Form Area */
        .profile-main { padding: 40px; width: 65%; background: white; }
        .profile-main h3 { 
            color: var(--primary-blue); 
            margin-bottom: 25px; 
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid var(--light-blue);
            padding-bottom: 10px;
        }

        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-sub);
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            outline: none;
            font-size: 0.95rem;
            transition: 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-control:disabled { background: #f8fafc; cursor: not-allowed; color: #94a3b8; }

        .btn-submit {
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-submit:hover { background: var(--dark-blue); transform: translateY(-1px); }

        /* Alert */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        @media (max-width: 768px) {
            .profile-card { flex-direction: column; }
            .profile-sidebar, .profile-main { width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn-back"><i class="fas fa-chevron-left"></i> Quay lại trang chủ</a>

    <?php echo $msg; ?>

    <div class="profile-card">
        <div class="profile-sidebar">
            <div class="avatar-circle">
                <i class="fas fa-user-shield"></i>
            </div>
            <h2><?php echo htmlspecialchars($info['HoTen'] ?? 'Chưa cập nhật'); ?></h2>
            <p>@<?php echo $u; ?></p>
            <div style="margin-top: 20px; padding: 10px 20px; background: rgba(255,255,255,0.1); border-radius: 30px; font-size: 0.8rem;">
                <?php echo $sub_text; ?>
            </div>
        </div>

        <div class="profile-main">
            <h3>Cập nhật hồ sơ</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Họ và tên</label>
                    <input type="text" name="hoten" class="form-control" value="<?php echo htmlspecialchars($info['HoTen'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Số CMND / CCCD</label>
                    <input type="text" name="cmnd" class="form-control" value="<?php echo htmlspecialchars($info['CMND_CCCD'] ?? ''); ?>" placeholder="Nhập số CMND/CCCD">
                </div>

                <div class="form-group">
                    <label>Ngày sinh</label>
                    <input type="date" name="ngaysinh" class="form-control" value="<?php echo $info['NgaySinh'] ?? ''; ?>">
                </div>

                <div class="form-group">
                    <label>Địa chỉ liên hệ</label>
                    <input type="text" name="diachi" class="form-control" value="<?php echo htmlspecialchars($info['DiaChi'] ?? ''); ?>" placeholder="Số nhà, tên đường, phường/xã...">
                </div>

                <div class="form-group">
                    <label>Tên đăng nhập (Không thể thay đổi)</label>
                    <input type="text" class="form-control" value="<?php echo $u; ?>" disabled>
                </div>

                <button type="submit" name="update_info" class="btn-submit">
                    <i class="fas fa-save"></i> LƯU THAY ĐỔI
                </button>
            </form>
            
            <p style="margin-top: 25px; text-align: center; font-size: 0.85rem;">
                <a href="doi-mat-khau.php" style="color: var(--primary-blue); text-decoration: none; font-weight: 600;">
                    <i class="fas fa-key"></i> Bạn muốn đổi mật khẩu?
                </a>
            </p>
        </div>
    </div>
</div>

<script>
    // Chống reload form khi F5
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>

</body>
</html>