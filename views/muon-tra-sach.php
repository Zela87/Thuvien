<?php
include_once __DIR__ . '/../actions/auth-muon-tra.php';
require_once '../config/db.php';

$activeTab = $_GET['tab'] ?? 'duyet_yeu_cau';
$msg_session = $_SESSION['msg'] ?? "";
unset($_SESSION['msg']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Mượn Trả Sách - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style-main.css">
    <link rel="stylesheet" href="../assets/css/style-muontra.css">
</head>
<body>

    <?php include 'Index-sidebar.php' ?>

    <div class="main-content">
        <?php include 'Index-topbar.php' ?>

        <div class="content-body">
            <div class="mt-container">
                
                <?php if($msg_session): 
                    $m = explode('|', $msg_session); ?>
                    <div class="alert-box alert-<?php echo $m[0]; ?>">
                        <i class="fas <?php echo $m[0]=='success'?'fa-check-circle':'fa-exclamation-circle'; ?>"></i> <?php echo $m[1]; ?>
                    </div>
                <?php endif; ?>

                <div class="mt-tabs">
                    <button class="mt-tab-btn <?php echo $activeTab=='duyet_yeu_cau'?'active':''; ?>" onclick="location.href='?tab=duyet_yeu_cau'">
                        <i class="fas fa-tasks"></i> Duyệt Yêu Cầu
                    </button>
                    <button class="mt-tab-btn <?php echo $activeTab=='tra_sach'?'active':''; ?>" onclick="location.href='?tab=tra_sach'">
                        <i class="fas fa-undo"></i> Trả Sách
                    </button>
                    <button class="mt-tab-btn <?php echo $activeTab=='muon_truc_tiep'?'active':''; ?>" onclick="location.href='?tab=muon_truc_tiep'">
                        <i class="fas fa-plus-circle"></i> Mượn Tại Quầy
                    </button>
                </div>

                <?php if($activeTab == 'duyet_yeu_cau'): ?>
                <div class="mt-card">
                    <h3><i class="fas fa-clipboard-list"></i> Danh sách chờ duyệt</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Độc giả</th>
                                <th>Thông tin sách</th>
                                <th>Ngày đăng ký</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT yc.*, doc.HoTen, d.TenSach, ttv.SoThe FROM yeucaumuon yc 
                                    JOIN DocGia doc ON yc.MaDocGia = doc.MaDocGia
                                    JOIN TheThuVien ttv ON doc.MaDocGia = ttv.MaDocGia
                                    JOIN BanSach b ON yc.MaBanSach = b.MaBanSach
                                    JOIN DauSach d ON b.MaDauSach = d.MaDauSach WHERE yc.TrangThai = 'ChoDuyet'";
                            $res = $conn->query($sql);
                            if($res->num_rows > 0):
                                while($row = $res->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <b><?php echo $row['HoTen']; ?></b><br>
                                        <small class="text-muted">Số thẻ: <?php echo $row['SoThe']; ?></small>
                                    </td>
                                    <td>
                                        <span class="text-primary"><?php echo $row['TenSach']; ?></span><br>
                                        <code>Mã BS: <?php echo $row['MaBanSach']; ?></code>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($row['NgayYeuCau'])); ?></td>
                                    <td style="text-align:center;">
                                        <form method="POST">
                                            <input type="hidden" name="ma_yeu_cau" value="<?php echo $row['MaYeuCau']; ?>">
                                            <button type="submit" name="btn_duyet" class="btn-action btn-success"><i class="fas fa-check"></i> DUYỆT</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="4" style="text-align:center; padding:40px; color:#858796;">Hiện không có yêu cầu nào cần xử lý.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <?php if($activeTab == 'tra_sach'): ?>
                <div class="mt-card">
                    <h3><i class="fas fa-reply"></i> Nhận Sách Trả</h3>
                    <form method="POST" class="form-row">
                        <div class="form-group">
                            <label>Quét hoặc nhập Mã Bản Sách</label>
                            <input type="text" name="ma_bs_tra" class="form-control" placeholder="Nhập mã bản sách (ví dụ: BS001)" required autofocus>
                        </div>
                        <button type="submit" name="btn_tra_sach" class="btn-action">HOÀN TẤT TRẢ</button>
                    </form>
                </div>
                <?php endif; ?>

                <?php if($activeTab == 'muon_truc_tiep'): ?>
                <div class="mt-card">
                    <h3><i class="fas fa-barcode"></i> Đăng Ký Mượn Tại Quầy</h3>
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Mã Số Thẻ Độc Giả</label>
                                <input type="text" name="so_the" class="form-control" placeholder="Quét thẻ độc giả..." required>
                            </div>
                            <div class="form-group">
                                <label>Mã Bản Sách (Barcode)</label>
                                <input type="text" name="ma_bs_muon" class="form-control" placeholder="Quét mã vạch sách..." required>
                            </div>
                        </div>
                        <button type="submit" name="btn_muon_truc_tiep" class="btn-action" style="width: 100%;">
                            XÁC NHẬN CHO MƯỢN
                        </button>
                    </form>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

</body>
</html>