<?php
include_once '../actions/auth-register.php';

$msg = $msg ?? '';

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/css-index.css">
    <link rel="stylesheet" href="../assets/css/css-Register.css">
</head>

<body>

    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <i class="fas fa-user-plus"></i>
                <h2>Đăng Ký Thành Viên</h2>
            </div>

            <?php echo $msg; ?>

            <form method="POST">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label><i class="fas fa-id-card"></i> Họ và tên</label>
                        <input type="text" name="hoten" class="input-control" placeholder="Nguyễn Văn A" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Tên đăng nhập</label>
                        <input type="text" name="username" class="input-control" placeholder="user123" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-fingerprint"></i> CCCD/CMND</label>
                        <input type="text" name="cccd" class="input-control" placeholder="Số căn cước" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-key"></i> Mật khẩu</label>
                        <div class="pass-wrapper">
                            <input type="password" name="password" id="p1" class="input-control" placeholder="******" required>
                            <i class="fas fa-eye toggle-eye" onclick="togglePass('p1', this)"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-redo"></i> Nhập lại mật khẩu</label>
                        <div class="pass-wrapper">
                            <input type="password" name="repassword" id="p2" class="input-control" placeholder="******" required>
                            <i class="fas fa-eye toggle-eye" onclick="togglePass('p2', this)"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> Ngày sinh</label>
                        <input type="date" name="ngaysinh" class="input-control" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Địa chỉ</label>
                        <input type="text" name="diachi" class="input-control" placeholder="Tỉnh/Thành phố" required>
                    </div>
                </div>

                <button type="submit" name="register" class="btn-register">TẠO TÀI KHOẢN NGAY</button>
            </form>

            <div class="register-footer">
                <p>Đã có tài khoản? <a href="Login.php">Đăng nhập</a></p>
                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eaecf4;">
                <a href="Index.php" style="color: #858796;"><i class="fas fa-arrow-left"></i> Quay lại trang chủ</a>
            </div>
        </div>
    </div>

    <script>
        function togglePass(id, icon) {
            const input = document.getElementById(id);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>

</body>

</html>