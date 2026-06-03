<?php
session_start();
require_once __DIR__ . "/../includes/db.php";


/* ===== AUTH ===== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    die("Unauthorized");
}
$farmer_id = (int)$_SESSION['user_id'];


/* ===== INITIALIZE VARIABLES (VERY IMPORTANT) ===== */
$totalProducts  = 0;
$activeProducts = 0;
$earnings       = 0.00;

/* ===== TOTAL PRODUCTS ===== */
$res = $conn->query(
    "SELECT COUNT(*) AS cnt FROM products WHERE farmer_id = $farmer_id"
);
if ($res && $row = $res->fetch_assoc()) {
    $totalProducts = (int)$row['cnt'];
}

/* ===== ACTIVE PRODUCTS ===== */
$res = $conn->query(
    "SELECT COUNT(*) AS cnt FROM products 
     WHERE farmer_id = $farmer_id AND status = 'active'"
);
if ($res && $row = $res->fetch_assoc()) {
    $activeProducts = (int)$row['cnt'];
}

/* ===== EARNINGS ===== */
$stmt = $conn->prepare("
    SELECT IFNULL(SUM(oi.price * oi.quantity), 0)
    FROM consumer_orders co
    JOIN order_items oi ON oi.order_id = co.id
    JOIN products p ON p.id = oi.product_id
    WHERE p.farmer_id = ?
    AND (
        (co.payment_method = 'COD' AND co.status = 'DELIVERED')
        OR
        (co.payment_method IN ('UPI','RAZORPAY') AND co.status = 'PLACED')
    )
");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$stmt->bind_result($earnings);
$stmt->fetch();
$stmt->close();


/* ================= ADD PRODUCT ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {

    $name = trim($_POST['name']);
    $category = $_POST['category'] === 'custom'
        ? trim($_POST['custom_category'])
        : $_POST['category'];

    $price = (float)$_POST['price'];
    $quantity = (int)$_POST['quantity'];
    $unit = $_POST['unit'] ?? '';

if ($unit === '') {
    die("Please select a unit");
}

    $available_from = $_POST['available_from'];
    $expiry_date = $_POST['expiry_date'];
    $description = trim($_POST['description']);

    /* AUTO STATUS */
    $today = date('Y-m-d');
    if ($today < $available_from) {
        $status = 'upcoming';
    } elseif ($today <= $expiry_date) {
        $status = 'active';
    } else {
        $status = 'expired';
    }

    /* IMAGE UPLOAD */
    $image = null;

if (!empty($_FILES['image']['name'])) {

    $uploadDir = __DIR__ . "/../uploads/products/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $imageName = time() . "_" . basename($_FILES['image']['name']);
    $targetPath = $uploadDir . $imageName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $image = $imageName;
    } else {
        $image = null;
    }
}


    /* SILENT BLOCKCHAIN TX (SIMULATED HASH) */
    $blockchain_tx = "0x" . bin2hex(random_bytes(16));

    /* INSERT */
    $stmt = $conn->prepare("
        INSERT INTO products
        (farmer_id, name, category, price_inr, quantity, unit,
         available_from, expiry_date, status,
         image, description, blockchain_tx, active)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1)
    ");

    $stmt->bind_param(
        "issdisssssss",
        $farmer_id,
        $name,
        $category,
        $price,
        $quantity,
        $unit,
        $available_from,
        $expiry_date,
        $status,
        $image,
        $description,
        $blockchain_tx
    );

    $stmt->execute();
    $_SESSION['product_success'] = 
"🎉 Product Added Successfully!<br>
Your product is now live and visible to consumers.<br>
All future sales will be securely recorded and verifiable.";


    header("Location: dashboard.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Farmer Dashboard | AgriChain</title>

<link rel="stylesheet" href="../assets/css/farmer.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* ===== MODAL & CATEGORY UI ===== */
.modal {
  display:none;
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.6);
  justify-content:center;
  align-items:center;
  z-index:999;
}
.modal-box {
  background:#fff;
  width:420px;
  padding:25px;
  border-radius:18px;
  animation:pop .3s ease;
}
@keyframes pop {
  from {transform:scale(.9);opacity:0}
  to {transform:scale(1);opacity:1}
}
.category-grid {
  display:grid;
  grid-template-columns:repeat(3,1fr);
  gap:10px;
  margin-bottom:15px;
}
.cat-card {
  padding:12px;
  border-radius:14px;
  background:#f3f3f3;
  text-align:center;
  cursor:pointer;
  transition:.3s;
}
.cat-card:hover {transform:translateY(-3px)}
.cat-card.active {
  background:#2e7d32;
  color:#fff;
}
.modal-box input,
.modal-box textarea {
  width:100%;
  padding:10px;
  margin-bottom:10px;
}
</style>
</head>

<body class="dashboard-page">


<!-- ============ SIDEBAR ============ -->
<aside class="sidebar">
  <h2>🌾 AgriChain</h2>

  <a class="active" href="dashboard.php">
    <i class="fa fa-chart-line"></i>
    <span>Dashboard</span>
  </a>

  <a href="javascript:void(0)" onclick="openAddProduct()">
    <i class="fa fa-plus"></i>
    <span>Add Product</span>
  </a>

  <a href="my_products.php">
    <i class="fa fa-box"></i>
    <span>My Products</span>
  </a>

  <a href="orders.php">
    <i class="fa fa-receipt"></i>
    <span>Orders</span>
  </a>

  <!-- ⭐ NEW: Reviews -->
  <a href="reviews.php">
    <i class="fa fa-star"></i>
    <span>Customer Reviews</span>
  </a>

  <a href="earnings.php">
    <i class="fa fa-wallet"></i>
    <span>Earnings</span>
  </a>

  <a href="farm_profile.php">
    <i class="fa fa-location-dot"></i>
    <span>Farm Profile</span>
  </a>

  <a class="logout" href="../auth/logout.php">
    <i class="fa fa-sign-out-alt"></i>
    <span>Logout</span>
  </a>
</aside>



<!-- ============ MAIN ============ -->
 <!-- TELEGRAM CONNECT CARD -->
<div style="
  background:#eef7ff;
  padding:16px;
  border-radius:14px;
  margin:20px 0;
  display:flex;
  align-items:center;
  justify-content:space-between;
">
  <div>
    <h3 style="margin:0">📲 Telegram Notifications</h3>
    <p style="margin:4px 0;color:#555">
      Get order updates directly on Telegram
    </p>
  </div>

  <div>
    <a href="https://t.me/agrichain_notify_bot"
       target="_blank"
       style="
         padding:8px 14px;
         background:#229ED9;
         color:#fff;
         border-radius:10px;
         text-decoration:none;
         margin-right:8px;
       ">
       Open Bot
    </a>

    <a href="/agrichain/connect_telegram.php"
       style="
         padding:8px 14px;
         background:#2e7d32;
         color:#fff;
         border-radius:10px;
         text-decoration:none;
       ">
       ✅ Connect Telegram
    </a>
  </div>
</div>

<main class="main">

<?php if (isset($_SESSION['product_success'])): ?>
  <div class="toast success">
    <i class="fa fa-check-circle"></i>
    <?= $_SESSION['product_success']; ?>
  </div>
<?php unset($_SESSION['product_success']); endif; ?>

<section id="ProductsSection">

<header>
  <h1>Welcome Farmer 👋</h1>
  <p>Manage your products and sales easily</p>
</header>

<!-- STATS -->
<section class="stats">
  <div class="card green">
    <i class="fa fa-seedling"></i>
    <h3>Total Products</h3>
    <p><?= $totalProducts ?></p>
  </div>

  <div class="card blue">
    <i class="fa fa-check-circle"></i>
    <h3>Active Products</h3>
    <p><?= $activeProducts ?></p>
  </div>

  <div class="card gold">
    <i class="fa fa-rupee-sign"></i>
    <h3>Earnings</h3>
    <p>₹<?= number_format($earnings,2) ?></p>
  </div>

  <div class="card purple">
    <i class="fa fa-link"></i>
    <h3>Status</h3>
    <p>Live</p>
  </div>
</section>
</section>
</main>



<div class="modal" id="addProductModal">
  <div class="modal-box">

    <h2>Add Product</h2>

    <!-- CATEGORY SELECTION -->
    <div class="category-grid">
      <div class="cat-card" onclick="selectCategory(this,'farm')">🌾<span>Farm</span></div>
      <div class="cat-card" onclick="selectCategory(this,'poultry')">🐔<span>Poultry</span></div>
      <div class="cat-card" onclick="selectCategory(this,'dairy')">🥛<span>Dairy</span></div>
      <div class="cat-card" onclick="selectCategory(this,'handmade')">🧺<span>Handmade</span></div>
      <div class="cat-card" onclick="selectCategory(this,'organic')">🍃<span>Organic</span></div>
      <div class="cat-card" onclick="selectCategory(this,'custom')">⭐<span>Custom</span></div>
    </div>

    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="category" id="category">

      <!-- CUSTOM CATEGORY -->
      <input
        type="text"
        name="custom_category"
        id="customBox"
        placeholder="Custom category"
        style="display:none"
      >

      <!-- IMAGE -->
      <input type="file" name="image" required>

      <!-- PRODUCT NAME -->
      <input type="text" name="name" placeholder="Product Name" required>

      <!-- PRICE -->
      <div class="row">
        <input type="number" name="price" placeholder="Price (₹)" required>

        <select name="unit" required>
  <option value="">Select Unit</option>
  <option value="kg">Kg</option>
  <option value="gram">Gram</option>
  <option value="litre">Litre</option>
  <option value="ml">Millilitre</option>
  <option value="bag">Bag</option>
  <option value="dozen">Dozen</option>
  <option value="piece">Piece</option>
  <option value="bundle">Bundle</option> 
</select>

      </div>

      <!-- AVAILABLE QUANTITY -->
      <div class="row">
        <input
          type="number"
          name="quantity"
          placeholder="Available Quantity"
          required
        >

        <select enabled>

          <option value="kg">Kgs</option>
          <option value="g">Grams</option>
          <option value="quintal">Quintals</option>
          <option value="litre">Litre</option>
          <option value="ml">mls</option>
          <option value="piece">Pieces</option>
          <option value="dozen">Dozens</option>
          <option value="bag">Bags</option>
          <option value="bundle">Bundles</option> 
        </select>
      </div>

      <small class="hint">
        This is the total stock available for sale
      </small>

      <!-- AVAILABILITY -->
      <div class="row">
        <input type="date" name="available_from" required>
        <input type="date" name="expiry_date" required>
      </div>

      <!-- DESCRIPTION -->
      <textarea name="description" placeholder="Description"></textarea>

      <!-- ACTIONS -->
      <button class="btn primary" name="add_product">Save Product</button>
      <button type="button" class="btn outline" onclick="closeAddProduct()">Cancel</button>
    </form>

  </div>
</div>


 <!-- MY PRODUCTS -->
  <a href="javascript:void(0)" onclick="openAddProduct()">
  <i class="fa fa-plus"></i> Add Product
</a>

    

    <div class="products-grid">
      <?php
      $res = $conn->query("SELECT * FROM products WHERE farmer_id=$farmer_id ORDER BY created_at DESC");
      while ($row = $res->fetch_assoc()):
      ?>
      <div class="product-card">
        <img src="../uploads/products/<?= htmlspecialchars($row['image']) ?>">

        <h3><?= htmlspecialchars($row['name']) ?></h3>

        <p class="price">
          ₹<?= $row['price_inr'] ?> / <?= $row['unit'] ?>
        </p>

        <p class="qty">
          Available Quantity: <?= $row['quantity'] ?> <?= $row['unit'] ?>
        </p>

        <span class="status <?= $row['status'] ?>">
          <?= ucfirst($row['status']) ?>
        </span>
      </div>
      <?php endwhile; ?>
    </div>
  </section>
<script src="https://cdn.jsdelivr.net/npm/web3@1.10.0/dist/web3.min.js"></script>

<script>
/* ======================================================
   DOM READY
====================================================== */
document.addEventListener("DOMContentLoaded", () => {
  // Auto hide success banner
  const successBanner = document.getElementById("productSuccess");
  if (successBanner) {
    setTimeout(() => {
      successBanner.style.display = "none";
    }, 4000);
  }
});

/* ======================================================
   ADD PRODUCT MODAL
====================================================== */
function openAddProduct() {
  const modal = document.getElementById("addProductModal");
  if (!modal) return;
  modal.style.display = "flex";
}

function closeAddProduct() {
  const modal = document.getElementById("addProductModal");
  if (!modal) return;
  modal.style.display = "none";
}

/* ======================================================
   CATEGORY SELECTION
====================================================== */
function selectCategory(el, value) {
  const categoryInput = document.getElementById("category");
  const customBox = document.getElementById("customBox");

  if (!categoryInput) return;

  categoryInput.value = value;

  document.querySelectorAll(".cat-card").forEach(card => {
    card.classList.remove("active");
  });

  el.classList.add("active");

  if (customBox) customBox.style.display = "none";
}

function selectCustomCategory(el) {
  const categoryInput = document.getElementById("category");
  const customBox = document.getElementById("customBox");

  if (!categoryInput || !customBox) return;

  categoryInput.value = "custom";

  document.querySelectorAll(".cat-card").forEach(card => {
    card.classList.remove("active");
  });

  el.classList.add("active");
  customBox.style.display = "block";
}

/* ======================================================
   SECTION SWITCHER (SAFE + ANIMATED)
====================================================== */
function showSection(sectionId) {
  const sections = document.querySelectorAll("main section");

  sections.forEach(section => {
    section.style.display = "none";
    section.classList.remove("fade-in");
  });

  const target = document.getElementById(sectionId);
  if (!target) return;

  target.style.display = "block";

  // animation
  setTimeout(() => {
    target.classList.add("fade-in");
  }, 10);

  // sidebar active state
  document.querySelectorAll(".sidebar a").forEach(link => {
    link.classList.remove("active");
  });
}

/* ======================================================
   BLOCKCHAIN PAYMENT (POLYGON)
====================================================== */
async function buyProduct(orderId, farmerWallet, amountInWei) {
  if (!window.ethereum) {
    alert("MetaMask is required to complete payment");
    return;
  }

  try {
    // Request wallet
    const accounts = await ethereum.request({
      method: "eth_requestAccounts"
    });

    const buyerWallet = accounts[0];
    const web3 = new Web3(window.ethereum);

    // CONTRACT DETAILS (replace properly)
    const contractAddress = "0xYOUR_DEPLOYED_CONTRACT_ADDRESS";
    const abi = YOUR_CONTRACT_ABI;

    const contract = new web3.eth.Contract(abi, contractAddress);

    const tx = await contract.methods
      .recordOrder(orderId, farmerWallet, amountInWei)
      .send({
        from: buyerWallet,
        value: amountInWei
      });

    console.log("TX Hash:", tx.transactionHash);

    // Save transaction
    await fetch("save_tx.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        order_id: orderId,
        tx_hash: tx.transactionHash
      })
    });

    alert("✅ Payment successful!");

  } catch (err) {
    console.error(err);
    alert("❌ Transaction failed or cancelled");
  }
}



/* ======================================================
   🎤 FULL VOICE AI ASSISTANT (FINAL)
====================================================== */

let recognition;
let isListening = false;
let isAddingProduct = false;

function startVoice() {

  if (!('webkitSpeechRecognition' in window || 'SpeechRecognition' in window)) {
    alert("Voice not supported in this browser");
    return;
  }

  recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();

  recognition.lang = "en-IN";
  recognition.continuous = true;        // ✅ keep listening
  recognition.interimResults = false;   // ✅ only final speech

  // 🎤 UI FEEDBACK
  recognition.onstart = () => {
    isListening = true;
    document.getElementById("voiceBtn").classList.add("listening");

    document.getElementById("chatMessages").innerHTML += `
      <div class="msg bot">🎤 Listening...</div>
    `;
  };

 recognition.onresult = (event) => {
  let transcript = "";

  for (let i = event.resultIndex; i < event.results.length; i++) {
    transcript += event.results[i][0].transcript;
  }

  transcript = transcript.toLowerCase().trim();

  console.log("Heard:", transcript);

  // 🔥 FIRST NAVIGATION
  handleNavigation(transcript);

  // 🔥 THEN PRODUCT FORM LOGIC
  handleVoiceCommand(transcript);
};

  // 🔁 KEEP LISTENING
  recognition.onend = () => {
    if (isListening) {
      recognition.start();
    }
  };

  // ❌ ERROR HANDLING
  recognition.onerror = (e) => {
    console.log("Voice error:", e.error);
  };

  recognition.start();
}


function handleVoiceCommand(msg) {

  // 🟢 OPEN ADD PRODUCT
  if (msg.includes("add product")) {
    openAddProduct();
    isAddingProduct = true;
  }

  // 🚫 Stop if not adding
  if (!isAddingProduct) return;

  // 🧠 SMART FULL PARSING
  // CATEGORY
  const categories = ["farm", "poultry", "dairy", "handmade", "organic", "custom"];

categories.forEach(cat => {
  if (msg.includes(cat)) {

    // set hidden input
    document.getElementById("category").value = cat;

    // update UI selection
    document.querySelectorAll(".cat-card").forEach(card => {
      card.classList.remove("active");

      let text = card.innerText.toLowerCase();

      if (text.includes(cat)) {
        card.classList.add("active");
      }
    });

  }
});
  // NAME
  let nameMatch = msg.match(/product name (.+?)( price| quantity| category| description|$)/);
  if (nameMatch) {
    document.querySelector("input[name='name']").value = nameMatch[1].trim();
  }

  // PRICE
  let priceMatch = msg.match(/price (\d+)/);
  if (priceMatch) {
    document.querySelector("input[name='price']").value = priceMatch[1];
  }

  // QUANTITY
  let qtyMatch = msg.match(/quantity (\d+)/);
  if (qtyMatch) {
    document.querySelector("input[name='quantity']").value = qtyMatch[1];
  }

  // UNIT
  if (msg.includes("kg")) {
    document.querySelector("select[name='unit']").value = "kg";
  }

  

  // AVAILABLE
if (msg.includes("available today")) {
  let today = new Date().toISOString().split("T")[0];
  document.querySelector("input[name='available_from']").value = today;
} else {
  let availMatch = msg.match(/available (.+?)( expiry|$)/);
  if (availMatch) {
    let d = new Date(availMatch[1].trim());
    if (!isNaN(d)) {
      document.querySelector("input[name='available_from']").value =
        d.toISOString().split("T")[0];
    }
  }
}

// EXPIRY
let expMatch = msg.match(/expiry (.+)/);
if (expMatch) {
  let d = new Date(expMatch[1].trim());
  if (!isNaN(d)) {
    document.querySelector("input[name='expiry_date']").value =
      d.toISOString().split("T")[0];
  }
}

  // DESCRIPTION
  let descMatch = msg.match(/description (.+)/);
  if (descMatch) {
    document.querySelector("textarea[name='description']").value = descMatch[1];
  }

  // 🚀 AUTO SAVE
  if (msg.includes("save product")) {
    document.querySelector("button[name='add_product']").click();
    isAddingProduct = false;
  }
}



function handleNavigation(msg) {

  msg = msg.toLowerCase();

  if (msg.includes("dashboard")) {
    window.location.href = "dashboard.php";
  }

  else if (msg.includes("add product")) {
    openAddProduct();
  }

  else if (msg.includes("my products") || msg.includes("show products")) {
    window.location.href = "my_products.php";
  }

  else if (msg.includes("orders") || msg.includes("my orders")) {
    window.location.href = "orders.php";
  }

  else if (msg.includes("earnings")) {
    window.location.href = "earnings.php";
  }

  else if (msg.includes("profile") || msg.includes("farm profile")) {
    window.location.href = "farm_profile.php";
  }

  else if (msg.includes("logout")) {
    window.location.href = "logout.php";
  }
}
</script>

<!-- AI CHAT UI -->
<div id="chatBox" style="
  position: fixed;
  bottom: 80px;
  right: 20px;
  width: 260px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  padding: 10px;
  z-index: 999;
">

  <div id="chatMessages" style="
    height: 120px;
    overflow-y: auto;
    font-size: 13px;
  "></div>

  <input id="msg" placeholder="Type or speak..." style="
    width: 100%;
    padding: 6px;
    margin-top: 5px;
  ">
</div>
<div id="voiceBtn" onclick="startVoice()" style="
  position: fixed;
  bottom: 20px;
  right: 20px;
  width: 65px;
  height: 65px;
  background: linear-gradient(135deg,#2e7d32,#66bb6a);
  color: white;
  font-size: 26px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 6px 18px rgba(0,0,0,0.3);
  z-index: 999;
  transition: 0.3s;
">
🎤
</div>
</body>
</html>
