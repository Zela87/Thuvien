<?php
include_once __DIR__ . '/../actions/auth-quan-ly-doc-gia.php';
require_once '../config/db.php';

$msg_session = $_SESSION['msg'] ?? "";
unset($_SESSION['msg']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Độc Giả - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style-main.css">
    <link rel="stylesheet" href="../assets/css/style-quan-ly-doc-gia.css">
</head>
<body>

    <?php include 'Index-sidebar.php' ?>

    <div class="main-content">
        <?php include 'Index-topbar.php' ?>

        <div class="content-body">
            <?php if($msg_session): 
                $m = explode('|', $msg_session); ?>
                <div style="padding:15px; border-radius:5px; margin-bottom:20px; background:<?php echo $m[0]=='success'?'#e5f9f0':'#fdf2f2'; ?>; color:<?php echo $m[0]=='success'?'#1cc88a':'#e74a3b'; ?>;">
                    <i class="fas fa-info-circle"></i> <?php echo $m[1]; ?>
                </div>
            <?php endif; ?>

            <div class="reader-card">
                <div class="action-bar">
                    <h3 style="color: #4e73df;"><i class="fas fa-users"></i> DANH SÁCH THẺ THƯ VIỆN</h3>
                    <form method="POST" style="display:flex; gap:10px; align-items:center;">
                        <select name="ma_doc_gia_auto" class="form-control" style="padding:8px;" required>
                            <option value="">-- Chọn độc giả chưa có thẻ --</option>
                            <?php
                            $sql_no_card = "SELECT MaDocGia, HoTen FROM DocGia WHERE MaDocGia NOT IN (SELECT MaDocGia FROM TheThuVien)";
                            $res_no = $conn->query($sql_no_card);
                            while($dg = $res_no->fetch_assoc()) echo "<option value='{$dg['MaDocGia']}'>{$dg['HoTen']}</option>";
                            ?>
                        </select>
                        <input type="number" name="tien_coc_auto" placeholder="Tiền cọc..." style="width:100px; padding:8px;" required>
                        <button type="submit" name="auto_card" class="btn-renew" style="padding:8px 15px; border-radius:5px; border:none; color:white; font-weight:bold; cursor:pointer;">CẤP THẺ</button>
                    </form>
                </div>

                <table class="table-readers">
                    <thead>
                        <tr>
                            <th>Số Thẻ</th>
                            <th>Độc Giả</th>
                            <th>Thời Hạn</th>
                            <th>Tiền Cọc</th>
                            <th>Trạng Thái</th>
                            <th style="text-align:center;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT t.*, d.HoTen FROM TheThuVien t JOIN DocGia d ON t.MaDocGia = d.MaDocGia ORDER BY t.NgayCap DESC";
                        $res = $conn->query($sql);
                        if($res->num_rows > 0):
                            while($row = $res->fetch_assoc()):
                                $isExpired = (strtotime($row['NgayHetHan']) < time());
                        ?>
                        <tr>
                            <td><code><?php echo $row['SoThe']; ?></code></td>
                            <td><b><?php echo $row['HoTen']; ?></b></td>
                            <td>
                                <small>Từ: <?php echo date('d/m/y', strtotime($row['NgayCap'])); ?></small><br>
                                <b style="color:<?php echo $isExpired?'#e74a3b':'#5a5c69'; ?>;">Đến: <?php echo date('d/m/y', strtotime($row['NgayHetHan'])); ?></b>
                            </td>
                            <td><?php echo number_format($row['TienDatCoc']); ?>đ</td>
                            <td>
                                <?php if($row['TrangThai'] == 1): ?>
                                    <span class="badge-card badge-active">Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge-card badge-locked">Đã khóa</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center; display:flex; gap:5px; justify-content:center;">
                                <form method="POST">
                                    <input type="hidden" name="so_the" value="<?php echo $row['SoThe']; ?>">
                                    <button type="submit" name="renew_card" class="btn-mini btn-renew" title="Gia hạn 1 năm"><i class="fas fa-calendar-plus"></i></button>
                                    
                                    <?php if($row['TrangThai'] == 1): ?>
                                        <input type="hidden" name="status" value="0">
                                        <button type="submit" name="toggle_status" class="btn-mini btn-lock" title="Khóa thẻ"><i class="fas fa-lock"></i></button>
                                    <?php else: ?>
                                        <input type="hidden" name="status" value="1">
                                        <button type="submit" name="toggle_status" class="btn-mini btn-unlock" title="Mở khóa"><i class="fas fa-unlock"></i></button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="6" style="text-align:center; padding:30px; color:#aaa;">Chưa có dữ liệu thẻ thư viện.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>