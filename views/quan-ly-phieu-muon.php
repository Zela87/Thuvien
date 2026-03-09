<?php
session_start();
// Giả định file auth này thiết lập các biến $user_role, $ho_ten giống auth-index.php
include '../actions/auth-quan-ly-phieu-muon.php'; 
require_once '../config/db.php';
$conn->set_charset("utf8mb4");

// Các biến hỗ trợ hiển thị từ file auth (nếu có) hoặc mặc định
$ho_ten = isset($ho_ten) ? $ho_ten : "Người dùng";
$user_role = isset($user_role) ? $user_role : "guest";
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Phiếu Mượn - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="../assets/css/style-main.css">
    <link rel="stylesheet" href="../assets/css/style-index.css">
    <link rel="stylesheet" href="../assets/css/style-quan-ly-phieu-muon.css">
    
    
</head>

<body>

    <?php include 'Index-sidebar.php' ?>

    <div class="main-content">
        <?php include 'Index-topbar.php' ?>

        <div class="content-body">
            <div class="welcome-box">
                <h2 style="color: var(--primary-color); margin-bottom: 5px;">
                    <i class="fas fa-exchange-alt"></i> QUẢN LÝ MƯỢN - TRẢ
                </h2>
                <p style="color: #6e707e; font-size: 0.9rem;">Danh sách các đầu sách đang được độc giả mượn tại thư viện.</p>
            </div>

            <?php if (isset($msg) && $msg != "") echo $msg; ?>

            <div class="search-card">
                <form class="search-box" method="GET">
                    <input type="text" name="search" placeholder="Tên sách, độc giả, mã thẻ..." value="<?php echo isset($search) ? htmlspecialchars($search) : ''; ?>">
                    <select name="filter_status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="valid" <?php if (isset($filterStatus) && $filterStatus == 'valid') echo 'selected'; ?>>Trong hạn</option>
                        <option value="late" <?php if (isset($filterStatus) && $filterStatus == 'late') echo 'selected'; ?>>Quá hạn</option>
                    </select>
                    <button type="submit"><i class="fas fa-filter"></i> LỌC DỮ LIỆU</button>
                    <?php if (isset($search) || isset($filterStatus)): ?>
                        <a href="quan-ly-phieu-muon.php" style="color:var(--danger-color); text-decoration:none; align-self:center; font-weight:bold; font-size:0.8rem; margin-left:10px;">XÓA LỌC</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Mã PM</th>
                            <th>Độc Giả</th>
                            <th>Sách</th>
                            <th>Hạn Trả</th>
                            <th>Trạng Thái</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $whereCondition = isset($whereCondition) ? $whereCondition : "";
                        $sqlList = "SELECT pm.MaPhieuMuon, pm.SoThe, pm.NgayHenTra, pm.NgayMuon, 
                                       doc.HoTen, b.MaBanSach, d.TenSach
                                FROM PhieuMuon pm
                                JOIN ChiTietMuonTra ct ON pm.MaPhieuMuon = ct.MaPhieuMuon
                                JOIN BanSach b ON ct.MaBanSach = b.MaBanSach
                                JOIN DauSach d ON b.MaDauSach = d.MaDauSach
                                JOIN TheThuVien ttv ON pm.SoThe = ttv.SoThe
                                JOIN DocGia doc ON ttv.MaDocGia = doc.MaDocGia
                                WHERE ct.NgayTraThucTe IS NULL 
                                $whereCondition
                                ORDER BY pm.NgayHenTra ASC";

                        $resList = $conn->query($sqlList);
                        if ($resList && $resList->num_rows > 0) {
                            while ($row = $resList->fetch_assoc()) {
                                $isLate = (strtotime($row['NgayHenTra']) < time());
                        ?>
                                <tr>
                                    <td><small>#<?php echo $row['MaPhieuMuon']; ?></small></td>
                                    <td>
                                        <strong style="color:var(--primary-color)"><?php echo $row['HoTen']; ?></strong><br>
                                        <small style="color:#858796">Thẻ: <?php echo $row['SoThe']; ?></small>
                                    </td>
                                    <td>
                                        <strong style="color:#2e59d9"><?php echo $row['TenSach']; ?></strong><br>
                                        <small>Mã bản: <?php echo $row['MaBanSach']; ?></small>
                                    </td>
                                    <td><i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($row['NgayHenTra'])); ?></td>
                                    <td>
                                        <?php if ($isLate): ?>
                                            <span class="badge-status bg-late"><i class="fas fa-exclamation-circle"></i> Quá hạn</span>
                                        <?php else: ?>
                                            <span class="badge-status bg-ok"><i class="fas fa-clock"></i> Đang mượn</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn-return" onclick="openReturnModal('<?php echo $row['MaBanSach']; ?>', '<?php echo addslashes($row['TenSach']); ?>')">
                                            <i class="fas fa-undo"></i> THU HỒI
                                        </button>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding: 50px; color:#ccc;'>
                                    <i class='fas fa-folder-open' style='font-size: 2rem;'></i><br>Không có dữ liệu mượn sách.
                                  </td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="returnModal" class="modal">
        <div class="modal-content">
            <h3 style="color:var(--primary-color); margin-bottom:15px; text-transform:uppercase;">
                <i class="fas fa-clipboard-check"></i> Xác nhận trả sách
            </h3>
            <p style="font-size:0.95rem; margin-bottom:5px;">Sách: <b id="modalBookName" style="color:#333;"></b></p>
            <p style="font-size:0.85rem; color:#858796; margin-bottom:20px;">Mã bản sách: <span id="modalBookIdDisplay"></span></p>

            <form method="POST">
                <input type="hidden" name="ma_ban_sach_hide" id="modalBookIdInput">

                <div class="form-group">
                    <label>Tình trạng sách thực tế:</label>
                    <select name="tinh_trang_option">
                        <option value="BinhThuong">Bình thường / Tốt</option>
                        <option value="Rach">Rách nhẹ / Vẽ bậy</option>
                        <option value="Hong">Hỏng nặng / Mất trang</option>
                        <option value="Mat">Làm mất sách</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ghi chú chi tiết:</label>
                    <textarea name="ly_do_note" rows="3" placeholder="Nhập tình trạng cụ thể nếu có..."></textarea>
                </div>

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" name="btn_tra_modal" style="flex:2; background:var(--primary-color); color:white; border:none; padding:12px; border-radius:5px; cursor:pointer; font-weight:bold;">XÁC NHẬN TRẢ</button>
                    <button type="button" onclick="closeModal()" style="flex:1; background:#eaecf4; border:none; padding:12px; border-radius:5px; cursor:pointer; font-weight:bold; color:#5a5c69;">HỦY</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        var modal = document.getElementById("returnModal");

        function openReturnModal(id, name) {
            document.getElementById("modalBookName").innerText = name;
            document.getElementById("modalBookIdDisplay").innerText = id;
            document.getElementById("modalBookIdInput").value = id;
            modal.style.display = "block";
        }

        function closeModal() {
            modal.style.display = "none";
        }

        window.onclick = function(e) {
            if (e.target == modal) closeModal();
        }
    </script>

</body>

</html>