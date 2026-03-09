<?php
include_once __DIR__ . '/../actions/auth-nxb.php';
require_once '../config/db.php';

$msg_session = $_SESSION['msg'] ?? "";
unset($_SESSION['msg']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Nhà Xuất Bản - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style-main.css">
    <link rel="stylesheet" href="../assets/css/style-nxb.css">
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

            <div class="nxb-card">
                <div class="nxb-form-container">
                    <h4 id="form-title" style="color: #4e73df; margin-bottom: 15px;"><i class="fas fa-plus-circle"></i> THÊM NHÀ XUẤT BẢN</h4>
                    <form method="POST">
                        <input type="hidden" name="ma_nxb" id="ma_nxb">
                        <div style="display: grid; grid-template-columns: 1fr 2fr auto; gap: 15px;">
                            <input type="text" name="ten_nxb" id="ten_nxb" class="form-control" placeholder="Tên NXB" required>
                            <input type="text" name="dia_chi" id="dia_chi" class="form-control" placeholder="Địa chỉ">
                            <div style="display: flex; gap: 5px;">
                                <button type="submit" name="save_nxb" class="btn-save" style="margin-top:0">LƯU DỮ LIỆU</button>
                                <button type="button" onclick="resetForm()" class="btn-cancel" style="display:none; background:#858796; color:white; border:none; padding:0 15px; border-radius:5px;">HỦY</button>
                            </div>
                        </div>
                    </form>
                </div>

                <table class="table-nxb">
                    <thead>
                        <tr>
                            <th width="10%">ID</th>
                            <th width="30%">Tên Nhà Xuất Bản</th>
                            <th width="45%">Địa Chỉ</th>
                            <th width="15%" style="text-align:center;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $res = $conn->query("SELECT * FROM nhaxuatban ORDER BY MaNXB DESC");
                        while($row = $res->fetch_assoc()):
                        ?>
                        <tr>
                            <td><code>#<?php echo $row['MaNXB']; ?></code></td>
                            <td><b><?php echo $row['TenNXB']; ?></b></td>
                            <td class="text-muted"><?php echo $row['DiaChi'] ?: '---'; ?></td>
                            <td style="text-align:center;">
                                <button class="btn-edit" onclick="editNXB(<?php echo $row['MaNXB']; ?>, '<?php echo addslashes($row['TenNXB']); ?>', '<?php echo addslashes($row['DiaChi']); ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="?delete=<?php echo $row['MaNXB']; ?>" class="btn-delete" onclick="return confirm('Xóa nhà xuất bản này?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function editNXB(id, ten, diachi) {
            document.getElementById('form-title').innerHTML = '<i class="fas fa-edit"></i> CẬP NHẬT NHÀ XUẤT BẢN';
            document.getElementById('ma_nxb').value = id;
            document.getElementById('ten_nxb').value = ten;
            document.getElementById('dia_chi').value = diachi;
            document.querySelector('.btn-cancel').style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('form-title').innerHTML = '<i class="fas fa-plus-circle"></i> THÊM NHÀ XUẤT BẢN';
            document.getElementById('ma_nxb').value = '';
            document.getElementById('ten_nxb').value = '';
            document.getElementById('dia_chi').value = '';
            document.querySelector('.btn-cancel').style.display = 'none';
        }
    </script>
</body>
</html>