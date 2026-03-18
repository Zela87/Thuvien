<?php
// Gọi auth và db từ file bạn cung cấp
require_once '../actions/auth-lich-su-he-thong.php'; 

// Lấy thông tin hiển thị cho Topbar
$ho_ten = $_SESSION['ho_ten'] ?? 'Nhân viên';
$user_role = $_SESSION['role'] ?? 'guest';

// Helper thoát ký tự
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Hệ Thống - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style-main.css">
    <link rel="stylesheet" href="../assets/css/style-lich-su-he-thong.css">
</head>
<body>

    <?php include 'Index-sidebar.php' ?>

    <div class="main-content">
        <?php include 'Index-topbar.php' ?>

        <div class="content-body">
            <div class="history-card">
                <div style="margin-bottom: 20px;">
                    <h2 style="color: var(--primary-color);"><i class="fas fa-history"></i> Lịch Sử Hoạt Động</h2>
                    <p style="color: #858796; font-size: 0.9rem;">Hệ thống ghi lại toàn bộ nhật ký mượn trả và thay đổi.</p>
                </div>

                <div class="tab-nav">
                    <button class="tab-btn active" onclick="openTab('nhat-ky', this)">📜 Nhật Ký Hoạt Động</button>
                    <button class="tab-btn" onclick="openTab('muon-tra', this)">🔄 Lịch Sử Mượn Trả</button>
                    <button class="tab-btn" onclick="openTab('thanh-ly', this)">📚 Sách Đã Thanh Lý</button>
                </div>

                <!-- Tab 1: Nhật Ký Hoạt Động -->
                <div id="nhat-ky" class="tab-content active">
                    <div class="table-responsive">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Thời Gian</th>
                                    <th>Người Thực Hiện</th>
                                    <th>Hành Động</th>
                                    <th>Chi Tiết / Lý Do</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history_nhat_ky as $row): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($row['ThoiGian'])) ?></td>
                                        <td><?= e($row['NguoiThucHien']) ?></td>
                                        <td><b><?= e($row['HanhDong']) ?></b></td>
                                        <td><?= e($row['ChiTiet']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 2: Lịch Sử Mượn Trả -->
                <div id="muon-tra" class="tab-content">
                    <div class="table-responsive">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Ngày Mượn</th>
                                    <th>Độc Giả</th>
                                    <th>Sách</th>
                                    <th>Ngày Trả</th>
                                    <th>Tình Trạng Sách</th>
                                    <th>Trạng Thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history_muon_tra as $row): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($row['NgayMuon'])) ?></td>
                                        <td><?= e($row['HoTenDocGia']) ?></td>
                                        <td><?= e($row['TenSach']) ?></td>
                                        <td><?= $row['NgayTraThucTe'] ? date('d/m/Y', strtotime($row['NgayTraThucTe'])) : '---' ?></td>
                                        <td><?= e($row['TinhTrangSach'] ?? '---') ?></td>
                                        <td>
                                            <?php if ($row['NgayTraThucTe']): ?>
                                                <span class="status-badge bg-success-light">Đã trả</span>
                                            <?php else: ?>
                                                <span class="status-badge bg-warning-light">Chưa trả</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 3: Sách Đã Thanh Lý -->
                <div id="thanh-ly" class="tab-content">
                    <div class="table-responsive">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Mã Sách</th>
                                    <th>Tên Sách</th>
                                    <th>Trạng Thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history_thanh_ly as $row): ?>
                                    <tr>
                                        <td><b><?= e($row['MaBanSach']) ?></b></td>
                                        <td><?= e($row['TenSach']) ?></td>
                                        <td><span class="status-badge bg-danger-light">Đã thanh lý</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function openTab(tabId, btn) {
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            btn.classList.add('active');
        }
    </script>
</body>
</html>