<?php
include_once __DIR__ . '/../actions/auth-doi-mat-khau.php';
require_once '../config/db.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi Mật Khẩu - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style-main.css">
    <link rel="stylesheet" href="../assets/css/style-doi-mat-khau.css">
</head>
<body>

    <?php include 'Index-sidebar.php' ?>

    <div class="main-content">
        <?php include 'Index-topbar.php' ?>

        <div class="content-body">
            <div class="auth-card">
                <h3><i class="fas fa-key"></i> ĐỔI MẬT KHẨU</h3>
                
                <?php echo $msg; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Mật khẩu hiện tại</label>
                        <input type="password" name="old_pass" class="form-control" placeholder="Nhập mật khẩu cũ" required>
                    </div>

                    <div class="form-group">
                        <label>Mật khẩu mới</label>
                        <input type="password" name="new_pass" class="form-control" placeholder="Tối thiểu 6 ký tự" required>
                    </div>

                    <div class="form-group">
                        <label>Xác nhận mật khẩu mới</label>
                        <input type="password" name="confirm_pass" class="form-control" placeholder="Nhập lại mật khẩu mới" required>
                    </div>

                    <button type="submit" name="change_pass" class="btn-save">
                        <i class="fas fa-save"></i> LƯU THAY ĐỔI
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>