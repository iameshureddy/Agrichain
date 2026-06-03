<?php
session_start();
require_once "../includes/db.php";

/* ================= AUTH ================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    header("Location: ../auth/login.php");
    exit;
}

$consumerName = $_SESSION['name'] ?? "Customer";

/* ================= FETCH FARMER PRODUCTS ================= */
$sql = "
SELECT 
  p.id,
  p.name,
  p.price_inr,
  p.unit,
  p.quantity,
  p.image,
  p.category,
  p.description,
  p.available_from,
  p.expiry_date,

  CASE
    WHEN CURDATE() < p.available_from THEN 'upcoming'
    WHEN CURDATE() > p.expiry_date THEN 'expired'
    ELSE 'active'
  END AS status,

  f.farm_name,
  f.village,
  f.district

FROM products p
JOIN farm_profiles f ON p.farmer_id = f.farmer_id
ORDER BY p.created_at DESC
";

$products = $conn->query($sql);
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>AgriChain – Fresh From Nearby Farms</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="../assets/css/consumer.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- ================= SIDEBAR ================= -->
<aside class="consumer-sidebar">
  <h2>🛒 AgriChain</h2>

  <a class="active"><i class="fa fa-home"></i> Home</a>
  <a href="cart.php"><i class="fa fa-cart-shopping"></i> Cart</a>
  <a href="orders.php"><i class="fa fa-box"></i> Orders</a>
  <a href="address.php"><i class="fa fa-location-dot"></i> Address</a> 
  <a href="../auth/logout.php" class="logout">
    <i class="fa fa-sign-out-alt"></i> Logout
  </a>
</aside>

<!-- ================= MAIN ================= -->
<main class="consumer-main">

<!-- TOP BAR -->
<div class="top-bar">
  

  <input
    type="text"
    id="searchInput"
    placeholder="Search vegetables, fruits, dairy..."
    onkeyup="searchProducts()"
  >
   <!-- OFFER -->
<div class="offer-banner">
  🚜 Farm-to-Home delivery in <b>15–30 minutes</b>
</div>
  <div class="delivery-box">
    📍 Delivering nearby farms
    <a href="address.php">Change</a>
  </div>
</div>

<!-- WELCOME -->
<section class="welcome-box">
  <h1>👋 Hello <span><?= htmlspecialchars($consumerName) ?></span></h1>
  <p>
    Fresh vegetables, fruits, dairy and poultry delivered directly
    from nearby farmers. No middlemen. Honest prices.
  </p>
   <!-- ================= DELIVERY INFO FLOAT CARD ================= -->
<style>
.delivery-float{
  position:fixed;
  top:130px;                /* adjust if header height differs */
  right:60px;
  z-index:1000;

  width:280px;
  background:linear-gradient(135deg,#e8f5e9,#f1f8e9);
  border-radius:18px;
  padding:16px 18px;

  display:flex;
  gap:14px;
  align-items:flex-start;

  box-shadow:0 12px 26px rgba(0,0,0,.15);
  border-left:6px solid #2e7d32;
}

.delivery-float .icon{
  font-size:32px;
}

.delivery-float .text strong{
  display:block;
  font-size:16px;
  color:#2e7d32;
  line-height:1.3;
}

.delivery-float .text span{
  display:block;
  margin-top:4px;
  font-size:13px;
  color:#555;
}

/* Hide on small screens */
@media (max-width:768px){
  .delivery-float{
    display:none;
  }
}
</style>

<div class="delivery-float">
  <div class="icon">🚚</div>
  <div class="text">
    <strong>Free Delivery on orders above ₹159</strong>
    <span>Small orders below ₹499 have a ₹40 delivery fee</span>
  </div>
</div>
<!-- ============================================================ -->

</section>
  <!-- ================= CATEGORIES ================= -->
<div class="categories">

  <div class="category-card active" data-category="all">
    <span class="cat-icon">🛒</span>
    <span class="cat-text">All</span>
  </div>

  <div class="category-card" data-category="farm">
    <span class="cat-icon">🌾</span>
    <span class="cat-text">Farm</span>
  </div>

  <div class="category-card" data-category="dairy">
    <span class="cat-icon">🥛</span>
    <span class="cat-text">Dairy</span>
  </div>

  <div class="category-card" data-category="poultry">
    <span class="cat-icon">🍗</span>
    <span class="cat-text">Poultry</span>
  </div>

  <div class="category-card" data-category="organic">
    <span class="cat-icon">🍃</span>
    <span class="cat-text">Organic</span>
  </div>
  
  <div class="category-card" data-category="handmade">
  <span class="cat-icon">🧵</span>
  <span class="cat-text">Handmade</span>
</div>

</div>



<!-- PRODUCTS -->




<h2 class="section-title">Available Products</h2>

<div class="product-grid" id="productGrid">

<?php while ($p = $products->fetch_assoc()): ?>
<div class="product-card"
     data-id="<?= $p['id'] ?>"
     data-name="<?= strtolower($p['name']) ?>"
     data-category="<?= strtolower(trim($p['category'])) ?>"
     data-status="<?= $p['status'] ?>">


  <img src="../uploads/products/<?= $p['image'] ?: 'no-image.png' ?>">

  <h3><?= htmlspecialchars($p['name']) ?></h3>

  <p class="farm">
    🚜 <?= htmlspecialchars($p['farm_name']) ?>,
    <?= htmlspecialchars($p['village']) ?>
  </p>

  <div class="meta-row">
    <span class="stock">
      Stock: <?= $p['quantity'] ?> <?= $p['unit'] ?>
    </span>
    <span class="status <?= $p['status'] ?>">
      <?= strtoupper($p['status']) ?>
    </span>
  </div>

  <p class="desc">
    <?= htmlspecialchars(substr($p['description'], 0, 70)) ?>…
  </p>

  <p class="price">
    ₹<?= $p['price_inr'] ?> / <?= $p['unit'] ?>
  </p>

  <div class="qty-box">
  <button onclick="decQty(this)">−</button>
  <span class="qty">1</span>

  <button onclick="incQty(this)">+</button>
</div>


  <button class="add-btn"
    <?= $p['status'] !== 'active' ? 'disabled' : '' ?>
    onclick="addToCart(this)">
    <?= $p['status'] === 'active' ? 'Add to Cart' : 'Unavailable' ?>
  </button>

</div>
<?php endwhile; ?>

</div>
</main>

<!-- POPUP -->
<div id="popup"></div>
<script src="/agrichain/assets/js/cart.js"></script>
<script src="/agrichain/assets/js/consumer.js"></script>
</body>
</html>

<
