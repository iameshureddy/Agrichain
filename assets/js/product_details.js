let qty = 1;

function incQty() {
  qty++;
  document.getElementById("qty").innerText = qty;
}

function decQty() {
  if (qty > 1) {
    qty--;
    document.getElementById("qty").innerText = qty;
  }
}

function addToCart(productId) {
  showPopup("🛒 Product added to cart");
  // Later: AJAX → DB cart table
}

function showPopup(msg) {
  const p = document.getElementById("popup");
  p.innerText = msg;
  p.style.display = "block";
  setTimeout(() => p.style.display = "none", 2500);
}
