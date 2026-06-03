<?php
session_start();
require_once "../includes/db.php";
require_once "../utils/telegram.php";

/* ================= AUTH ================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    header("Location: ../auth/login.php");
    exit;
}

$consumer_id = (int)$_SESSION['user_id'];

/* ================= FETCH CART ================= */
$cart = $conn->query("
    SELECT c.product_id, c.quantity, p.price_inr, p.farmer_id
    FROM cart c
    JOIN products p ON p.id = c.product_id
    WHERE c.consumer_id = $consumer_id
");

if ($cart->num_rows == 0) {
    $_SESSION['popup'] = "Cart is empty!";
    header("Location: cart.php");
    exit;
}

/* ================= START TRANSACTION ================= */
$conn->begin_transaction();

try {

    /* ================= CREATE ORDER ================= */
    $stmt = $conn->prepare("
        INSERT INTO consumer_orders
        (consumer_id, payment_method, payment_ref, status)
        VALUES (?, 'COD', 'CASH_ON_DELIVERY', 'PLACED')
    ");
    $stmt->bind_param("i", $consumer_id);
    $stmt->execute();

    $order_id = $stmt->insert_id;

    /* ================= MOVE CART ITEMS ================= */
    $total = 0;
    $farmer_id = null;

    while ($c = $cart->fetch_assoc()) {

        $sub = $c['price_inr'] * $c['quantity'];
        $total += $sub;
        $farmer_id = $c['farmer_id'];

        $stmtItem = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");
        $stmtItem->bind_param(
            "iiid",
            $order_id,
            $c['product_id'],
            $c['quantity'],
            $c['price_inr']
        );
        $stmtItem->execute();
    }

    /* ================= UPDATE TOTAL ================= */
    $stmt = $conn->prepare("
        UPDATE consumer_orders
        SET total_amount = ?
        WHERE id = ?
    ");
    $stmt->bind_param("di", $total, $order_id);
    $stmt->execute();

    /* ================= CLEAR CART ================= */
    $conn->query("DELETE FROM cart WHERE consumer_id = $consumer_id");

    /* ================= BLOCKCHAIN ================= */
    $fingerprint = $order_id . "|" . $consumer_id . "|" . $total;
    $cmd = "node ../blockchain-api/storeOrder.js $order_id \"$fingerprint\"";
    $res = json_decode(shell_exec($cmd), true);

    if (!empty($res)) {

        $hash  = $res['hash'] ?? null;
        $tx    = $res['txHash'] ?? ($res['transactionHash'] ?? null);
        $block = $res['blockNumber'] ?? null;

        if ($tx) {
            $stmt = $conn->prepare("
                UPDATE consumer_orders
                SET blockchain_hash = ?, blockchain_tx = ?, block_number = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ssii", $hash, $tx, $block, $order_id);
            $stmt->execute();
        }
    }

    /* ================= COMMIT ================= */
    $conn->commit();

} catch (Exception $e) {

    $conn->rollback();
    die("Order failed: " . $e->getMessage());
}

/* ================= TELEGRAM ================= */
$consumer = $conn->query("
    SELECT name, telegram_chat_id
    FROM users
    WHERE id = $consumer_id
")->fetch_assoc();

$farmer = null;
if (!empty($farmer_id)) {
    $farmer = $conn->query("
        SELECT name, telegram_chat_id
        FROM users
        WHERE id = $farmer_id
    ")->fetch_assoc();
}

if (!empty($consumer['telegram_chat_id'])) {
    sendTelegram(
        $consumer['telegram_chat_id'],
        "🛒 Order #$order_id placed. Amount ₹".number_format($total,2)
    );
}

if ($farmer && !empty($farmer['telegram_chat_id'])) {
    sendTelegram(
        $farmer['telegram_chat_id'],
        "🚜 New COD Order #$order_id received."
    );
}

/* ================= REDIRECT ================= */
$_SESSION['popup'] = "🎉 COD Order placed successfully!";
header("Location: orders.php?new_order=$order_id");
exit;
?>
