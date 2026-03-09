<?php include_once __DIR__ . '/../actions/auth-login.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Hệ thống Thư viện</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/css-index.css">
    <link rel="stylesheet" href="../assets/css/css-Login.css">
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-book-reader"></i>
            <h2>LIB MANAGE</h2>
            <p style="color: #858796;">Đăng nhập để quản lý thư viện</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Tên đăng nhập</label>
                <input type="text" name="username" class="input-control" placeholder="Nhập tên đăng nhập..." required>
            </div>

            <div class="form-group">
                <label>Mật khẩu</label>
                <div class="pass-wrapper">
                    <input type="password" name="password" id="loginPass" class="input-control" placeholder="********" required>
                    <i class="fas fa-eye toggle-eye" onclick="togglePassword()"></i>
                </div>
            </div>

            <button type="submit" name="login" class="btn-login">Đăng nhập</button>
        </form>

        <div class="login-footer">
            <p>Chưa có tài khoản? <a href="Register.php">Đăng ký ngay</a></p>
            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eaecf4;">
            <a href="index.php" style="color: #858796;"><i class="fas fa-arrow-left"></i> Quay lại trang chủ</a>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const passInput = document.getElementById('loginPass');
        const eyeIcon = document.querySelector('.toggle-eye');
        if (passInput.type === 'password') {
            passInput.type = 'text';
            eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passInput.type = 'password';
            eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>

</body>
</html>