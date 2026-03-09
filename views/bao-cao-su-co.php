<?php
include_once __DIR__ . '/../actions/auth-bao-cao-su-co.php';
require_once '../config/db.php';
$conn->set_charset("utf8mb4");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hỗ Trợ & Phản Hồi - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="../assets/css/style-main.css">
    <link rel="stylesheet" href="../assets/css/style-bao-cao.css">
</head>

<body>

    <?php include 'Index-sidebar.php' ?>

    <div class="main-content">
        <?php include 'Index-topbar.php' ?>

        <div class="content-body">
            <div class="report-container">
                
                <?php if ($role == 'DocGia'): ?>
                <div class="report-card">
                    <h3><i class="fas fa-paper-plane"></i> Gửi yêu cầu hỗ trợ</h3>
                    <form method="post">
                        <div style="display: flex; gap: 15px; margin-bottom: 15px; flex-wrap: wrap;">
                            <select name="loai_su_co" required style="flex: 1; min-width: 200px; padding: 12px; border: 1px solid #d1d3e2; border-radius: 5px;">
                                <option value="" disabled selected>-- Chọn loại vấn đề --</option>
                                <option value="Vấn đề về thẻ">Thẻ thư viện (Mất, Hỏng...)</option>
                                <option value="Hết sách">Sách (Hết sách, Yêu cầu nhập)</option>
                                <option value="Web lỗi">Lỗi Website</option>
                                <option value="Khác">Khác</option>
                            </select>
                            <input type="text" name="tieude" placeholder="Tiêu đề yêu cầu..." required style="flex: 2; min-width: 300px; padding: 12px; border: 1px solid #d1d3e2; border-radius: 5px;">
                        </div>
                        <textarea name="noidung" placeholder="Vui lòng mô tả chi tiết sự cố bạn đang gặp phải..." required style="width: 100%; height: 120px; padding: 12px; border: 1px solid #d1d3e2; border-radius: 5px; margin-bottom: 15px; resize: vertical;"></textarea>
                        <button type="submit" name="send_report" style="background: var(--primary-color); color: white; padding: 12px 30px; border: none; cursor: pointer; border-radius: 5px; font-weight: bold; transition: 0.3s;">
                            <i class="fas fa-paper-plane"></i> GỬI YÊU CẦU
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <div class="report-card">
                    <h3><i class="fas fa-list-alt"></i> Danh sách phản hồi</h3>
                    
                    <?php
                    $sql = "SELECT bc.*, the.SoThe FROM BaoCaoSuCo bc 
                            LEFT JOIN DocGia dg ON bc.NguoiBaoCao = dg.TenDangNhap 
                            LEFT JOIN TheThuVien the ON dg.MaDocGia = the.MaDocGia ";
                    
                    if ($role == 'QuanTriVien') { 
                        $sql .= " WHERE bc.LoaiSuCo IN ('Web lỗi', 'Khác')"; 
                    } elseif ($role == 'ThuThu') { 
                        $sql .= " WHERE bc.LoaiSuCo IN ('Vấn đề về thẻ', 'Hết sách', 'Khác')"; 
                    } else { 
                        $sql .= " WHERE bc.NguoiBaoCao = '$u'"; 
                    }
                    
                    $sql .= " ORDER BY bc.NgayBaoCao DESC";
                    $res = $conn->query($sql);

                    if ($res && $res->num_rows > 0) {
                        while ($row = $res->fetch_assoc()) {
                            $sttClass = $row['TrangThai'] == 'Chờ xử lý' ? 'status-new' : 'status-done';
                            
                            $badgeColor = 'bg-khac';
                            if($row['LoaiSuCo'] == 'Vấn đề về thẻ') $badgeColor = 'bg-the';
                            if($row['LoaiSuCo'] == 'Hết sách') $badgeColor = 'bg-sach';
                            if($row['LoaiSuCo'] == 'Web lỗi') $badgeColor = 'bg-web';
                            
                            $showCard = !empty($row['SoThe']) ? "<span class='sothe-badge'><i class='fas fa-id-card'></i> {$row['SoThe']}</span>" : "";
                    ?>

                    <div class="msg-box">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <div>
                                <span class="tag-type <?php echo $badgeColor; ?>"><?php echo $row['LoaiSuCo']; ?></span>
                                <strong style="font-size: 1.1rem; color: #2e59d9;"><?php echo htmlspecialchars($row['TieuDe']); ?></strong>
                                <div style="margin-top: 5px; font-size: 0.85rem; color: #858796;">
                                    <i class="fas fa-user"></i> <?php echo $row['NguoiBaoCao']; ?> <?php echo $showCard; ?>
                                    <span style="margin-left: 15px;"><i class="far fa-clock"></i> <?php echo $row['NgayBaoCao']; ?></span>
                                </div>
                            </div>
                            <span class="<?php echo $sttClass; ?>"><?php echo $row['TrangThai']; ?></span>
                        </div>
                        
                        <div style="background: white; padding: 15px; border-radius: 5px; border: 1px solid #eaecf4; color: #4e73df;">
                            <?php echo nl2br(htmlspecialchars($row['NoiDung'])); ?>
                        </div>

                        <?php if (!empty($row['PhanHoi'])): ?>
                            <div class="msg-reply">
                                <b><i class="fas fa-user-shield"></i> PHẢN HỒI TỪ THƯ VIỆN:</b>
                                <p style="margin-top: 5px; color: #5a5c69;"><?php echo nl2br(htmlspecialchars($row['PhanHoi'])); ?></p>
                            </div>
                        <?php elseif ($role == 'QuanTriVien' || $role == 'ThuThu'): ?>
                            <form method="post" style="margin-top: 15px; display: flex; gap: 10px;">
                                <input type="hidden" name="id_baocao" value="<?php echo $row['MaBaoCao']; ?>">
                                <input type="text" name="phanhoi" placeholder="Nhập nội dung phản hồi..." required style="flex: 1; padding: 10px; border: 1px solid #d1d3e2; border-radius: 5px;">
                                <button type="submit" name="reply_report" style="background: #1cc88a; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                                    GỬI PHẢN HỒI
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <?php
                        }
                    } else {
                        echo "<div style='text-align: center; padding: 40px; color: #b7b9cc;'>
                                <i class='fas fa-info-circle' style='font-size: 2rem; margin-bottom: 10px;'></i>
                                <p>Hiện chưa có yêu cầu hỗ trợ nào.</p>
                              </div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

</body>
</html>