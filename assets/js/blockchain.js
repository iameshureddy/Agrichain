async function recordOnBlockchain() {
  if (!window.ethereum) {
    alert("Install MetaMask");
    return;
  }

  const accounts = await ethereum.request({ method: "eth_requestAccounts" });
  alert("Connected wallet: " + accounts[0]);

  // Later:
  // contract.methods.addProduct(priceWei).send({ from: accounts[0] });

  document.getElementById("tx_hash").value = "SIMULATED_TX_" + Date.now();
}
