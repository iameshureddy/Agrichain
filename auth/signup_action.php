<?php
session_start();
require "../includes/db.php";

function generateLoginId($role, $conn) {
    $year = date("Y");

    $prefix = match ($role) {
        'farmer' => 'FARM',
        'consumer' => 'CONS',
        'admin' => 'ADMIN'
    };

    $res = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='$role'");
    $row = $res->fetch_assoc();

    $number = str_pad($row['total'] + 1, 4, "0", STR_PAD_LEFT);
    return "$prefix-$year-$number";
}

$name = trim($_POST['name']);
$role = $_POST['role'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$login_id = generateLoginId($role, $conn);

$stmt = $conn->prepare(
  "INSERT INTO users (login_id, name, role, password)
   VALUES (?, ?, ?, ?)"
);

$stmt->bind_param("ssss", $login_id, $name, $role, $password);
$stmt->execute();

/* STORE LOGIN ID FOR NOTIFICATION */
$_SESSION['new_login_id'] = $login_id;

/* REDIRECT BACK */
header("Location: signup.php");
exit;
