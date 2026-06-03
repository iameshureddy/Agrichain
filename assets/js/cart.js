/* ===============================
   SAFE DOM READY
================================ */
document.addEventListener("DOMContentLoaded", () => {
  // Nothing forced here
});

/* ===============================
   QUANTITY CONTROLS
================================ */
function incQty(btn) {
  const span = btn.parentElement.querySelector("span");
  span.innerText = parseInt(span.innerText) + 1;
}

function decQty(btn) {
  const span = btn.parentElement.querySelector("span");
  const val = parseInt(span.innerText);
  if (val > 1) span.innerText = val - 1;
}

/* ===============================
   ADD TO CART
================================ */
function addToCart(productId) {
  const card = document.querySelector(`.product-card[data-id="${productId}"]`);
  if (!card) return;

  const qty = card.querySelector(".qty-box span").innerText;

  fetch("/agrichain/consumer/cart_action.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=add&product_id=${productId}&quantity=${qty}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showPopup("✅ Added to cart");
    } else {
      showPopup(data.error || "❌ Failed");
    }
  })
  .catch(() => showPopup("❌ Network error"));
}

/* ===============================
   CART PAGE CONTROLS
================================ */
function updateQty(productId, qty) {
  if (qty < 1) return;

  fetch("/agrichain/consumer/cart_action.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=update&product_id=${productId}&quantity=${qty}`
  }).then(() => location.reload());
}

function removeItem(productId) {
  fetch("/agrichain/consumer/cart_action.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=remove&product_id=${productId}`
  }).then(() => location.reload());
}

/* ===============================
   SEARCH FILTER
================================ */
function searchProducts() {
  const input = document.getElementById("searchInput");
  if (!input) return;

  const term = input.value.toLowerCase();
  document.querySelectorAll(".product-card").forEach(card => {
    card.style.display = card.dataset.name.includes(term)
      ? "block"
      : "none";
  });
}

/* ===============================
   CATEGORY FILTER
================================ */
function filterCategory(cat) {
  document.querySelectorAll(".category-card").forEach(c => c.classList.remove("active"));
  event.currentTarget.classList.add("active");

  document.querySelectorAll(".product-card").forEach(card => {
    card.style.display =
      cat === "all" || card.dataset.category === cat
        ? "block"
        : "none";
  });
}

/* ===============================
   POPUP
================================ */
function showPopup(msg) {
  const popup = document.getElementById("popup");
  if (!popup) return;

  popup.innerText = msg;
  popup.className = "popup show";

  setTimeout(() => popup.className = "popup", 2000);
}
