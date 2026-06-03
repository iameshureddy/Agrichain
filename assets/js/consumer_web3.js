let web3;
let account;
let cart = [];
let total = 0;

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
}

window.addEventListener("load", connectWallet);

// 🛒 Cart
function addToCart(name, price) {
  cart.push({ name, price });
  total += price;
  renderCart();
}

function renderCart() {
  const cartDiv = document.getElementById("cartItems");
  cartDiv.innerHTML = "";

  cart.forEach((item) => {
    cartDiv.innerHTML += `<p>${item.name} - ₹${item.price}</p>`;
  });

  document.getElementById("total").innerText = total;
}

// 💳 Checkout
function checkout() {
  alert("MetaMask payment flow (next step)");
}

// 🎙 Voice Search
function startVoiceSearch() {
  const rec = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
  rec.lang = "en-IN";
  rec.start();

  rec.onresult = (e) => {
    const text = e.results[0][0].transcript;
    document.getElementById("search").value = text;
    alert("Searching for: " + text);
  };
}
