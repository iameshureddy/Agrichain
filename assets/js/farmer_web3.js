let web3;
let account;

async function connectWallet() {
  if (!window.ethereum) {
    document.getElementById("walletStatus").innerText = "MetaMask not installed";
    return;
  }

  web3 = new Web3(window.ethereum);
  const accounts = await ethereum.request({ method: "eth_requestAccounts" });
  account = accounts[0];

  document.getElementById("walletStatus").innerText =
    account.slice(0, 6) + "..." + account.slice(-4);

  // Mock analytics (replace with API later)
  document.getElementById("earningsINR").innerText = 12500;
  document.getElementById("ordersCount").innerText = 8;
  document.getElementById("productCount").innerText = 4;
  document.getElementById("tokens").innerText = 120;
}

window.addEventListener("load", connectWallet);

// ➕ Add product (₹ → ETH conversion later)
function addProduct() {
  alert("Product sent to blockchain (demo)");
}

// 🎙 Voice commands
function startVoice() {
  const rec = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
  rec.lang = "en-IN";
  rec.start();

  rec.onresult = (e) => {
    alert("You said: " + e.results[0][0].transcript);
  };
}
