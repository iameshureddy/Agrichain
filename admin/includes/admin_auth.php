<?php
session_start();

/* Session timeout: 15 minutes */
$timeout = 900;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}

$_SESSION['LAST_ACTIVITY'] = time();

/* Check login */
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
