<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    die("Unauthorized access");
}

$farmer_id = (int)$_SESSION['user_id'];
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/* ===== FETCH PRODUCT ===== */
$res = $conn->query("
    SELECT * FROM products 
    WHERE id = $product_id AND farmer_id = $farmer_id
");

$product = $res->fetch_assoc();
if (!$product) {
    die("Product not found");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Product | AgriChain</title>

<!-- Edit Product CSS -->
<link rel="stylesheet" href="../assets/css/edit_product.css">
</head>

<body>

<div class="edit-container">

  <div class="edit-header">
    <h1>✏️ Edit Product</h1>
    <p>Update your product details</p>
    <a href="my_products.php">← Back to My Products</a>
  </div>

  <form class="edit-form"
        method="POST"
        action="update_product.php"
        enctype="multipart/form-data">

    <input type="hidden" name="id" value="<?= $product['id'] ?>">

    <!-- Product Name -->
    <label>Product Name</label>
    <input type="text"
           name="name"
           value="<?= htmlspecialchars($product['name']) ?>"
           required>

    <!-- Price -->
    <label>Price (₹)</label>
    <input type="number"
           step="0.01"
           name="price"
           value="<?= $product['price_inr'] ?>"
           required>

    <!-- Quantity -->
    <label>Quantity</label>
    <input type="number"
           name="quantity"
           value="<?= $product['quantity'] ?>"
           required>

    <!-- Unit -->
    <select name="unit" required>
  <option value="kg" <?= $product['unit']=="kg" ? "selected" : "" ?>>Kg</option>
  <option value="gram" <?= $product['unit']=="gram" ? "selected" : "" ?>>Gram</option>
  <option value="litre" <?= $product['unit']=="litre" ? "selected" : "" ?>>Litre</option>
  <option value="ml" <?= $product['unit']=="ml" ? "selected" : "" ?>>Millilitre</option>
  <option value="bag" <?= $product['unit']=="bag" ? "selected" : "" ?>>Bag</option>
  <option value="dozen" <?= $product['unit']=="dozen" ? "selected" : "" ?>>Dozen</option>
  <option value="piece" <?= $product['unit']=="piece" ? "selected" : "" ?>>Piece</option>
  <option value="quintal" <?= $product['unit']=="quintal" ? "selected" : "" ?>>Quintal</option>
  <option value="bundle" <?= $product['unit']=="bundle" ? "selected" : "" ?>>Bundle</option> 
</select>


    <!-- Available From -->
    <label>Available From</label>
    <input type="date"
           name="available_from"
           value="<?= $product['available_from'] ?>"
           required>

    <!-- Expiry Date -->
    <label>Expiry Date</label>
    <input type="date"
           name="expiry_date"
           value="<?= $product['expiry_date'] ?>"
           required>

    <!-- Description -->
    <label>Description</label>
    <textarea name="description"
              rows="4"><?= htmlspecialchars($product['description']) ?></textarea>

    <!-- Current Image -->
    <?php if (!empty($product['image'])): ?>
      <div class="current-image">
        <p>Current Image</p>
        <img src="../uploads/products/<?= htmlspecialchars($product['image']) ?>">
      </div>
    <?php endif; ?>

    <!-- Change Image -->
    <label>Change Image (optional)</label>
    <input type="file" name="image">

    <!-- Actions -->
    <div class="edit-actions">
      <button type="submit" class="btn save">
        💾 Save Changes
      </button>
      <a href="my_products.php" class="btn cancel">
        Cancel
      </a>
    </div>

  </form>

</div>

</body>
</html>
