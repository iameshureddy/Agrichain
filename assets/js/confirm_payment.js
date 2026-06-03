document.addEventListener("DOMContentLoaded", () => {
  const paidBtn = document.getElementById("paidBtn");
  const msg = document.getElementById("msg");

  if (!paidBtn) return;

  paidBtn.onclick = async () => {
    paidBtn.disabled = true;
    msg.innerText = "Confirming payment...";

    try {
      const res = await fetch("../consumer/confirm_payment.php", {
        method: "POST"
      });

      const data = await res.json();

      if (!data.success) {
        msg.innerText = data.error || "Payment failed";
        paidBtn.disabled = false;
        return;
      }

      msg.innerText = "✅ Order confirmed! Redirecting...";
      setTimeout(() => {
        window.location.href = "orders.php";
      }, 1200);

    } catch (err) {
      msg.innerText = "Server error. Try again.";
      paidBtn.disabled = false;
    }
  };
});
