const ETH_RATE_INR = 250000; // example rate

async function checkout(productId, priceInr, farmerAddress) {
  if (!account) {
    alert("Wallet not connected");
    return;
  }

  const eth = priceInr / ETH_RATE_INR;
  const wei = web3.utils.toWei(eth.toString(), "ether");

  try {
    const tx = await agriChain.methods.buyProduct(productId).send({
      from: account,
      value: wei
    });

    const orderId = tx.events.ProductBought.returnValues.orderId;

    await fetch("/agrichain/api/save_order.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        orderId,
        productId,
        farmer: farmerAddress,
        wei,
        inr: priceInr,
        tx: tx.transactionHash
      })
    });

    alert("✅ Order successful!");
    window.location.href = "/agrichain/consumer/orders.php";

  } catch (e) {
    console.error(e);
    alert("Transaction failed");
  }
}
