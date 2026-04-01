<?php
include_once __DIR__ . '/../actions/auth-index.php';
require_once '../config/db.php';

// Lấy thông tin người dùng từ session
$ho_ten = isset($_SESSION['ho_ten']) ? $_SESSION['ho_ten'] : 'Khách';
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';

$conn->set_charset("utf8mb4");

// Kiểm tra quyền hạn
if ($user_role != 'QuanTriVien' && $user_role != 'ThuThu') {
    header("Location: index.php");
    exit();
}

// --- 1. SỐ LIỆU TỔNG QUAN ---
$tongPhat    = $conn->query("SELECT SUM(TienPhat) FROM ChiTietMuonTra")->fetch_row()[0] ?? 0;
$tongDauSach = $conn->query("SELECT COUNT(*) FROM DauSach")->fetch_row()[0];
$tongDocGia  = $conn->query("SELECT COUNT(*) FROM DocGia")->fetch_row()[0];
$dangMuon    = $conn->query("SELECT COUNT(*) FROM ChiTietMuonTra WHERE NgayTraThucTe IS NULL")->fetch_row()[0];
$sachThanhLy = $conn->query("SELECT COUNT(*) FROM BanSach WHERE TinhTrang = 3")->fetch_row()[0];

// --- 2. DANH SÁCH QUÁ HẠN ---
$sqlQuaHan = "SELECT dg.HoTen, ttv.SoThe, ds.TenSach, bs.MaBanSach, pm.NgayHenTra,
                DATEDIFF(CURDATE(), pm.NgayHenTra) as SoNgayTre
              FROM ChiTietMuonTra ct
              JOIN BanSach bs ON ct.MaBanSach = bs.MaBanSach
              JOIN DauSach ds ON bs.MaDauSach = ds.MaDauSach
              JOIN PhieuMuon pm ON ct.MaPhieuMuon = pm.MaPhieuMuon
              JOIN TheThuVien ttv ON pm.SoThe = ttv.SoThe
              JOIN DocGia dg ON ttv.MaDocGia = dg.MaDocGia
              WHERE ct.NgayTraThucTe IS NULL AND pm.NgayHenTra < CURDATE()
              ORDER BY SoNgayTre DESC";
$resQuaHan = $conn->query($sqlQuaHan);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống Kê Hệ Thống - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style-main.css">
    <link rel="stylesheet" href="../assets/css/style-thong-ke.css">
</head>
<body>

    <?php include 'Index-sidebar.php'; ?>

    <div class="main-content">
        <?php include 'Index-topbar.php'; ?>

        <div class="content-body">

            <div class="page-header-row">
                <div class="page-header" style="margin-bottom:0;">
                    <h1><i class="fas fa-chart-bar" style="color: var(--primary-color);"></i> Thống Kê Hệ Thống</h1>
                    <p>Tổng quan hoạt động thư viện và danh sách sách quá hạn</p>
                </div>
                <button onclick="printReport()" class="btn-print">
                    <i class="fas fa-print"></i> Xuất Báo Cáo PDF
                </button>
            </div>

            <!-- STAT CARDS -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon icon-blue"><i class="fas fa-book"></i></div>
                    <div class="stat-info">
                        <h3>Đầu Sách</h3>
                        <p><?php echo number_format($tongDauSach); ?></p>
                    </div>
                </div>
                <div class="stat-card green">
                    <div class="stat-icon icon-green"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <h3>Độc Giả</h3>
                        <p><?php echo number_format($tongDocGia); ?></p>
                    </div>
                </div>
                <div class="stat-card yellow">
                    <div class="stat-icon icon-yellow"><i class="fas fa-exchange-alt"></i></div>
                    <div class="stat-info">
                        <h3>Đang Mượn</h3>
                        <p><?php echo number_format($dangMuon); ?></p>
                    </div>
                </div>
                <div class="stat-card red">
                    <div class="stat-icon icon-red"><i class="fas fa-hand-holding-usd"></i></div>
                    <div class="stat-info">
                        <h3>Tổng Tiền Phạt</h3>
                        <p><?php echo number_format($tongPhat); ?>đ</p>
                    </div>
                </div>
            </div>

            <!-- BẢNG QUÁ HẠN -->
            <div class="card" id="printSection">
                <div class="card-header">
                    <h2>
                        <i class="fas fa-exclamation-circle"></i>
                        Danh Sách Sách Quá Hạn Chưa Trả
                    </h2>
                    <span class="report-date">Ngày báo cáo: <?php echo date('d/m/Y'); ?></span>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Độc Giả</th>
                                <th>Số Thẻ</th>
                                <th>Tên Sách</th>
                                <th>Hạn Trả</th>
                                <th>Quá Hạn</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resQuaHan->num_rows > 0): ?>
                                <?php while ($row = $resQuaHan->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['HoTen']); ?></strong></td>
                                    <td><span class="mono"><?php echo $row['SoThe']; ?></span></td>
                                    <td>
                                        <div class="td-book-name"><?php echo htmlspecialchars($row['TenSach']); ?></div>
                                        <div class="td-book-code">Mã: <?php echo $row['MaBanSach']; ?></div>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($row['NgayHenTra'])); ?></td>
                                    <td><span class="badge-overdue"><i class="fas fa-clock"></i> <?php echo $row['SoNgayTre']; ?> ngày</span></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <i class="fas fa-check-circle"></i>
                                            <p>Tuyệt vời! Hiện tại không có sách nào bị quá hạn.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="report-footer">
                * Thống kê được tạo tự động bởi hệ thống quản lý thư viện LIB MANAGE.
            </div>

        </div><!-- /.content-body -->
    </div><!-- /.main-content -->

    <script>
        function printReport() {
            const printContents = document.getElementById('printSection').innerHTML;
            const printWindow = window.open('', '', 'height=800,width=1000');
            printWindow.document.write('<html><head><title>Báo Cáo Thư Viện</title>');
            printWindow.document.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">');
            printWindow.document.write('<style>');
            printWindow.document.write('body{font-family:Arial,sans-serif;padding:30px;color:#333}');
            printWindow.document.write('.header-print{text-align:center;margin-bottom:30px;border-bottom:2px solid #4e73df;padding-bottom:15px}');
            printWindow.document.write('.header-print h1{color:#4e73df;font-size:1.4rem;margin-bottom:5px}');
            printWindow.document.write('table{width:100%;border-collapse:collapse;margin-top:20px}');
            printWindow.document.write('th,td{border:1px solid #ddd;padding:10px 14px;text-align:left}');
            printWindow.document.write('th{background:#f8f9fc;font-size:11px;text-transform:uppercase;color:#666}');
            printWindow.document.write('.badge-overdue{color:#e74a3b;font-weight:bold}');
            printWindow.document.write('.sign-row{margin-top:60px;display:flex;justify-content:space-between}');
            printWindow.document.write('.sign-col{text-align:center;min-width:180px}');
            printWindow.document.write('</style></head><body>');
            printWindow.document.write('<div class="header-print"><h1>BÁO CÁO THỐNG KÊ THƯ VIỆN</h1><p>Ngày lập: <?php echo date("d/m/Y H:i"); ?></p></div>');
            printWindow.document.write(printContents);
            printWindow.document.write('<div class="sign-row">');
            printWindow.document.write('<div class="sign-col">Người lập phiếu<br><br><br><strong>(Ký tên)</strong></div>');
            printWindow.document.write('<div class="sign-col">Xác nhận của thủ thư<br><br><br><strong>(Ký tên)</strong></div>');
            printWindow.document.write('</div></body></html>');
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => { printWindow.print(); printWindow.close(); }, 500);
        }
    </script>
</body>
</html>