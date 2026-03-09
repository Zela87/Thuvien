<?php
require_once '../config/db.php';
session_start();
$conn->set_charset("utf8mb4");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra Cứu Sách - LIB MANAGE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/css-index.css">
    <!-- <style>
        :root {
            --primary-color: #4e73df;
            --bg-color: #f8f9fc;
            --text-color: #333;
            --border-color: #d1d3e2;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            padding: 40px 20px;
        }

        .container { max-width: 1200px; margin: 0 auto; }

        .btn-back {
            display: inline-block;
            text-decoration: none;
            color: #858796;
            font-weight: 600;
            margin-bottom: 20px;
            transition: 0.3s;
        }
        .btn-back:hover { color: var(--primary-color); }

        /* Card Tìm kiếm */
        .search-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 30px;
            border-top: 4px solid var(--primary-color);
        }

        .search-box { display: flex; gap: 10px; flex-wrap: wrap; }
        .search-box input, .search-box select {
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            flex: 1;
            min-width: 200px;
        }
        .search-box button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        /* Grid Sách */
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 25px;
        }

        .book-item {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            border: 1px solid #eee;
            display: flex;
            flex-direction: column;
        }

        .book-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .book-image {
            width: 100%;
            height: 280px;
            background-color: #eaecf4;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .book-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .book-image i {
            font-size: 50px;
            color: #b7b9cc;
        }

        .book-info {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .book-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 8px;
            line-height: 1.4;
            height: 2.8em;
            overflow: hidden;
        }

        .book-meta {
            font-size: 0.85rem;
            color: #6e707e;
            margin-bottom: 5px;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 10px;
        }

        .status-on { background: #e5f9f0; color: var(--success-color); }
        .status-off { background: #fdf2f2; color: var(--danger-color); }

        .empty-result {
            text-align: center;
            grid-column: 1 / -1;
            padding: 50px;
            color: #858796;
        }
    </style> -->
</head>
<body>

<div class="content-body">
            <div class="search-card">
                <form method="GET" action="index.php" class="search-box">
                    <input type="text" name="keyword" placeholder="Nhập tên sách, tác giả..." value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
                    <select name="criteria">
                        <option value="TenSach" <?php if(isset($_GET['criteria']) && $_GET['criteria']=='TenSach') echo 'selected'; ?>>Tên Sách</option>
                        <option value="TacGia" <?php if(isset($_GET['criteria']) && $_GET['criteria']=='TacGia') echo 'selected'; ?>>Tác Giả</option>
                        <option value="TenTheLoai" <?php if(isset($_GET['criteria']) && $_GET['criteria']=='TenTheLoai') echo 'selected'; ?>>Thể Loại</option>
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
                    while($row = $result->fetch_assoc()) {
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

</body>
</html>