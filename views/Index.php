<?php
include_once __DIR__ . '/../actions/auth-index.php';
require_once '../config/db.php';
$conn->set_charset("utf8mb4");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quản lý Thư viện - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="../assets/css/style-main.css">
    <link rel="stylesheet" href="../assets/css/style-index.css">

</head>

<body>

    <?php include 'Index-sidebar.php' ?>

    <div class="main-content">
        <?php include 'Index-topbar.php' ?>

        <div class="content-body">
            <div class="search-card">
                <form method="GET" action="index.php" class="search-box">
                    <input type="text" name="keyword" placeholder="Nhập tên sách, tác giả..." value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
                    <select name="criteria">
                        <option value="TenSach" <?php if (isset($_GET['criteria']) && $_GET['criteria'] == 'TenSach') echo 'selected'; ?>>Tên Sách</option>
                        <option value="TacGia" <?php if (isset($_GET['criteria']) && $_GET['criteria'] == 'TacGia') echo 'selected'; ?>>Tác Giả</option>
                        <option value="TenTheLoai" <?php if (isset($_GET['criteria']) && $_GET['criteria'] == 'TenTheLoai') echo 'selected'; ?>>Thể Loại</option>
                    </select>
                    <button type="submit"><i class="fas fa-search"></i> TÌM SÁCH</button>
                </form>
            </div>

            <div class="book-grid">
                <?php
                $where_clause = " WHERE 1=1 ";
                if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {
                    $key = $conn->real_escape_string($_GET['keyword']);
                    $cri = $conn->real_escape_string($_GET['criteria']);
                    if ($cri == 'TenSach') $where_clause .= " AND d.TenSach LIKE '%$key%'";
                    elseif ($cri == 'TacGia') $where_clause .= " AND t.TenTacGia LIKE '%$key%'";
                    elseif ($cri == 'TenTheLoai') $where_clause .= " AND tl.TenTheLoai LIKE '%$key%'";
                }

                $sql = "SELECT d.MaDauSach, d.TenSach, d.NamXuatBan, d.HinhAnh, tl.TenTheLoai, 
                        GROUP_CONCAT(t.TenTacGia SEPARATOR ', ') as TacGiaLienKet,
                        (SELECT COUNT(*) FROM bansach b WHERE b.MaDauSach = d.MaDauSach AND b.TinhTrang = 1) as SachCon
                        FROM dausach d
                        LEFT JOIN theloai tl ON d.MaTheLoai = tl.MaTheLoai
                        LEFT JOIN tacgia_sach ts ON d.MaDauSach = ts.MaDauSach
                        LEFT JOIN tacgia t ON ts.MaTacGia = t.MaTacGia
                        $where_clause
                        GROUP BY d.MaDauSach
                        ORDER BY d.MaDauSach DESC";

                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $count = $row['SachCon'];
                        $statusClass = $count > 0 ? 'status-on' : 'status-off';
                        $statusText = $count > 0 ? "Còn $count cuốn" : "Hết sách";
                        $imgPath = !empty($row['HinhAnh']) ? "../uploads/books/" . $row['HinhAnh'] : "";
                ?>

                        <div class="book-item">
                            <div class="book-image">
                                <?php if (!empty($row['HinhAnh']) && file_exists($imgPath)): ?>
                                    <img src="<?php echo $imgPath; ?>" alt="Bìa sách">
                                <?php else: ?>
                                    <i class="fas fa-book"></i>
                                <?php endif; ?>
                            </div>
                            <div class="book-info">
                                <div class="book-title"><?php echo htmlspecialchars($row['TenSach']); ?></div>
                                <div class="book-meta"><i class="fas fa-user-edit"></i> <?php echo htmlspecialchars($row['TacGiaLienKet'] ?: 'N/A'); ?></div>
                                <div class="book-meta"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($row['TenTheLoai']); ?></div>
                                <div style="margin-top: auto;">
                                    <span class="badge-status <?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                <?php
                    }
                } else {
                    echo "<div style='grid-column: 1/-1; text-align: center; padding: 50px; color: #ccc;'>
                            <i class='fas fa-search' style='font-size: 3rem; margin-bottom: 10px;'></i>
                            <p>Không tìm thấy sách nào phù hợp</p>
                          </div>";
                }
                ?>
            </div>
        </div>
    </div>

</body>

</html>