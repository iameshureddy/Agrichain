<?php
function inrToEth($inr) {
  $ETH_RATE = 100000; // ₹100,000 = 1 ETH (fixed)
  return round($inr / $ETH_RATE, 6);
}
