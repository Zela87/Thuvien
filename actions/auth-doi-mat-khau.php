<?php
session_start();
require_once __DIR__ . '/../config/db.php';
$conn->set_charset("utf8mb4");

if (!isset($_SESSION['username'])) { 
    header("Location: Index.php"); 
    exit(); 
}

$u = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$ho_ten = isset($_SESSION['ho_ten']) ? $_SESSION['ho_ten'] : 'Người dùng';
$user_role = $role;

$msg = "";

if (isset($_POST['change_pass'])) {
    $old = $_POST['old_pass'];
    $new = $_POST['new_pass'];
    $confirm = $_POST['confirm_pass'];

    // Sử dụng Prepared Statement để bảo mật hơn
    $stmt = $conn->prepare("SELECT MatKhau FROM TaiKhoan WHERE TenDangNhap=? AND MatKhau=?");
    $stmt->bind_param("ss", $u, $old);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $_SESSION['msg_type'] = "error";
        $_SESSION['msg_text'] = "Mật khẩu cũ không chính xác!";
    } elseif ($new != $confirm) {
        $_SESSION['msg_type'] = "error";
        $_SESSION['msg_text'] = "Mật khẩu xác nhận không khớp!";
    } else {
        $update = $conn->prepare("UPDATE TaiKhoan SET MatKhau=? WHERE TenDangNhap=?");
        $update->bind_param("ss", $new, $u);
        $update->execute();
        $_SESSION['msg_type'] = "success";
        $_SESSION['msg_text'] = "Đổi mật khẩu thành công!";
    }
    header("Location: ../pages/doi-mat-khau.php"); 
    exit();
}

if (isset($_SESSION['msg_text'])) {
    $msg_type = $_SESSION['msg_type'];
    $msg_text = $_SESSION['msg_text'];
    $msg = "<div class='alert $msg_type'>$msg_text</div>";
    unset($_SESSION['msg_type'], $_SESSION['msg_text']);
}
?>