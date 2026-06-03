<?php
session_start();
require_once "../includes/db.php";

/* AUTH */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    header("Location: ../auth/login.php");
    exit;
}

$consumer_id = (int)$_SESSION['user_id'];

/* HANDLE QUANTITY ACTIONS */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_id = (int)$_POST['product_id'];
    $action = $_POST['action'] ?? '';

    if ($action === 'inc') {
        $conn->query("
            UPDATE cart 
            SET quantity = quantity + 1 
            WHERE consumer_id = $consumer_id AND product_id = $product_id
        ");
    }

    if ($action === 'dec') {
        $conn->query("
            UPDATE cart 
            SET quantity = GREATEST(quantity - 1, 1)
            WHERE consumer_id = $consumer_id AND product_id = $product_id
        ");
    }

    if ($action === 'remove') {
        $conn->query("
            DELETE FROM cart 
            WHERE consumer_id = $consumer_id AND product_id = $product_id
        ");
    }

    header("Location: cart.php");
    exit;
}

/* FETCH CART */
$res = $conn->query("
    SELECT 
        c.product_id,
        c.quantity,
        p.name,
        p.price_inr,
        p.unit,
        p.image
    FROM cart c
    JOIN products p ON p.id = c.product_id
    WHERE c.consumer_id = $consumer_id
");

$total = 0;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>My Cart</title>
<link rel="stylesheet" href="../assets/css/cart.css">
</head>

<body>

<div class="cart-container">
<h2 class="cart-title">🛒 My Cart</h2>

<?php if ($res->num_rows === 0): ?>
  <div class="empty-cart">
    Your cart is empty.<br><br>
    <a href="dashboard.php">← Continue Shopping</a>
  </div>
<?php else: ?>

<?php while ($row = $res->fetch_assoc()):
    $subtotal = $row['price_inr'] * $row['quantity'];
    $total += $subtotal;
?>
<div class="cart-item">

  <img src="../uploads/products/<?= htmlspecialchars($row['image']) ?>">

  <div class="item-info">
    <h3><?= htmlspecialchars($row['name']) ?></h3>
    <p>₹<?= $row['price_inr'] ?> / <?= htmlspecialchars($row['unit']) ?></p>

    <div class="qty-controls">
      <form method="post">
        <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
        <input type="hidden" name="action" value="dec">
        <button type="submit">−</button>
      </form>

      <strong><?= $row['quantity'] ?></strong>

      <form method="post">
        <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
        <input type="hidden" name="action" value="inc">
        <button type="submit">+</button>
      </form>
    </div>
  </div>

  <div class="item-actions">
    <div class="subtotal">₹<?= $subtotal ?></div>

    <form method="post">
      <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
      <input type="hidden" name="action" value="remove">
      <button class="remove">Remove</button>
    </form>
  </div>

</div>
<?php endwhile; ?>

<div class="cart-summary">
  <strong>Total: ₹<?= $total ?></strong>
  <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
</div>

<?php endif; ?>
</div>

</body>
</html>
