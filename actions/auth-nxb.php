<?php
session_start();
require_once __DIR__ . '/../config/db.php';
$conn->set_charset("utf8mb4");

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'QuanTriVien' && $_SESSION['role'] != 'ThuThu')) {
    header("Location: Index.php");
    exit();
}
$u = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$ho_ten = isset($_SESSION['ho_ten']) ? $_SESSION['ho_ten'] : 'Người dùng';
$user_role = $role;
// XỬ LÝ LƯU (THÊM/SỬA)
if (isset($_POST['save_nxb'])) {
    $ten = $conn->real_escape_string($_POST['ten_nxb']);
    $dia_chi = $conn->real_escape_string($_POST['dia_chi']);
    $ma = $_POST['ma_nxb'];

    if (empty($ma)) {
        $sql = "INSERT INTO nhaxuatban (TenNXB, DiaChi) VALUES ('$ten', '$dia_chi')";
        $action_msg = "Thêm mới";
    } else {
        $sql = "UPDATE nhaxuatban SET TenNXB = '$ten', DiaChi = '$dia_chi' WHERE MaNXB = $ma";
        $action_msg = "Cập nhật";
    }

    if ($conn->query($sql)) {
        $_SESSION['msg'] = "success|$action_msg nhà xuất bản thành công!";
    } else {
        $_SESSION['msg'] = "error|Lỗi: " . $conn->error;
    }
    header("Location: ../pages/quan-ly-nha-xuat-ban.php"); exit();
}

// XỬ LÝ XÓA
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $check = $conn->query("SELECT COUNT(*) as count FROM dausach WHERE MaNXB = $id")->fetch_assoc();
    
    if ($check['count'] > 0) {
        $_SESSION['msg'] = "error|Không thể xóa! NXB này đang liên kết với " . $check['count'] . " đầu sách.";
    } else {
        $conn->query("DELETE FROM nhaxuatban WHERE MaNXB = $id");
        $_SESSION['msg'] = "success|Đã xóa nhà xuất bản thành công!";
    }
    header("Location: ../pages/quan-ly-nha-xuat-ban.php"); exit();
}
?>