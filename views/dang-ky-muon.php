<?php
include_once __DIR__ . '/../actions/auth-dang-ky-muon.php';
require_once '../config/db.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Ký Mượn Sách - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style-main.css">
    <link rel="stylesheet" href="../assets/css/style-dang-ky-muon.css">
</head>
<body>

    <?php include 'Index-sidebar.php' ?>

    <div class="main-content">
        <?php include 'Index-topbar.php' ?>

        <div class="content-body">
            <?php if ($error_card): ?>
                <div style="text-align: center; padding: 50px; background: white; border-radius: 10px; border: 1px solid #e3e6f0;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #f6c23e; margin-bottom: 20px;"></i>
                    <h2 style="color: #4e73df;"><?php echo $error_card; ?></h2>
                    <p style="color: #858796; margin-top: 10px;">Vui lòng liên hệ thủ thư để được hỗ trợ xử lý thẻ.</p>
                </div>
            <?php else: ?>
                
                <div class="borrow-grid">
                    <div class="info-box">
                        <h3 style="color: #4e73df; margin-bottom: 15px;"><i class="fas fa-id-card"></i> Thẻ Thư Viện</h3>
                        <div style="line-height: 2;">
                            <p>Độc giả: <b><?php echo $docGia['HoTen']; ?></b></p>
                            <p>Số thẻ: <code style="background: #f8f9fc; padding: 2px 5px;"><?php echo $docGia['SoThe']; ?></code></p>
                            <p>Hạn dùng: <?php echo date('d/m/Y', strtotime($docGia['NgayHetHan'])); ?></p>
                            <hr style="margin: 10px 0; border: 0; border-top: 1px solid #eee;">
                            <p>Giới hạn: <b><?php echo $maxSach; ?> cuốn</b></p>
                            <p>Đang mượn: <b style="color: #1cc88a;"><?php echo $soSachDangMuon; ?></b></p>
                            <p>Chờ duyệt: <b style="color: #f6c23e;"><?php echo $soSachChoDuyet; ?></b></p>
                            <p>Có thể đăng ký: <b style="color: #4e73df; font-size: 1.2rem;"><?php echo max(0, $soSachCoTheMuon); ?></b></p>
                        </div>

                        <form method="POST" id="borrowForm">
                            <div id="selectedDisplay" style="margin-top: 15px; font-size: 0.85rem; color: #4e73df; display:none;">
                                <i class="fas fa-check-circle"></i> Đã chọn <b id="countText">0</b> bản sách.
                            </div>
                            <button type="submit" name="btn_dang_ky" id="submitBtn" class="btn-borrow" disabled>
                                GỬI YÊU CẦU MƯỢN
                            </button>
                        </form>
                    </div>

                    <div class="book-table-card">
                        <h3 style="color: #4e73df;"><i class="fas fa-book"></i> Danh Mục Sách Khả Dụng</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Tên Sách</th>
                                    <th>Thông tin</th>
                                    <th>Bản sách (Click để chọn)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT d.MaDauSach, d.TenSach, n.TenNXB, GROUP_CONCAT(DISTINCT t.TenTacGia) AS TacGia
                                        FROM dausach d 
                                        LEFT JOIN nhaxuatban n ON d.MaNXB = n.MaNXB
                                        LEFT JOIN tacgia_sach ts ON d.MaDauSach = ts.MaDauSach
                                        LEFT JOIN tacgia t ON ts.MaTacGia = t.MaTacGia
                                        GROUP BY d.MaDauSach ORDER BY d.MaDauSach DESC";
                                $res = $conn->query($sql);
                                while ($row = $res->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><b style="color: #2e59d9;"><?php echo $row['TenSach']; ?></b></td>
                                    <td style="font-size: 0.8rem; color: #858796;">
                                        <?php echo $row['TacGia']; ?><br><?php echo $row['TenNXB']; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $maDS = $row['MaDauSach'];
                                        $sql_copies = "SELECT b.*, 
                                                       (SELECT COUNT(*) FROM yeucaumuon WHERE MaBanSach = b.MaBanSach AND TrangThai = 'ChoDuyet') as ChoDuyet
                                                       FROM bansach b WHERE b.MaDauSach = $maDS";
                                        $res_copies = $conn->query($sql_copies);
                                        while ($copy = $res_copies->fetch_assoc()):
                                            $class = "bc-red"; $click = ""; $title = "Không sẵn sàng";
                                            if ($copy['ChoDuyet'] > 0) { $class = "bc-yellow"; $title = "Đang chờ duyệt"; }
                                            elseif ($copy['TinhTrang'] == 1) { $class = "bc-green"; $click = "onclick='toggleBook(this)'"; $title = "Có thể mượn"; }
                                        ?>
                                            <span class="barcode-tag <?php echo $class; ?>" 
                                                  data-id="<?php echo $copy['MaBanSach']; ?>" 
                                                  title="<?php echo $title; ?>" <?php echo $click; ?>>
                                                <?php echo $copy['MaBanSach']; ?>
                                            </span>
                                        <?php endwhile; ?>
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

    <script>
        const maxAllowed = <?php echo max(0, $soSachCoTheMuon); ?>;
        let selected = new Set();

        function toggleBook(el) {
            const id = el.dataset.id;
            if (selected.has(id)) {
                selected.delete(id);
                el.classList.remove('selected');
            } else {
                if (selected.size >= maxAllowed) {
                    alert('Bạn chỉ được chọn tối đa ' + maxAllowed + ' cuốn!');
                    return;
                }
                selected.add(id);
                el.classList.add('selected');
            }
            
            document.getElementById('countText').innerText = selected.size;
            document.getElementById('selectedDisplay').style.display = selected.size > 0 ? 'block' : 'none';
            document.getElementById('submitBtn').disabled = selected.size === 0;

            // Cập nhật input ẩn cho form
            const form = document.getElementById('borrowForm');
            form.querySelectorAll('input[name="selected_books[]"]').forEach(i => i.remove());
            selected.forEach(bookId => {
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = 'selected_books[]'; input.value = bookId;
                form.appendChild(input);
            });
        }
    </script>
</body>
</html>