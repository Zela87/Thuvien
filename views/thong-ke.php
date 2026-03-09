<?php 
require '../config/db.php'; 
session_start();
$conn->set_charset("utf8mb4");

// Kiểm tra quyền hạn (Chỉ Admin/Thủ thư mới được xem thống kê)
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'QuanTriVien' && $_SESSION['role'] != 'ThuThu')) {
    header("Location: index.php");
    exit();
}

// --- 1. LẤY SỐ LIỆU TỔNG QUAN ---
$tongPhat = $conn->query("SELECT SUM(TienPhat) FROM ChiTietMuonTra")->fetch_row()[0] ?? 0;
$tongDauSach = $conn->query("SELECT COUNT(*) FROM DauSach")->fetch_row()[0];
$tongDocGia = $conn->query("SELECT COUNT(*) FROM DocGia")->fetch_row()[0];
$dangMuon = $conn->query("SELECT COUNT(*) FROM ChiTietMuonTra WHERE NgayTraThucTe IS NULL")->fetch_row()[0];
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
              WHERE ct.NgayTraThucTe IS NULL AND pm.NgayHenTra < CURDATE()";
$resQuaHan = $conn->query($sqlQuaHan);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống Kê Hệ Thống - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #2563eb;
            --dark-blue: #1e40af;
            --light-blue: #eff6ff;
            --border-color: #e2e8f0;
            --text-main: #1e293b;
            --text-sub: #64748b;
            --white: #ffffff;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', 'Segoe UI', sans-serif; }
        body { background-color: #f8fafc; color: var(--text-main); padding: 20px; line-height: 1.6; }
        .container { max-width: 1200px; margin: 0 auto; }

        /* Header & Navigation */
        .header-area { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .btn-back { text-decoration: none; color: var(--text-sub); font-weight: 500; transition: 0.2s; display: flex; align-items: center; gap: 8px; }
        .btn-back:hover { color: var(--primary-blue); }

        /* Stat Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .stat-icon {
            width: 50px; height: 50px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
        }
        .icon-blue { background: #dbeafe; color: var(--primary-blue); }
        .icon-green { background: #d1fae5; color: var(--success); }
        .icon-red { background: #fee2e2; color: var(--danger); }
        .icon-yellow { background: #fef3c7; color: var(--warning); }
        
        .stat-info h3 { font-size: 0.85rem; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.025em; }
        .stat-info p { font-size: 1.4rem; font-weight: 700; color: var(--text-main); }

        /* Content Card */
        .card {
            background: var(--white);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--white);
        }
        .card-header h2 { font-size: 1.1rem; color: var(--primary-blue); display: flex; align-items: center; gap: 10px; }

        /* Table Style */
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th {
            background: #f8fafc;
            padding: 12px 20px;
            text-align: left;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: var(--text-sub);
            border-bottom: 2px solid var(--border-color);
        }
        td { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; font-size: 0.95rem; }
        tr:hover { background-color: var(--light-blue); }

        .badge-red { background: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }

        /* Buttons */
        .btn-print {
            background: var(--primary-blue);
            color: white; border: none; padding: 10px 20px;
            border-radius: 8px; cursor: pointer; font-weight: 600;
            display: inline-flex; align-items: center; gap: 8px; transition: 0.3s;
        }
        .btn-print:hover { background: var(--dark-blue); transform: translateY(-1px); }

        @media print {
            .btn-back, .btn-print, .header-area { display: none !important; }
            body { background: white; padding: 0; }
            .card { border: none; box-shadow: none; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-area">
        <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Quay lại trang chủ</a>
        <button onclick="printReport()" class="btn-print"><i class="fas fa-print"></i> Xuất báo cáo PDF</button>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon icon-blue"><i class="fas fa-book"></i></div>
            <div class="stat-info">
                <h3>Đầu sách</h3>
                <p><?php echo number_format($tongDauSach); ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-green"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <h3>Độc giả</h3>
                <p><?php echo number_format($tongDocGia); ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-yellow"><i class="fas fa-exchange-alt"></i></div>
            <div class="stat-info">
                <h3>Đang mượn</h3>
                <p><?php echo number_format($dangMuon); ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-red"><i class="fas fa-hand-holding-usd"></i></div>
            <div class="stat-info">
                <h3>Tiền phạt</h3>
                <p><?php echo number_format($tongPhat); ?>đ</p>
            </div>
        </div>
    </div>

    <div class="card" id="printSection">
        <div class="card-header">
            <h2><i class="fas fa-exclamation-circle"></i> DANH SÁCH SÁCH QUÁ HẠN CHƯA TRẢ</h2>
            <span style="font-size: 0.85rem; color: var(--text-sub);">Ngày báo cáo: <?php echo date('d/m/Y'); ?></span>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Độc giả</th>
                        <th>Số thẻ</th>
                        <th>Tên sách (Mã vạch)</th>
                        <th>Hạn trả</th>
                        <th>Quá hạn</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resQuaHan->num_rows > 0): ?>
                        <?php while ($row = $resQuaHan->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $row['HoTen']; ?></strong></td>
                            <td><code><?php echo $row['SoThe']; ?></code></td>
                            <td>
                                <?php echo $row['TenSach']; ?><br>
                                <small style="color:var(--text-sub)">Mã: <?php echo $row['MaBanSach']; ?></small>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($row['NgayHenTra'])); ?></td>
                            <td><span class="badge-red"><?php echo $row['SoNgayTre']; ?> ngày</span></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 40px; color: var(--text-sub);">
                                <i class="fas fa-check-circle" style="font-size: 2rem; color: var(--success); display: block; margin-bottom: 10px;"></i>
                                Tuyệt vời! Hiện tại không có sách nào bị quá hạn.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 20px; font-size: 0.8rem; color: var(--text-sub); text-align: right;">
        * Thống kê được tạo tự động bởi hệ thống quản lý thư viện LIB MANAGE.
    </div>
</div>

<script>
    function printReport() {
        const printContents = document.getElementById('printSection').innerHTML;
        const originalContents = document.body.innerHTML;
        
        // Tạo cửa sổ in chuyên nghiệp
        const printWindow = window.open('', '', 'height=800,width=1000');
        printWindow.document.write('<html><head><title>Báo Cáo Thư Viện</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">');
        printWindow.document.write('<style>');
        printWindow.document.write('body{font-family:Arial,sans-serif;padding:30px;color:#333}');
        printWindow.document.write('.header-print{text-align:center;margin-bottom:30px;border-bottom:2px solid #2563eb;padding-bottom:10px}');
        printWindow.document.write('table{width:100%;border-collapse:collapse;margin-top:20px}');
        printWindow.document.write('th,td{border:1px solid #ddd;padding:12px;text-align:left}');
        printWindow.document.write('th{background:#f8fafc;font-size:12px;text-transform:uppercase}');
        printWindow.document.write('.badge-red{color:#ef4444;font-weight:bold}');
        printWindow.document.write('</style></head><body>');
        printWindow.document.write('<div class="header-print"><h1>BÁO CÁO THỐNG KÊ THƯ VIỆN</h1><p>Ngày lập: <?php echo date("d/m/Y H:i"); ?></p></div>');
        printWindow.document.write(printContents);
        printWindow.document.write('<div style="margin-top:50px;display:flex;justify-content:space-between">');
        printWindow.document.write('<div>Người lập phiếu<br><br><br><b>(Ký tên)</b></div>');
        printWindow.document.write('<div>Xác nhận của thủ thư<br><br><br><b>(Ký tên)</b></div>');
        printWindow.document.write('</div></body></html>');
        
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    }
</script>

</body>
</html>