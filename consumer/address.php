<?php
session_start();
require_once "../includes/db.php";

/* ==========================
   AUTH CHECK
========================== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    header("Location: ../auth/login.php");
    exit;
}

$consumer_id = (int)$_SESSION['user_id'];
$success = false;
$error = "";

/* ==========================
   HANDLE FORM SUBMIT
========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name      = trim($_POST['name'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $house_no  = trim($_POST['house_no'] ?? '');
    $area      = trim($_POST['area'] ?? '');
    $landmark  = trim($_POST['landmark'] ?? '');
    $village   = trim($_POST['village'] ?? '');
    $district  = trim($_POST['district'] ?? '');
    $state     = trim($_POST['state'] ?? '');
    $pincode   = trim($_POST['pincode'] ?? '');
    $latitude  = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;

    if (
        !$name || !$phone || !$house_no || !$area ||
        !$village || !$district || !$state || !$pincode
    ) {
        $error = "❌ Please fill all required fields";
    } else {

        /* Remove old default */
        $conn->query("
            UPDATE consumer_addresses 
            SET is_default = 0 
            WHERE consumer_id = $consumer_id
        ");

        /* Insert new address */
        $stmt = $conn->prepare("
            INSERT INTO consumer_addresses
            (consumer_id, name, phone, house_no, area, landmark, village, district, state, pincode, latitude, longitude, is_default)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");

        $stmt->bind_param(
            "issssssssidd",
            $consumer_id,
            $name,
            $phone,
            $house_no,
            $area,
            $landmark,
            $village,
            $district,
            $state,
            $pincode,
            $latitude,
            $longitude
        );

        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = "❌ Failed to save address";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Address | AgriChain</title>

<link rel="stylesheet" href="../assets/css/address.css">

<link rel="stylesheet"
 href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
<script
 src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js">
</script>

<style>
/* TOAST */
.toast {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 14px 22px;
  border-radius: 12px;
  font-weight: 600;
  z-index: 9999;
  transition: all 0.3s ease;
}
.success-toast {
  background: #2e7d32;
  color: #fff;
}
.error-toast {
  background: #c62828;
  color: #fff;
}
</style>
</head>

<body>

<?php if ($success): ?>
<div class="toast success-toast" id="successToast">
  ✅ Address saved successfully!
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="toast error-toast"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="page-wrap">
  <div class="address-card">

    <h2>📍 Add Delivery Address</h2>
    <p class="subtitle">We deliver fresh produce straight to your doorstep</p>

    <form method="POST">

      <div class="grid-2">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="text" name="phone" placeholder="Phone Number" required>
      </div>

      <input type="text" name="house_no" placeholder="House / Flat No" required>
      <input type="text" name="area" placeholder="Street / Area" required>
      <input type="text" name="landmark" placeholder="Landmark (optional)">

      <div class="grid-2">
        <input type="text" name="village" placeholder="City / Village" required>
        <input type="text" name="district" placeholder="District" required>
      </div>

      <div class="grid-2">
        <input type="text" name="state" placeholder="State" required>
        <input type="text" name="pincode" placeholder="Pincode" required>
      </div>

      <!-- Hidden coordinates -->
      <input type="hidden" id="latitude" name="latitude">
      <input type="hidden" id="longitude" name="longitude">

      <label class="map-label">Select location on map</label>
      <div id="map" style="height:300px;border-radius:14px;"></div>

      <button type="submit" class="save-btn">Save Address</button>

    </form>

  </div>
</div>

<script>
/* MAP */
const map = L.map('map').setView([20.5937, 78.9629], 5);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

let marker = null;
map.on('click', e => {
  if (marker) {
    marker.setLatLng(e.latlng);
  } else {
    marker = L.marker(e.latlng).addTo(map);
  }
  document.getElementById('latitude').value = e.latlng.lat;
  document.getElementById('longitude').value = e.latlng.lng;
});

/* SUCCESS TOAST + REDIRECT */
document.addEventListener("DOMContentLoaded", () => {
  const toast = document.getElementById("successToast");
  if (toast) {
    setTimeout(() => {
      toast.style.opacity = "0";
      toast.style.transform = "translateX(40px)";
    }, 2500);

    setTimeout(() => {
      window.location.href = "checkout.php";
    }, 3000);
  }
});
</script>

</body>
</html>
