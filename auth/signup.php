<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Signup | AgriChain</title>
  <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

<div class="auth-container">
  <div class="auth-card">

    <!-- BRAND -->
    <div class="brand">
      <h1>🌾 AgriChain</h1>
    </div>

    <!-- LOGIN ID NOTIFICATION -->
    <?php if (isset($_SESSION['new_login_id'])): ?>
      <div class="login-alert">
        <strong>Account Created 🎉</strong>
        <p>Your Login ID</p>
        <div class="login-id-box">
          <?= htmlspecialchars($_SESSION['new_login_id']); ?>
        </div>
        <small>Please save this ID. You will need it to login.</small>
      </div>
      <?php unset($_SESSION['new_login_id']); ?>
    <?php endif; ?>

    <h2>Create Account</h2>
    <p class="subtitle">Only Farmers and Consumers can register</p>

    <form action="signup_action.php" method="POST">

      <input type="text" name="name" placeholder="Full Name" required>

      <!-- ROLE SELECTION (ADMIN NOT ALLOWED) -->
      <div class="role-select">

        <label class="role-card">
          <input type="radio" name="role" value="farmer" required>
          <span>🌾 Farmer</span>
        </label>

        <label class="role-card">
          <input type="radio" name="role" value="consumer" required>
          <span>🛒 Consumer</span>
        </label>

      </div>

      <input type="password" name="password" placeholder="Password" required>

      <button type="submit" class="btn-primary">
        Create Account
      </button>
    </form>

    <p class="switch">
      Already have an account?
      <a href="login.php">Login</a>
    </p>

  </div>
</div>

</body>
</html>
