<?php
include_once __DIR__ . '/../actions/auth-thanh-ly-sach.php';
require_once '../config/db.php';

$msg_session = $_SESSION['msg'] ?? "";
unset($_SESSION['msg']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh Lý Sách - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style-main.css">
    <style>
        .discard-card {
            background: white; padding: 25px; border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            border-top: 4px solid var(--primary-color);
        }
        .table-discard { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table-discard th { background: #f8f9fc; color: #4e73df; padding: 12px; text-align: left; border-bottom: 2px solid #e3e6f0; }
        .table-discard td { padding: 12px; border-bottom: 1px solid #e3e6f0; font-size: 0.9rem; }
        
        .status-tag { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; }
        .tag-available { background: #e5f9f0; color: #1cc88a; }
        .tag-damaged { background: #fff3cd; color: #856404; }

        /* Modal Style */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 10% auto; padding: 25px; border-radius: 10px; width: 400px; }
    </style>
</head>
<body>

    <?php include 'Index-sidebar.php' ?>

    <div class="main-content">
        <?php include 'Index-topbar.php' ?>

        <div class="content-body" style="padding: 20px;">
            
            <?php if($msg_session): 
                $m = explode('|', $msg_session); ?>
                <div style="padding:15px; border-radius:5px; margin-bottom:20px; background:<?php echo $m[0]=='success'?'#e5f9f0':'#fdf2f2'; ?>; color:<?php echo $m[0]=='success'?'#1cc88a':'#e74a3b'; ?>;">
                    <i class="fas fa-info-circle"></i> <?php echo $m[1]; ?>
                </div>
            <?php endif; ?>

            <div class="discard-card">
                <h3 style="color: var(--primary-color);"><i class="fas fa-trash-alt"></i> DANH SÁCH SÁCH CẦN THANH LÝ</h3>
                <p style="font-size: 0.9rem; color: #858796; margin-bottom: 20px;">Lưu ý: Chỉ thanh lý những sách đã quá cũ, hư hỏng nặng hoặc không còn giá trị sử dụng.</p>

                <table class="table-discard">
                    <thead>
                        <tr>
                            <th>Mã Bản Sách</th>
                            <th>Tên Đầu Sách</th>
                            <th>Tình Trạng</th>
                            <th style="text-align:center;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Chỉ lấy sách Sẵn sàng (1) hoặc Hư hỏng (2) để thanh lý. Không lấy sách đang mượn (0) hoặc đã thanh lý (3).
                        $sql = "SELECT bs.*, ds.TenSach FROM BanSach bs 
                                JOIN DauSach ds ON bs.MaDauSach = ds.MaDauSach 
                                WHERE bs.TinhTrang IN (1, 2) 
                                ORDER BY bs.TinhTrang DESC";
                        $res = $conn->query($sql);
                        while($row = $res->fetch_assoc()):
                            $tagClass = $row['TinhTrang'] == 2 ? 'tag-damaged' : 'tag-available';
                            $tagText = $row['TinhTrang'] == 2 ? 'Hư hỏng' : 'Sẵn sàng';
                        ?>
                        <tr>
                            <td><code><?php echo $row['MaBanSach']; ?></code></td>
                            <td><b><?php echo $row['TenSach']; ?></b></td>
                            <td><span class="status-tag <?php echo $tagClass; ?>"><?php echo $tagText; ?></span></td>
                            
                            <td style="text-align:center;">
                                <button onclick="openModal('<?php echo $row['MaBanSach']; ?>', '<?php echo addslashes($row['TenSach']); ?>')" 
                                        style="background:var(--danger-color); color:white; border:none; padding:6px 12px; border-radius:4px; cursor:pointer;">
                                    Thanh lý
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="lydoModal" class="modal">
        <div class="modal-content">
            <h4 style="color:var(--danger-color); margin-bottom:15px;">Xác nhận thanh lý</h4>
            <div style="background:#f8f9fc; padding:10px; border-radius:5px; margin-bottom:15px; font-size:0.85rem;">
                Sách: <b id="tenSachModal"></b><br>
                Mã: <code id="maSachModal"></code>
            </div>
            <form method="POST">
                <input type="hidden" name="maBanSach" id="inputMaSach">
                <label style="font-size:0.9rem; font-weight:bold;">Lý do thanh lý:</label>
                <textarea name="lydo" rows="3" style="width:100%; margin:10px 0; padding:10px; border-radius:5px; border:1px solid #ddd;" placeholder="VD: Sách rách nát, cũ nát..." required></textarea>
                <div style="display:flex; gap:10px; margin-top:10px;">
                    <button type="submit" name="confirm_thanhly" style="flex:2; background:var(--danger-color); color:white; border:none; padding:10px; border-radius:5px; cursor:pointer; font-weight:bold;">XÁC NHẬN</button>
                    <button type="button" onclick="closeModal()" style="flex:1; background:#858796; color:white; border:none; padding:10px; border-radius:5px; cursor:pointer;">HỦY</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(ma, ten) {
            document.getElementById('lydoModal').style.display = "block";
            document.getElementById('maSachModal').innerText = ma;
            document.getElementById('tenSachModal').innerText = ten;
            document.getElementById('inputMaSach').value = ma;
        }
        function closeModal() {
            document.getElementById('lydoModal').style.display = "none";
        }
        window.onclick = function(event) {
            if (event.target == document.getElementById('lydoModal')) closeModal();
        }
    </script>
</body>
</html>