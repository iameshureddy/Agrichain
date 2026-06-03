<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

/* ================= AUTH ================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../auth/login.php");
    exit;
}

$farmer_id = (int) $_SESSION['user_id'];

/* ================= FETCH FARMER ORDERS ================= */
$stmt = $conn->prepare("
    SELECT DISTINCT
        co.id,
        co.consumer_id,
        co.total_amount,
        co.payment_method,
        co.status,
        co.created_at
    FROM consumer_orders co
    JOIN order_items oi ON oi.order_id = co.id
    JOIN products p ON p.id = oi.product_id
    WHERE p.farmer_id = ?
    ORDER BY co.id DESC
");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$orders = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Farmer Orders | AgriChain</title>

<style>
body{
    font-family:Segoe UI, sans-serif;
    background:#f4f6f8;
    margin:0;
}
.wrap{
    max-width:1100px;
    margin:30px auto;
}
.card{
    background:#fff;
    border-radius:16px;
    padding:22px;
    margin-bottom:26px;
    box-shadow:0 12px 25px rgba(0,0,0,.12);
}
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.badge{
    padding:6px 14px;
    border-radius:14px;
    font-size:13px;
    font-weight:600;
}
.PLACED{background:#fdecea;color:#c62828}
.PACKED{background:#fff3cd;color:#856404}
.SHIPPED{background:#e3f2fd;color:#1565c0}
.DELIVERED{background:#e8f5e9;color:#2e7d32}

.small{font-size:14px;color:#555}
hr{border:none;border-top:1px solid #eee;margin:18px 0}

/* PRODUCTS */
.items{
    display:flex;
    gap:16px;
    flex-wrap:wrap;
}
.item{
    width:200px;
    background:#fafafa;
    padding:12px;
    border-radius:14px;
}
.item img{
    width:100%;
    height:120px;
    object-fit:cover;
    border-radius:10px;
}

/* ADDRESS */
.address{
    background:#f1f8e9;
    padding:14px;
    border-radius:14px;
    font-size:14px;
}

/* FORM */
form{margin-top:12px}
select{
    padding:6px 10px;
    border-radius:8px;
}
button{
    padding:7px 14px;
    border:none;
    border-radius:8px;
    background:#2e7d32;
    color:#fff;
    font-weight:600;
    cursor:pointer;
}
</style>
</head>

<body>
<?php if (!empty($_SESSION['popup'])): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    alert("<?= addslashes($_SESSION['popup']) ?>");
});
</script>
<?php unset($_SESSION['popup']); endif; ?>

<div class="wrap">
<h2>📦 Orders Received</h2>

<!-- POPUP MESSAGE -->
<?php if (!empty($_SESSION['order_status_updated'])): ?>
<script>
alert("<?= addslashes($_SESSION['order_status_updated']) ?>");
</script>
<?php unset($_SESSION['order_status_updated']); endif; ?>

<?php if ($orders->num_rows === 0): ?>
    <p>No orders yet.</p>
<?php endif; ?>

<?php while ($o = $orders->fetch_assoc()): ?>

<div class="card">

    <!-- HEADER -->
    <div class="header">
        <div>
            <b>Order #<?= $o['id'] ?></b><br>
            <span class="small">
                Payment: <?= htmlspecialchars($o['payment_method']) ?><br>
                Ordered on: <?= date("d M Y, h:i A", strtotime($o['created_at'])) ?>
            </span>
        </div>
        <div style="text-align:right">
            <b>₹<?= number_format($o['total_amount'],2) ?></b><br>
            <span class="badge <?= $o['status'] ?>">
                <?= $o['status'] ?>
            </span>
        </div>
    </div>

    <hr>

    <!-- ADDRESS -->
    <h4>📍 Delivery Address</h4>
    <?php
    $addr = $conn->prepare("
        SELECT name, phone, house_no, area, landmark, village, district, state, pincode
        FROM consumer_addresses
        WHERE consumer_id = ? AND is_default = 1
        LIMIT 1
    ");
    $addr->bind_param("i", $o['consumer_id']);
    $addr->execute();
    $address = $addr->get_result()->fetch_assoc();
    ?>

    <?php if ($address): ?>
        <div class="address">
            <b><?= htmlspecialchars($address['name']) ?></b><br>
            📞 <?= htmlspecialchars($address['phone']) ?><br>
            <?= htmlspecialchars($address['house_no']) ?>,
            <?= htmlspecialchars($address['area']) ?>,
            <?= htmlspecialchars($address['landmark']) ?><br>
            <?= htmlspecialchars($address['village']) ?>,
            <?= htmlspecialchars($address['district']) ?>,
            <?= htmlspecialchars($address['state']) ?> - <?= htmlspecialchars($address['pincode']) ?>
        </div>
    <?php else: ?>
        <p class="small">No address found</p>
    <?php endif; ?>

    <hr>

    <!-- PRODUCTS -->
    <h4>🛒 Products</h4>
    <div class="items">
    <?php
    $items = $conn->prepare("
        SELECT p.name, p.image, oi.quantity, oi.price
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = ? AND p.farmer_id = ?
    ");
    $items->bind_param("ii", $o['id'], $farmer_id);
    $items->execute();
    $resItems = $items->get_result();

    while ($i = $resItems->fetch_assoc()):
    ?>
        <div class="item">
            <img src="../uploads/products/<?= htmlspecialchars($i['image'] ?: 'no-image.png') ?>">
            <b><?= htmlspecialchars($i['name']) ?></b><br>
            Qty: <?= (int)$i['quantity'] ?><br>
            Price: ₹<?= number_format($i['price'],2) ?>
        </div>
    <?php endwhile; ?>
    </div>

    <hr>

    <!-- STATUS UPDATE -->
    <h4>🚚 Update Order Status</h4>
    <form method="POST" action="update_order_status.php">
        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
        <select name="status" required>
            <?php foreach (['PLACED','PACKED','SHIPPED','DELIVERED'] as $s): ?>
                <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>>
                    <?= $s ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Update</button>
    </form>

</div>

<?php endwhile; ?>

</div>
</body>
</html>
