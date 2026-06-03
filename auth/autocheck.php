<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /agrichain/auth/login.php");
    exit;
}

if ($_SESSION['role'] !== 'farmer') {
    die("Access Denied");
}
?>
