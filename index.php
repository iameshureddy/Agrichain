<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AgriChain | Blockchain Agriculture</title>

  <!-- CSS -->
  <link rel="stylesheet" href="assets/css/styles.css">

  <!-- Icons -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<!-- ================= NAVBAR ================= -->
<header class="navbar">
  <div class="logo">🌾 <strong>AgriChain</strong></div>

  <nav>
    <a href="#why">Why</a>
    <a href="#features">Features</a>
    <a href="auth/login.php" class="btn-nav">Login</a>
    <a href="auth/signup.php" class="btn-nav solid">Create Account</a>
  </nav>
</header>

<!-- ================= HERO ================= -->
<section class="hero">
  <div class="hero-overlay"></div>

  <div class="hero-content reveal">
    <h1>Empowering Farmers Through Blockchain</h1>

    <p>
      A next-generation agricultural marketplace that directly connects
      farmers and consumers with transparency, trust, and fair pricing.
    </p>

    <div class="hero-buttons">
      <a href="auth/signup.php?role=farmer" class="btn-main">
        🌾 Join as Farmer
      </a>
      <a href="auth/signup.php?role=consumer" class="btn-outline">
        🛒 Join as Consumer
      </a>
    </div>
  </div>
</section>

<!-- ================= WHY AGRICHAIN ================= -->
<section id="why" class="why-section">
  <div class="why-card reveal">
    <h2>Why AgriChain?</h2>

    <p>
      Traditional agriculture suffers from price exploitation, delayed payments,
      and lack of transparency due to multiple intermediaries.
      Farmers work hard but receive minimal profit.
    </p>

    <p>
      <strong>AgriChain</strong> solves this using
      <strong>blockchain technology</strong>.
      Every crop listing, order, and payment is permanently recorded,
      tamper-proof, and verifiable — ensuring fairness and trust for everyone.
    </p>

    <div class="why-actions">
      <a href="auth/signup.php" class="btn-main">Get Started</a>
      <a href="auth/login.php" class="btn-outline-dark">Login</a>
    </div>
  </div>
</section>

<!-- ================= FEATURES ================= -->
<section id="features" class="features-section">
  <h2 class="reveal">Platform Features</h2>
  <p class="subtitle reveal">
    Secure • Transparent • Farmer-First Marketplace
  </p>

  <div class="features-grid">

    <div class="feature-card reveal">
      <div class="icon">🌱</div>
      <h3>Crop Listing</h3>
      <p>
        Farmers list crops with pricing, quantity, harvest dates,
        and images. All data is securely stored and traceable.
      </p>
    </div>

    <div class="feature-card reveal">
      <div class="icon">🛒</div>
      <h3>Direct Purchase</h3>
      <p>
        Consumers buy fresh produce directly from farmers,
        eliminating middlemen and ensuring fair prices.
      </p>
    </div>

    <div class="feature-card reveal">
      <div class="icon">🔗</div>
      <h3>Blockchain Transparency</h3>
      <p>
        Every transaction is immutable and verifiable,
        ensuring trust, traceability, and accountability.
      </p>
    </div>

    <div class="feature-card reveal">
      <div class="icon">👛</div>
      <h3>Wallet Payments</h3>
      <p>
        Secure and instant payments using MetaMask
        and blockchain-based wallets.
      </p>
    </div>

  </div>
</section>

<!-- ================= CTA ================= -->
<section class="cta-section reveal">
  <h2>Transform Agriculture Today</h2>
  <p>
    Join AgriChain and become part of a transparent,
    farmer-empowering ecosystem.
  </p>

  <a href="auth/signup.php" class="btn-main">Create Free Account</a>
</section>

<!-- ================= FOOTER ================= -->
<footer class="footer">
  © 2026 AgriChain | Blockchain-Powered Agriculture
</footer>

<!-- JS -->
<script src="assets/js/animations.js"></script>

</body>
</html>
