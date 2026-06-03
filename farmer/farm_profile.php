<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    die("Unauthorized");
}

$farmer_id = (int)$_SESSION['user_id'];

$data = [
  'farm_name' => '',
  'owner_name' => '',
  'phone' => '',
  'address' => '',
  'village' => '',
  'district' => '',
  'state' => '',
  'pincode' => '',
  'latitude' => '',
  'longitude' => ''
];


/* SAVE PROFILE */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $upi = trim($_POST['upi_id']);

    // basic validation
    if (!preg_match('/^[a-zA-Z0-9.\-_]{2,}@[a-zA-Z]{2,}$/', $upi)) {
        $error = "Invalid UPI ID format";
    } else {

        $stmt = $conn->prepare("
          UPDATE farm_profiles
          SET upi_id = ?
          WHERE farmer_id = ?
        ");
        $stmt->bind_param("si", $upi, $farmer_id);
        $stmt->execute();

        $success = "UPI ID saved successfully";
    }
}

$res = $conn->query("SELECT * FROM farm_profiles WHERE farmer_id = $farmer_id");
if ($res && $res->num_rows > 0) {
    $data = $res->fetch_assoc();
}



?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Farm Profile</title>

<link rel="stylesheet" href="../assets/css/farm_profile.css">

<!-- Leaflet (CDNJS – reliable) -->
<link rel="stylesheet"
 href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
<script
 src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js">
</script>
</head>

<body>

<?php if (isset($_SESSION['farm_profile_success'])): ?>
<div id="successPopup" class="success-popup">
  <div class="success-box">
    <div class="success-icon">✔</div>
    <h3>Success</h3>
    <p><?= $_SESSION['farm_profile_success']; ?></p>
  </div>
</div>
<?php unset($_SESSION['farm_profile_success']); endif; ?>

<div class="farm-profile-container">

  <div class="farm-profile-header">
    <h1>🌾 Farm Profile</h1>
    <p>Manage your farm details & location</p>
    <a href="dashboard.php">← Back to Dashboard</a>
  </div>

  <form class="farm-profile-form" method="POST" action="save_farm_profile.php">

   

    <div>
      <label>Owner Name</label>
      <input name="owner_name" value="<?= htmlspecialchars($data['owner_name']) ?>">
    </div>

    <div>
      <label>Phone</label>
      <input name="phone" value="<?= htmlspecialchars($data['phone']) ?>">
    </div>

    <div>
      <label>Pincode</label>
      <input name="pincode" value="<?= htmlspecialchars($data['pincode']) ?>">
    </div>

    <div class="full">
      <label>Address</label>
      <textarea name="address"><?= htmlspecialchars($data['address']) ?></textarea>
    </div>

    <div>
      <label>Village</label>
      <input name="village" value="<?= htmlspecialchars($data['village']) ?>">
    </div>

    <div>
      <label>District</label>
      <input name="district" value="<?= htmlspecialchars($data['district']) ?>">
    </div>

    <div>
      <label>State</label>
      <input name="state" value="<?= htmlspecialchars($data['state']) ?>">
    </div>

    <div class="full">
      <label>Farm Location (Click on map)</label>
      <div id="map"></div>
    </div>

    <input type="hidden" id="latitude" name="latitude" value="<?= $data['latitude'] ?>">
    <input type="hidden" id="longitude" name="longitude" value="<?= $data['longitude'] ?>">
     
    
  <!-- PAYMENT SETTINGS -->
<div class="full" style="margin-top:20px;">
  <h3>💳 Payment Settings</h3>

  <label>UPI ID (for Online Payments)</label>
  <input
    type="text"
    name="upi_id"
    placeholder="example@upi"
    value="<?= htmlspecialchars($data['upi_id'] ?? '') ?>"
  >

  <?php if (isset($error)): ?>
    <p style="color:red;font-size:13px;"><?= $error ?></p>
  <?php endif; ?>

  <?php if (isset($success)): ?>
    <p style="color:green;font-size:13px;"><?= $success ?></p>
  <?php endif; ?>

  <p style="font-size:13px;color:#555;margin-top:6px;">
    ℹ️ This UPI will be shown to consumers during checkout.
  </p>
</div>

    <button class="save-btn">💾 Save Farm Profile</button>

  </form>
</div>

<script>
const lat = <?= $data['latitude'] ?: 20.5937 ?>;
const lng = <?= $data['longitude'] ?: 78.9629 ?>;

const map = L.map('map').setView([lat, lng], lat ? 15 : 5);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

let marker = lat ? L.marker([lat,lng]).addTo(map) : null;

map.on('click', e => {
  if (marker) marker.setLatLng(e.latlng);
  else marker = L.marker(e.latlng).addTo(map);

  document.getElementById('latitude').value = e.latlng.lat;
  document.getElementById('longitude').value = e.latlng.lng;
});


document.addEventListener("DOMContentLoaded", () => {
  const popup = document.getElementById("successPopup");
  if (popup) {
    setTimeout(() => {
      popup.style.opacity = "0";
      setTimeout(() => popup.remove(), 300);
    }, 3000); // closes after 3 seconds
  }
});



window.onload = () => {
  let data = localStorage.getItem("voiceData");
  if (data) {
    handleVoiceCommand(data);
    localStorage.removeItem("voiceData");
  }
};



function handleVoiceCommand(msg) {

  console.log("Auto Fill:", msg);

  msg = msg.toLowerCase();

  // NAME (independent)
  let name = msg.match(/name ([a-zA-Z ]+)/);
  if (name) {
    let input = document.querySelector("input[name='owner_name']");
    if (input) input.value = name[1].trim();
  }

  // PHONE
  let phone = msg.match(/phone (\d{6,})/);
  if (phone) {
    let input = document.querySelector("input[name='phone']");
    if (input) input.value = phone[1];
  }

  // PINCODE
  let pin = msg.match(/pincode (\d{6})/);
  if (pin) {
    let input = document.querySelector("input[name='pincode']");
    if (input) input.value = pin[1];
  }

  // ADDRESS
  let address = msg.match(/address ([a-zA-Z0-9 ,]+)/);
  if (address) {
    let input = document.querySelector("textarea[name='address']");
    if (input) input.value = address[1].trim();
  }

  // VILLAGE
  let village = msg.match(/village ([a-zA-Z ]+)/);
  if (village) {
    let input = document.querySelector("input[name='village']");
    if (input) input.value = village[1].trim();
  }

  // DISTRICT
  let district = msg.match(/district ([a-zA-Z ]+)/);
  if (district) {
    let input = document.querySelector("input[name='district']");
    if (input) input.value = district[1].trim();
  }

  // STATE
  let state = msg.match(/state ([a-zA-Z ]+)/);
  if (state) {
    let input = document.querySelector("input[name='state']");
    if (input) input.value = state[1].trim();
  }

  // SAVE
  if (msg.includes("save profile")) {
    document.querySelector("form")?.submit();
  }
}

</script>

</body>
</html>
