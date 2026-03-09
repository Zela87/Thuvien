<?php
include_once __DIR__ . '/../actions/auth-quan-ly-nguoi-dung.php';
require_once '../config/db.php';

$msg_session = $_SESSION['msg'] ?? "";
unset($_SESSION['msg']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Người Dùng - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style-main.css">
    <link rel="stylesheet" href="../assets/css/style-quan-ly-nguoi-dung.css">
</head>
<body>

    <?php include 'Index-sidebar.php' ?>

    <div class="main-content">
        <?php include 'Index-topbar.php' ?>

        <div class="content-body" style="padding: 20px;">
            <?php if($msg_session): 
                $m = explode('|', $msg_session); ?>
                <div style="padding:15px; border-radius:5px; margin-bottom:20px; background:<?php echo $m[0]=='success'?'#e5f9f0':'#fdf2f2'; ?>; color:<?php echo $m[0]=='success'?'#1cc88a':'#e74a3b'; ?>;">
                    <i class="fas fa-info-circle"></i> <?php echo $m[1]; ?>
                </div>
            <?php endif; ?>

            <div class="user-card">
                <h3 style="color: #4e73df; margin-bottom: 20px;"><i class="fas fa-user-plus"></i> THÊM TÀI KHOẢN MỚI</h3>
                <form method="POST" class="user-form">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <input type="text" name="user" class="form-control" placeholder="Tên đăng nhập" required>
                        <input type="password" name="pass" class="form-control" placeholder="Mật khẩu" required>
                        <input type="text" name="name" class="form-control" placeholder="Họ và tên" required>
                        <select name="role" class="form-control" required>
                            <option value="DocGia">Độc Giả</option>
                            <option value="ThuThu">Thủ Thư</option>
                            <option value="QuanTriVien">Quản Trị Viên</option>
                        </select>
                        <button type="submit" name="create_user" class="btn-save" style="margin-top:0">TẠO TÀI KHOẢN</button>
                    </div>
                </form>

                <h3 style="color: #4e73df; margin-bottom: 15px;"><i class="fas fa-users-cog"></i> DANH SÁCH TÀI KHOẢN</h3>
                <table class="table-users">
                    <thead>
                        <tr>
                            <th>Tên đăng nhập</th>
                            <th>Họ tên / Vai trò</th>
                            <th>Nhóm quyền</th>
                            <th>Trạng thái</th>
                            <th style="text-align:center;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT tk.*, dg.HoTen FROM TaiKhoan tk 
                                LEFT JOIN DocGia dg ON tk.TenDangNhap = dg.TenDangNhap 
                                ORDER BY tk.MaNhom ASC";
                        $res = $conn->query($sql);
                        while($row = $res->fetch_assoc()):
                            $roleClass = $row['MaNhom'] == 'QuanTriVien' ? 'role-admin' : ($row['MaNhom'] == 'ThuThu' ? 'role-staff' : 'role-user');
                        ?>
                        <tr>
                            <td><b><?php echo $row['TenDangNhap']; ?></b></td>
                            <td>
                                <?php echo $row['HoTen'] ?? '<i class="text-muted">Hệ thống</i>'; ?>
                            </td>
                            <td><span class="role-badge <?php echo $roleClass; ?>"><?php echo $row['MaNhom']; ?></span></td>
                            <td>
                                <span class="status-indicator <?php echo $row['TrangThai']==1 ? 'status-online' : 'status-offline'; ?>"></span>
                                <?php echo $row['TrangThai']==1 ? 'Đang hoạt động' : 'Đang khóa'; ?>
                            </td>
                            <td style="text-align:center;">
                                <?php if($row['TenDangNhap'] != $_SESSION['username']): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="target_user" value="<?php echo $row['TenDangNhap']; ?>">
                                    <input type="hidden" name="current_status" value="<?php echo $row['TrangThai']; ?>">
                                    <button type="submit" name="toggle_status" class="btn-mini <?php echo $row['TrangThai']==1 ? 'btn-lock' : 'btn-unlock'; ?>" style="border:none; padding:5px 10px; border-radius:4px; color:white; cursor:pointer;">
                                        <?php echo $row['TrangThai']==1 ? '<i class="fas fa-user-slash"></i> Khóa' : '<i class="fas fa-user-check"></i> Mở'; ?>
                                    </button>
                                </form>
                                <?php else: ?>
                                    <small class="text-muted">Đang đăng nhập</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>