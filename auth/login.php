<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | AgriChain</title>
  <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

<div class="auth-container">
  <div class="auth-card">

    <!-- BRAND -->
    <div class="brand">
      <h1>🌾 AgriChain</h1>
    </div>

    <h2>Welcome Back</h2>
    <p class="subtitle">Login using your system-generated ID</p>

    <form method="POST" action="/agrichain/auth/login_action.php">

      <input
        type="text"
        name="login_id"
        placeholder="Login ID (e.g. FARM-2026-0001)"
        required
      >

      <input
        type="password"
        name="password"
        placeholder="Password"
        required
      >

      <button type="submit" class="btn-primary">
        Login
      </button>
    </form>

    <p class="switch">
      New user?
      <a href="signup.php">Create Account</a>
    </p>

  </div>
</div>

</body>
</html>
