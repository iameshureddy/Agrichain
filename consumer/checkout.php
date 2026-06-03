<?php
session_start();
require_once "../includes/db.php";

/* AUTH */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    header("Location: ../auth/login.php");
    exit;
}

$consumer_id = (int)$_SESSION['user_id'];

/* ADDRESS */
$address = $conn->query("
  SELECT * FROM consumer_addresses
  WHERE consumer_id=$consumer_id AND is_default=1
  LIMIT 1
")->fetch_assoc();

if (!$address) {
    header("Location: address.php");
    exit;
}

/* CART + FARMERS */
$sql = "
SELECT 
  p.id product_id,
  p.name product_name,
  p.image,
  p.price_inr,
  c.quantity,
  fp.owner_name,
  fp.upi_id
FROM cart c
JOIN products p ON p.id=c.product_id
JOIN farm_profiles fp ON fp.farmer_id=p.farmer_id
WHERE c.consumer_id=$consumer_id
";
$res = $conn->query($sql);

if ($res->num_rows === 0) {
    header("Location: cart.php");
    exit;
}

$farmers = [];
$items   = [];
$total   = 0;

while ($r = $res->fetch_assoc()) {
    $sub = $r['price_inr'] * $r['quantity'];
    $total += $sub;

    $items[] = [
        'name' => $r['product_name'],
        'img'  => $r['image'],
        'qty'  => $r['quantity'],
        'price'=> $r['price_inr'],
        'sub'  => $sub
    ];

    $farmers[] = [
        'name' => $r['owner_name'],
        'upi'  => $r['upi_id'],
        'amt'  => $sub
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Checkout | AgriChain</title>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<link rel="stylesheet"
 href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body{font-family:'Segoe UI',sans-serif;background:#f5f7fa}
.wrap{max-width:1000px;margin:30px auto}
.card{background:#fff;border-radius:18px;padding:22px;margin-bottom:20px;box-shadow:0 10px 24px rgba(0,0,0,.12)}
h2,h3{margin:0 0 12px}
.addr{line-height:1.6}
.edit{float:right;color:#1565c0;text-decoration:none}
.item{display:flex;gap:14px;margin:14px 0}
.item img{width:70px;height:70px;border-radius:10px;object-fit:cover}
.item b{font-size:16px}
.price{margin-left:auto;font-weight:600}
.total{font-size:22px;font-weight:bold;text-align:right;margin-top:10px}

.pay label{display:block;margin:10px 0;font-size:16px}
.box{display:none;margin-top:16px}
.qr{text-align:center;margin-top:14px}
.qr img{width:180px}

.btn{padding:14px 28px;border:none;border-radius:14px;color:#fff;font-size:16px;cursor:pointer}
.cod{background:#6a1b9a}
.upi{background:#1565c0}
.rzp{background:#2e7d32}
</style>

<script>
function showBox(id){
  document.querySelectorAll('.box').forEach(b=>b.style.display='none');
  document.getElementById(id).style.display='block';
}
</script>
</head>

<body>

<div class="wrap">

<!-- ADDRESS -->
<div class="card">
<h3>📍 Delivery Address
<a class="edit" href="address.php"><i class="fa fa-pen"></i> Edit</a>
</h3>
<div class="addr">
<b><?= htmlspecialchars($address['name']) ?></b><br>
<?= htmlspecialchars($address['house_no']) ?>, <?= htmlspecialchars($address['area']) ?><br>
<?= htmlspecialchars($address['district']) ?>, <?= htmlspecialchars($address['state']) ?> - <?= htmlspecialchars($address['pincode']) ?><br>
📞 <?= htmlspecialchars($address['phone']) ?>
</div>
</div>

<!-- PRODUCTS -->
<div class="card">
<h3>🛒 Order Items</h3>

<?php foreach ($items as $it): ?>
<div class="item">
<img src="../uploads/products/<?= $it['img'] ?: 'no-image.png' ?>">
<div>
<b><?= htmlspecialchars($it['name']) ?></b><br>
Qty: <?= $it['qty'] ?> × ₹<?= $it['price'] ?>
</div>
<div class="price">₹<?= $it['sub'] ?></div>
</div>
<?php endforeach; ?>

<div class="total">Grand Total: ₹<?= $total ?></div>
</div>

<!-- PAYMENT -->
<div class="card pay">
<h3>💳 Payment Method</h3>

<label>
<input type="radio" name="pay" onclick="showBox('codBox')">
 <i class="fa fa-truck"></i> Cash on Delivery
</label>

<label>
<input type="radio" name="pay" onclick="showBox('upiBox')">
 <i class="fa fa-qrcode"></i> UPI (Scan & Pay)
</label>

<label>
<input type="radio" name="pay" onclick="showBox('rzpBox')">
 <i class="fa fa-credit-card"></i> Razorpay
</label>

<!-- COD -->
<div id="codBox" class="box">
<form action="cod_success.php" method="post">
<button class="btn cod">Place COD Order</button>
</form>
</div>

<!-- UPI -->
<div id="upiBox" class="box">
<?php foreach ($farmers as $f):
$upi = "upi://pay?" . http_build_query([
  'pa'=>$f['upi'],
  'pn'=>$f['name'],
  'am'=>$f['amt'],
  'cu'=>'INR'
]);
?>
<div class="qr">
<b><?= htmlspecialchars($f['name']) ?></b><br>
₹<?= $f['amt'] ?><br>
<img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($upi) ?>">
</div>
<?php endforeach; ?>

<form action="upi_success.php" method="post">
<button class="btn upi">I Have Paid</button>
</form>
</div>

<!-- RAZORPAY -->
<div id="rzpBox" class="box">
<button id="rzpBtn" class="btn rzp">Pay with Razorpay</button>
</div>

</div>

</div>

<script>
var options = {
  key: "rzp_test_S6e66IefUlzshv",
  amount: "<?= $total*100 ?>",
  currency: "INR",
  name: "AgriChain",
  handler: function (res) {
    window.location.href =
      "razorpay_success.php?payment_id=" + res.razorpay_payment_id;
  }
};
document.getElementById("rzpBtn").onclick = function(){
  new Razorpay(options).open();
};
</script>

</body>
</html>
