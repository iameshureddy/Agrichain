<?php
session_start();
require '../includes/db.php'; // DB connection

$email = trim($_POST['email']);
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT id, password FROM admins WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();

    if (password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['LAST_ACTIVITY'] = time();
        header("Location: dashboard.php");
        exit;
    }
}

header("Location: login.php?error=1");
exit;
