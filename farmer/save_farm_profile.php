<?php
session_start();
require_once "../includes/db.php";

/* ================= AUTH ================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    echo "<script>alert('Unauthorized access'); window.location.href='../auth/login.php';</script>";
    exit;
}

$farmer_id = (int)$_SESSION['user_id'];

/* ================= FORM DATA ================= */
$farm_name  = trim($_POST['farm_name'] ?? '');
$owner_name = trim($_POST['owner_name'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$address    = trim($_POST['address'] ?? '');
$village    = trim($_POST['village'] ?? '');
$district   = trim($_POST['district'] ?? '');
$state      = trim($_POST['state'] ?? '');
$pincode    = trim($_POST['pincode'] ?? '');
$upi_id     = trim($_POST['upi_id'] ?? '');

/* ================= BASIC VALIDATION ================= */
if ($owner_name === '' || $phone === '') {
    echo "<script>alert('Required fields missing'); history.back();</script>";
    exit;
}

/* ================= AUTO WALLET GENERATION ================= */
/* Blockchain hidden from farmer */
$wallet_address = '0x' . substr(hash('sha256', $farmer_id . time()), 0, 40);

/* ================= CHECK EXISTING PROFILE ================= */
$check = $conn->prepare("SELECT id FROM farm_profiles WHERE farmer_id = ?");
$check->bind_param("i", $farmer_id);
$check->execute();
$exists = $check->get_result()->num_rows > 0;

if ($exists) {

    /* ================= UPDATE ================= */
    $stmt = $conn->prepare("
        UPDATE farm_profiles SET
            farm_name = ?,
            owner_name = ?,
            phone = ?,
            address = ?,
            village = ?,
            district = ?,
            state = ?,
            pincode = ?,
            upi_id = ?
        WHERE farmer_id = ?
    ");

    $stmt->bind_param(
        "sssssssssi",
        $farm_name,
        $owner_name,
        $phone,
        $address,
        $village,
        $district,
        $state,
        $pincode,
        $upi_id,
        $farmer_id
    );

} else {

    /* ================= INSERT ================= */
    $stmt = $conn->prepare("
        INSERT INTO farm_profiles
        (farmer_id, farm_name, owner_name, phone, address, village, district, state, pincode, upi_id, wallet_address, payment_enabled)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");

    $stmt->bind_param(
        "issssssssss",
        $farmer_id,
        $farm_name,
        $owner_name,
        $phone,
        $address,
        $village,
        $district,
        $state,
        $pincode,
        $upi_id,
        $wallet_address
    );
}

/* ================= EXECUTE ================= */
if ($stmt->execute()) {
    echo "
    <script>
        alert('✅ Farm profile saved successfully!');
        window.location.href = 'dashboard.php';
    </script>";
} else {
    echo "
    <script>
        alert('❌ Failed to save profile');
        history.back();
    </script>";
}

exit;
