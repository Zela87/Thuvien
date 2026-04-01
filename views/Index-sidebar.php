<div class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-book-reader"></i> <span>LIB MANAGE</span>
        </div>

        <?php if ($user_role != 'guest'): ?>
            <?php if ($user_role == 'QuanTriVien' || $user_role == 'ThuThu'): ?>
                <div class="sidebar-section">Lưu thông</div>
                <a href="index.php" class="nav-item active"><i class="fas fa-search"></i><span>Tra Cứu Sách</span></a>
                <a href="muon-tra-sach.php" class="nav-item"><i class="fas fa-plus-circle"></i><span>Mượn Sách</span></a>
                <a href="quan-ly-phieu-muon.php" class="nav-item"><i class="fas fa-list-alt"></i><span>Phiếu Mượn</span></a>
                <a href="quan-ly-doc-gia.php" class="nav-item"><i class="fas fa-users"></i><span>Độc Giả</span></a>
                <a href="bao-cao-su-co.php" class="nav-item">
                    <i class="fas fa-headset"></i><span>Hỗ Trợ</span>
                    <?php if(isset($unread_support) && $unread_support > 0): ?><span class="badge"><?php echo $unread_support; ?></span><?php endif; ?>
                </a>

                <div class="sidebar-section">Kho sách</div>
                <a href="quan-ly-sach.php" class="nav-item"><i class="fas fa-book"></i><span>Sách</span></a>
                <a href="quan-ly-tac-gia.php" class="nav-item"><i class="fas fa-feather"></i><span>Tác Giả</span></a>
                <a href="quan-ly-nha-xuat-ban.php" class="nav-item"><i class="fas fa-building"></i><span>Nhà Xuất Bản</span></a>
                <a href="thanh-ly-sach.php" class="nav-item"><i class="fas fa-trash-alt"></i><span>Thanh Lý</span></a>

                <div class="sidebar-section">Hệ thống</div>
                <a href="lich-su-he-thong.php" class="nav-item"><i class="fas fa-history"></i><span>Lịch Sử</span></a>
                <?php if ($user_role == 'QuanTriVien'): ?>
                    <a href="quan-ly-nguoi-dung.php" class="nav-item"><i class="fas fa-user-shield"></i><span>Tài Khoản NV</span></a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($user_role == 'DocGia'): ?>
                <div class="sidebar-section">Người dùng</div>
                <a href="index.php" class="nav-item active"><i class="fas fa-search"></i><span>Tra Cứu Sách</span></a>
                <a href="dang-ky-muon.php" class="nav-item"><i class="fas fa-shopping-cart"></i><span>Đăng Ký Mượn</span></a>
                <a href="thong-tin-ca-nhan.php" class="nav-item"><i class="fas fa-user"></i><span>Hồ Sơ Của Tôi</span></a>
            <?php endif; ?>

            <div class="sidebar-section">Cá nhân</div>
            <a href="doi-mat-khau.php" class="nav-item"><i class="fas fa-key"></i><span>Đổi Mật Khẩu</span></a>
            <a href="../actions/auth-index.php?logout=true" class="nav-item" style="color: #ff8080;"><i class="fas fa-sign-out-alt"></i><span>Đăng Xuất</span></a>
        
        <?php else: ?>
            <div class="sidebar-section">Khách</div>
            <a href="Login.php" class="nav-item"><i class="fas fa-sign-in-alt"></i><span>Đăng Nhập</span></a>
            <!-- <a href="index.php" class="nav-item active"><i class="fas fa-search"></i><span>Tra Cứu Sách</span></a> -->
        <?php endif; ?>
    </div>