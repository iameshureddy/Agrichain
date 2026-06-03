<?php
require_once __DIR__ . '/../includes/auth_farmer.php';
require_once __DIR__ . '/../includes/db.php';

$id = $_POST['product_id'];
$conn->query("DELETE FROM products WHERE id=$id");

header("Location: dashboard.php");
