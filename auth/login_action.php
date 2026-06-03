<?php
session_start();
require "../includes/db.php";

$login_id = $_POST['login_id'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM users WHERE login_id=?");
$stmt->bind_param("s", $login_id);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['login_id'] = $user['login_id'];
    $_SESSION['role'] = $user['role'];

    if ($user['role'] === 'farmer') {
    header("Location: ../farmer/dashboard.php");
    exit;
} elseif ($user['role'] === 'consumer') {
    header("Location: ../consumer/dashboard.php");
    exit;
} else {
    header("Location: ../admin/dashboard.php");
    exit;
}

    exit;

} else {
    echo "Invalid Login ID or Password";
}
