<?php
session_start();

if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'farmer') {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}
