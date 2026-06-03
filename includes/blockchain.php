<?php
function generateOrderHash($orderId, $total) {
    return hash(
      'sha256',
      $orderId . "|" . number_format($total,2,'.','') . "|RAZORPAY"
    );
}
