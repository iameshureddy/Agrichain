<?php
require_once("../includes/auth_farmer.php");
require_once("../includes/db.php");

$wallet = $_SESSION['wallet_address'];

$name = $_POST['name'];
$price_inr = $_POST['price_inr'];
$quantity = $_POST['quantity'];
$unit = $_POST['unit'];

/* ETH conversion (example static rate) */
$ethRate = 250000; // ₹ per ETH
$priceWei = ($price_inr / $ethRate) * 1e18;

/* Image upload */
$imgName = time()."_".$_FILES['image']['name'];
move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/".$imgName);

$stmt = $conn->prepare("
 INSERT INTO products
 (name,image,quantity,unit,price_inr,price_wei,farmer_address)
 VALUES (?,?,?,?,?,?,?)
");
$stmt->bind_param(
 "ssdsdis",
 $name,$imgName,$quantity,$unit,$price_inr,$priceWei,$wallet
);

$stmt->execute();

header("Location: dashboard.php");
