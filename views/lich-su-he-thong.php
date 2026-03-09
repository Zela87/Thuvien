<?php
include_once __DIR__ . '/../actions/auth-lich-su-he-thong.php';
require_once '../config/db.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
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
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="color: #4e73df;"><i class="fas fa-history"></i> NHẬT KÝ HOẠT ĐỘNG</h3>
                </div>

                <div class="tab-container">
                    <?php if ($role == 'QuanTriVien'): ?>
                        <button class="tab-btn active" onclick="openTab(event, 'logs')">Lịch sử hệ thống</button>
                    <?php endif; ?>
                    
                    <button class="tab-btn <?php echo ($role != 'QuanTriVien') ? 'active' : ''; ?>" onclick="openTab(event, 'borrow')">Lịch sử mượn trả</button>
                    
                    <?php if ($role == 'DocGia'): ?>
                        <button class="tab-btn" onclick="openTab(event, 'requests')">Yêu cầu đăng ký</button>
                    <?php endif; ?>
                </div>

                <?php if ($role == 'QuanTriVien'): ?>
                <div id="logs" class="tab-content active">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Thời gian</th>
                                    <th>Người thực hiện</th>
                                    <th>Hành động</th>
                                    <th>Chi tiết</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $res_logs->fetch_assoc()): ?>
                                <tr>
                                    <td style="white-space: nowrap;"><?php echo date('d/m/Y H:i', strtotime($row['ThoiGian'])); ?></td>
                                    <td><span class="status-pill pill-success"><?php echo $row['NguoiThucHien']; ?></span></td>
                                    <td><b><?php echo $row['HanhDong']; ?></b></td>
                                    <td><?php echo htmlspecialchars($row['ChiTiet']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <div id="borrow" class="tab-content <?php echo ($role != 'QuanTriVien') ? 'active' : ''; ?>">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <?php if($role != 'DocGia') echo "<th>Số Thẻ</th>"; ?>
                                    <th>Tên Sách</th>
                                    <th>Ngày Mượn</th>
                                    <th>Ngày Trả</th>
                                    <th>Trạng Thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $res_borrow->fetch_assoc()): ?>
                                <tr>
                                    <?php if($role != 'DocGia') echo "<td><code>{$row['SoThe']}</code></td>"; ?>
                                    <td><b><?php echo $row['TenSach']; ?></b></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['NgayMuon'])); ?></td>
                                    <td><?php echo $row['NgayTraThucTe'] ? date('d/m/Y', strtotime($row['NgayTraThucTe'])) : '---'; ?></td>
                                    <td>
                                        <?php if($row['NgayTraThucTe']): ?>
                                            <span class="status-pill pill-success">Đã trả</span>
                                        <?php else: ?>
                                            <span class="status-pill pill-warning">Đang mượn</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if ($role == 'DocGia'): ?>
                <div id="requests" class="tab-content">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Mã Bản Sách</th>
                                    <th>Tên Sách</th>
                                    <th>Ngày Yêu Cầu</th>
                                    <th>Trạng Thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $res_req->fetch_assoc()): ?>
                                <tr>
                                    <td><code><?php echo $row['MaBanSach']; ?></code></td>
                                    <td><?php echo $row['TenSach']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['NgayYeuCau'])); ?></td>
                                    <td>
                                        <?php 
                                        if($row['TrangThai'] == 'ChoDuyet') echo "<span class='status-pill pill-warning'>Đang chờ duyệt</span>";
                                        else echo "<span class='status-pill pill-danger'>Đã hủy/Từ chối</span>";
                                        ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
                tabcontent[i].classList.remove("active");
            }
            tablinks = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.className += " active";
        }
    </script>
</body>
</html>