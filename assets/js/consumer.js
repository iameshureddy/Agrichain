document.addEventListener("DOMContentLoaded", () => {

  const categories = document.querySelectorAll(".category-card");
  const products = document.querySelectorAll(".product-card");

  categories.forEach(cat => {
    cat.addEventListener("click", () => {
      const selected = cat.dataset.category;

      categories.forEach(c => c.classList.remove("active"));
      cat.classList.add("active");

      products.forEach(p => {
        p.style.display =
          selected === "all" || p.dataset.category === selected
            ? "block"
            : "none";
      });
    });
  });

});

/* SEARCH */
function searchProducts() {
  const val = document.getElementById("searchInput").value.toLowerCase();
  document.querySelectorAll(".product-card").forEach(p => {
    p.style.display = p.dataset.name.includes(val) ? "block" : "none";
  });
}

/* QUANTITY */
function incQty(btn) {
  const q = btn.parentElement.querySelector(".qty");
  q.innerText = parseInt(q.innerText) + 1;
}

function decQty(btn) {
  const q = btn.parentElement.querySelector(".qty");
  if (parseInt(q.innerText) > 1) q.innerText--;
}

function addToCart(btn) {
  const card = btn.closest(".product-card");
  const id = card.dataset.id;
  const qty = card.querySelector(".qty").innerText;

  btn.disabled = true;
  btn.innerText = "Adding...";

  fetch("cart_action.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: `action=add&product_id=${id}&quantity=${qty}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showPopup("✅ Added to cart");
    } else {
      showPopup("❌ " + (data.message || "Failed"));
    }
    btn.disabled = false;
    btn.innerText = "Add to Cart";
  })
  .catch(err => {
    console.error(err);
    showPopup("❌ Server error");
    btn.disabled = false;
    btn.innerText = "Add to Cart";
  });
}


function showPopup(message) {
  const popup = document.getElementById("popup");
  if (!popup) return;

  popup.innerText = message;
  popup.style.display = "block";

  setTimeout(() => {
    popup.style.display = "none";
  }, 2000);
}
