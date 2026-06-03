<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: /agrichain/auth/login.php");
    exit;
}

switch ($_SESSION['role']) {
    case 'farmer':
        header("Location: /agrichain/farmer/dashboard.php");
        break;

    case 'consumer':
        header("Location: /agrichain/consumer/dashboard.php");
        break;

    case 'admin':
        header("Location: /agrichain/admin/dashboard.php");
        break;

    default:
        header("Location: /agrichain/auth/login.php");
}

exit;
