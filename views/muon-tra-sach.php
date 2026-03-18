?<?php
include '../actions/auth-muon-tra.php';
require_once '../config/db.php';
$conn->set_charset("utf8mb4");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mượn / Trả Sách - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style-main.css">
    <link rel="stylesheet" href="../assets/css/style-muon-tra.css">
</head>
<body>

    <?php include 'Index-sidebar.php'; ?>

    <div class="main-content">
        <?php include 'Index-topbar.php'; ?>

        <div class="content-body">

            <?php if ($msg) echo $msg; ?>

            <!-- TAB NAV -->
            <div class="tab-nav">
                <a href="?tab=muon_truc_tiep" class="tab-btn <?php echo $activeTab == 'muon_truc_tiep' ? 'active' : ''; ?>">
                    <i class="fas fa-book-reader"></i> Mượn Trực Tiếp
                </a>
                <a href="?tab=yeu_cau" class="tab-btn <?php echo $activeTab == 'yeu_cau' ? 'active' : ''; ?>">
                    <i class="fas fa-clipboard-list"></i> Yêu Cầu Mượn Online
                    <?php
                    $sqlCountYC = "SELECT COUNT(*) as cnt FROM yeucaumuon WHERE TrangThai = 'ChoDuyet'";
                    $countYC = $conn->query($sqlCountYC)->fetch_assoc()['cnt'];
                    if ($countYC > 0) echo "<span class='badge'>$countYC</span>";
                    ?>
                </a>
            </div>

            <!-- ===== TAB: MƯỢN TRỰC TIẾP ===== -->
            <div class="tab-content <?php echo $activeTab == 'muon_truc_tiep' ? 'active' : ''; ?>">
                <div class="two-col-layout">

                    <!-- CỘT TRÁI: FORM QUY TRÌNH -->
                    <div class="card">
                        <h2 class="card-title"><i class="fas fa-barcode"></i> Quy Trình Mượn</h2>

                        <div class="steps">
                            <div class="step-item <?php echo $step == 1 ? 'active' : ($step > 1 ? 'done' : ''); ?>">
                                <i class="fas fa-id-card"></i> 1. Nhập Thẻ
                            </div>
                            <div class="step-item <?php echo $step == 2 ? 'active' : ($step > 2 ? 'done' : ''); ?>">
                                <i class="fas fa-book"></i> 2. Nhập Sách
                            </div>
                            <div class="step-item <?php echo $step == 3 ? 'active' : ''; ?>">
                                <i class="fas fa-check"></i> 3. Xác Nhận
                            </div>
                        </div>

                        <?php if ($step == 1): ?>
                            <form method="POST" action="?tab=muon_truc_tiep">
                                <div class="form-group">
                                    <label>Mã Số Thẻ</label>
                                    <input type="text" name="the"
                                           value="<?php echo isset($_POST['the']) ? htmlspecialchars($_POST['the']) : ''; ?>"
                                           required placeholder="Quét hoặc nhập mã thẻ" autofocus>
                                </div>
                                <button type="submit" name="btn_check_card" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Kiểm Tra Thẻ
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if ($step == 2): ?>
                            <div class="alert alert-success" style="padding: 10px; margin-bottom: 15px;">
                                <i class="fas fa-user-check"></i> Thẻ hợp lệ. Vui lòng nhập sách.
                                <a href="?tab=muon_truc_tiep" style="float:right; color: #155724; font-weight:bold;">[Đổi thẻ]</a>
                            </div>
                            <form method="POST" action="?tab=muon_truc_tiep">
                                <input type="hidden" name="the" value="<?php echo htmlspecialchars($card_data['SoThe']); ?>">
                                <div class="form-group">
                                    <label>Mã Vạch Sách</label>
                                    <input type="text" name="masach" required placeholder="Quét mã sách" autofocus>
                                </div>
                                <div class="form-group">
                                    <label>Ngày Hẹn Trả</label>
                                    <input type="date" name="ngaytra" value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Ghi Chú</label>
                                    <textarea name="ghi_chu_muon" rows="2" placeholder="Ghi chú (nếu có)..."></textarea>
                                </div>
                                <button type="submit" name="btn_check_book" class="btn btn-primary">
                                    <i class="fas fa-arrow-right"></i> Kiểm Tra Sách
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if ($step == 3): ?>
                            <div class="alert alert-success" style="padding: 10px; margin-bottom: 15px;">
                                <i class="fas fa-check-double"></i> Mọi thông tin hợp lệ!
                            </div>
                            <form method="POST" action="?tab=muon_truc_tiep">
                                <input type="hidden" name="the_confirm"     value="<?php echo htmlspecialchars($card_data['SoThe']); ?>">
                                <input type="hidden" name="masach_confirm"  value="<?php echo htmlspecialchars($book_data['MaBanSach']); ?>">
                                <input type="hidden" name="ngaytra_confirm" value="<?php echo htmlspecialchars($_POST['ngaytra']); ?>">
                                <input type="hidden" name="ghi_chu_confirm" value="<?php echo htmlspecialchars($_POST['ghi_chu_muon']); ?>">
                                <button type="submit" name="btn_confirm" class="btn btn-success">
                                    <i class="fas fa-save"></i> XÁC NHẬN TẠO PHIẾU
                                </button>
                                <a href="?tab=muon_truc_tiep" class="btn btn-secondary" style="text-align:center; text-decoration:none; display:block; margin-top:10px;">
                                    Hủy Bỏ
                                </a>
                            </form>
                        <?php endif; ?>
                    </div>

                    <!-- CỘT PHẢI: THÔNG TIN CHI TIẾT -->
                    <div class="card">
                        <h2 class="card-title"><i class="fas fa-info-circle"></i> Thông Tin Chi Tiết</h2>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> <b>CÓ LỖI XẢY RA:</b><br>
                                <?php echo implode("<br>", $errors); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($card_data): ?>
                            <div class="info-card">
                                <h3><i class="fas fa-id-card"></i> Thông Tin Độc Giả</h3>
                                <div class="info-row"><span>Họ Tên:</span><b><?php echo htmlspecialchars($card_data['HoTen']); ?></b></div>
                                <div class="info-row"><span>Số Thẻ:</span><span><?php echo htmlspecialchars($card_data['SoThe']); ?></span></div>
                                <div class="info-row">
                                    <span>Trạng Thái:</span>
                                    <span style="color:<?php echo $card_data['TrangThaiThe'] == 1 ? 'green' : 'red'; ?>">
                                        <?php echo $card_data['TrangThaiThe'] == 1 ? 'Đang hoạt động' : 'Bị khóa'; ?>
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span>Hạn Thẻ:</span>
                                    <span style="color:<?php echo strtotime($card_data['NgayHetHan']) < time() ? 'red' : 'inherit'; ?>">
                                        <?php echo date('d/m/Y', strtotime($card_data['NgayHetHan'])); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($book_data): ?>
                            <div class="info-card">
                                <h3><i class="fas fa-book"></i> Thông Tin Sách</h3>
                                <div class="info-row"><span>Tên Sách:</span><b><?php echo htmlspecialchars($book_data['TenSach']); ?></b></div>
                                <div class="info-row"><span>Mã Vạch:</span><span><?php echo htmlspecialchars($book_data['MaBanSach']); ?></span></div>
                                <div class="info-row"><span>Tác Giả:</span><span><?php echo htmlspecialchars($book_data['TacGia']); ?></span></div>
                                <div class="info-row">
                                    <span>Tình Trạng:</span>
                                    <span style="color:<?php echo $book_data['TinhTrangSach'] == 1 ? 'green' : 'red'; ?>">
                                        <?php echo $book_data['TinhTrangSach'] == 1 ? 'Sẵn sàng' : 'Không sẵn sàng'; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!$card_data && !$book_data && empty($errors)): ?>
                            <div class="empty-state">
                                <i class="fas fa-id-card"></i>
                                <p>Vui lòng nhập mã thẻ để bắt đầu.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <!-- ===== TAB: YÊU CẦU MƯỢN ONLINE ===== -->
            <div class="tab-content <?php echo $activeTab == 'yeu_cau' ? 'active' : ''; ?>">
                <div class="card">
                    <h2 class="card-title"><i class="fas fa-list-alt"></i> Danh Sách Chờ Duyệt</h2>

                    <?php
                    $sqlReaders = "SELECT doc.MaDocGia, doc.HoTen, doc.TenDangNhap, ttv.SoThe,
                                          COUNT(yc.MaYeuCau) as SoSachYeuCau
                                   FROM yeucaumuon yc
                                   JOIN DocGia doc ON yc.MaDocGia = doc.MaDocGia
                                   LEFT JOIN TheThuVien ttv ON doc.MaDocGia = ttv.MaDocGia
                                   WHERE yc.TrangThai = 'ChoDuyet'
                                   GROUP BY doc.MaDocGia
                                   ORDER BY MIN(yc.NgayYeuCau) ASC";
                    $resReaders = $conn->query($sqlReaders);
                    $selectedReader = isset($_GET['reader']) ? (int)$_GET['reader'] : null;

                    if ($resReaders->num_rows > 0):
                    ?>
                        <div class="two-col-layout">
                            <!-- CỘT TRÁI: DANH SÁCH ĐỘC GIẢ -->
                            <div>
                                <h3 class="section-title"><i class="fas fa-users"></i> Độc Giả Chờ Duyệt</h3>
                                <div class="reader-list">
                                    <?php while ($reader = $resReaders->fetch_assoc()): ?>
                                        <div class="reader-item <?php echo $selectedReader == $reader['MaDocGia'] ? 'selected' : ''; ?>"
                                             onclick="window.location.href='?tab=yeu_cau&reader=<?php echo $reader['MaDocGia']; ?>'">
                                            <div class="reader-name">
                                                <?php echo htmlspecialchars($reader['HoTen']); ?>
                                                <span class="badge-count"><?php echo $reader['SoSachYeuCau']; ?> sách</span>
                                            </div>
                                            <div class="reader-meta">
                                                <i class="fas fa-id-card"></i>
                                                Thẻ: <?php echo $reader['SoThe'] ? htmlspecialchars($reader['SoThe']) : 'Chưa có'; ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>

                            <!-- CỘT PHẢI: CHI TIẾT -->
                            <div>
                                <?php if ($selectedReader): ?>
                                    <?php
                                    $sqlReaderInfo = "SELECT doc.*, ttv.SoThe, ttv.NgayHetHan, ttv.TrangThai as TrangThaiThe
                                                      FROM DocGia doc
                                                      LEFT JOIN TheThuVien ttv ON doc.MaDocGia = ttv.MaDocGia
                                                      WHERE doc.MaDocGia = $selectedReader";
                                    $readerInfo = $conn->query($sqlReaderInfo)->fetch_assoc();

                                    $sqlBooks = "SELECT yc.MaYeuCau, yc.NgayYeuCau, yc.MaBanSach,
                                                        ds.TenSach, bs.TinhTrang as TinhTrangSach,
                                                        GROUP_CONCAT(DISTINCT tg.TenTacGia SEPARATOR ', ') AS TacGia
                                                 FROM yeucaumuon yc
                                                 JOIN BanSach bs ON yc.MaBanSach = bs.MaBanSach
                                                 JOIN DauSach ds ON bs.MaDauSach = ds.MaDauSach
                                                 LEFT JOIN TacGia_Sach tgs ON ds.MaDauSach = tgs.MaDauSach
                                                 LEFT JOIN TacGia tg ON tgs.MaTacGia = tg.MaTacGia
                                                 WHERE yc.MaDocGia = $selectedReader AND yc.TrangThai = 'ChoDuyet'
                                                 GROUP BY yc.MaYeuCau
                                                 ORDER BY yc.NgayYeuCau ASC";
                                    $resBooks = $conn->query($sqlBooks);
                                    ?>

                                    <div class="info-card">
                                        <h3><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($readerInfo['HoTen']); ?></h3>
                                        <div class="info-row">
                                            <span>Tên đăng nhập:</span>
                                            <span><?php echo htmlspecialchars($readerInfo['TenDangNhap']); ?></span>
                                        </div>
                                        <div class="info-row">
                                            <span>Số thẻ:</span>
                                            <span><?php echo $readerInfo['SoThe'] ? htmlspecialchars($readerInfo['SoThe']) : '<i style="color:#dc3545;">Chưa có thẻ</i>'; ?></span>
                                        </div>
                                        <?php if ($readerInfo['SoThe']): ?>
                                            <div class="info-row">
                                                <span>Trạng thái thẻ:</span>
                                                <span style="color:<?php echo $readerInfo['TrangThaiThe'] == 1 ? 'green' : 'red'; ?>">
                                                    <?php echo $readerInfo['TrangThaiThe'] == 1 ? 'Hoạt động' : 'Bị khóa'; ?>
                                                </span>
                                            </div>
                                            <div class="info-row">
                                                <span>Ngày hết hạn:</span>
                                                <span><?php echo date('d/m/Y', strtotime($readerInfo['NgayHetHan'])); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <h3 class="section-title"><i class="fas fa-book"></i> Sách Yêu Cầu Mượn</h3>
                                    <div style="max-height: 500px; overflow-y: auto;">
                                        <?php while ($book = $resBooks->fetch_assoc()): ?>
                                            <div class="info-card">
                                                <div style="font-size: 1.05em; font-weight: bold; color: #333; margin-bottom: 10px;">
                                                    <?php echo htmlspecialchars($book['TenSach']); ?>
                                                </div>
                                                <div class="info-row">
                                                    <span>Tác giả:</span>
                                                    <span><?php echo $book['TacGia'] ? htmlspecialchars($book['TacGia']) : '<i>Không có</i>'; ?></span>
                                                </div>
                                                <div class="info-row">
                                                    <span>Mã vạch:</span>
                                                    <span class="mono-badge"><?php echo htmlspecialchars($book['MaBanSach']); ?></span>
                                                </div>
                                                <div class="info-row">
                                                    <span>Ngày yêu cầu:</span>
                                                    <span><?php echo date('d/m/Y H:i', strtotime($book['NgayYeuCau'])); ?></span>
                                                </div>
                                                <div class="info-row">
                                                    <span>Tình trạng sách:</span>
                                                    <span style="color:<?php echo $book['TinhTrangSach'] == 1 ? 'green' : 'red'; ?>">
                                                        <?php echo $book['TinhTrangSach'] == 1 ? 'Sẵn sàng' : 'Không sẵn sàng'; ?>
                                                    </span>
                                                </div>
                                                <div class="book-actions">
                                                    <form method="POST" action="?tab=yeu_cau&reader=<?php echo $selectedReader; ?>" style="display:inline;">
                                                        <input type="hidden" name="ma_yeu_cau" value="<?php echo $book['MaYeuCau']; ?>">
                                                        <button type="submit" name="btn_duyet" class="btn-action btn-approve"
                                                            onclick="return confirm('Bạn có chắc chắn duyệt yêu cầu này?');">
                                                            <i class="fas fa-check"></i> Duyệt
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="?tab=yeu_cau&reader=<?php echo $selectedReader; ?>" style="display:inline;">
                                                        <input type="hidden" name="ma_yeu_cau" value="<?php echo $book['MaYeuCau']; ?>">
                                                        <button type="submit" name="btn_tu_choi" class="btn-action btn-reject"
                                                            onclick="return confirm('Từ chối yêu cầu này?');">
                                                            <i class="fas fa-times"></i> Hủy
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>

                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-hand-pointer"></i>
                                        <p>Chọn một độc giả bên trái để xem chi tiết.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle" style="color: var(--success-color);"></i>
                            <p>Hiện không có yêu cầu nào đang chờ duyệt.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- end content-body -->
    </div><!-- end main-content -->

    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>