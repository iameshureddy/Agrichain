<?php
session_start();

/* If already logged in, redirect to dashboard */
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login | AgriChain</title>
  <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>

<div class="login-wrapper">
  <div class="login-card">

    <div class="login-header">
      <h1>🌾 AgriChain</h1>
      <p>Administrator Login</p>
    </div>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert error">Invalid email or password</div>
    <?php endif; ?>

    <?php if (isset($_GET['timeout'])): ?>
      <div class="alert error">Session expired. Please login again.</div>
    <?php endif; ?>

    <form method="POST" action="/agrichain/admin/login_action.php">

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" placeholder="admin@agrichain.com" required>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>

      <button type="submit" class="login-btn">Login</button>
    </form>

    <div class="login-footer">
      © <?php echo date('Y'); ?> AgriChain Admin Panel
    </div>

  </div>
</div>

</body>
</html>
