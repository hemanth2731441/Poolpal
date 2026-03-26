<?php
session_start();
if (isset($_SESSION['admin_id'])) {
  header("Location: admin_panel.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Login - PoolPal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #f6f8fd 0%, #f1f4f9 100%);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .admin-login-container {
      background: #ffffff;
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
      width: 100%;
      max-width: 420px;
      position: relative;
      overflow: hidden;
    }

    .admin-login-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #ffbf00, #ffd700);
    }

    .logo-section {
      text-align: center;
      margin-bottom: 35px;
    }

    .logo-section h2 {
      color: #1a1a1a;
      font-size: 28px;
      font-weight: 600;
      margin-bottom: 8px;
    }

    .logo-section p {
      color: #666;
      font-size: 15px;
    }

    .input-group {
      margin-bottom: 24px;
      position: relative;
    }

    .input-group label {
      display: block;
      font-size: 14px;
      color: #444;
      margin-bottom: 8px;
      font-weight: 500;
    }

    .input-group .input-wrapper {
      position: relative;
    }

    .input-group input {
      width: 100%;
      padding: 14px 45px 14px 16px;
      border-radius: 12px;
      border: 1.5px solid #e0e0e0;
      background: #ffffff;
      font-size: 15px;
      color: #333;
      transition: all 0.3s ease;
    }

    .input-group input:focus {
      border-color: #ffbf00;
      outline: none;
      box-shadow: 0 0 0 3px rgba(255, 191, 0, 0.1);
    }

    .input-group i {
      position: absolute;
      right: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: #888;
      font-size: 18px;
    }

    .login-btn {
      width: 100%;
      padding: 14px;
      background: linear-gradient(90deg, #ffbf00, #ffd700);
      color: #1a1a1a;
      border: none;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 10px;
    }

    .login-btn:hover {
      background: linear-gradient(90deg, #ffa000, #ffbf00);
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(255, 191, 0, 0.2);
    }

    .login-btn:active {
      transform: translateY(0);
    }

    .error-msg {
      background: #fff1f0;
      border: 1px solid #ffccc7;
      color: #cf1322;
      padding: 12px;
      border-radius: 8px;
      font-size: 14px;
      margin-top: 16px;
      text-align: center;
    }

    @media (max-width: 480px) {
      .admin-login-container {
        padding: 30px 20px;
      }

      .logo-section h2 {
        font-size: 24px;
      }
    }
  </style>
  </head>
<body>
<div class="main-content">

<div class="admin-login-container">
  <div class="logo-section">
    <h2>Welcome Back</h2>
    <p>Sign in to your admin account</p>
  </div>
  <form action="admin_login_action.php" method="POST">
    <div class="input-group">
      <label>Email Address</label>
      <div class="input-wrapper">
        <input type="email" name="email" placeholder="Enter your email" required />
        <i class="fas fa-envelope"></i>
      </div>
    </div>
    <div class="input-group">
      <label>Password</label>
      <div class="input-wrapper">
        <input type="password" name="password" placeholder="Enter your password" required />
        <i class="fas fa-lock"></i>
      </div>
    </div>
    <button type="submit" class="login-btn">Sign In</button>

    <?php if (isset($_GET['error'])): ?>
      <div class="error-msg">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo htmlspecialchars($_GET['error']); ?>
      </div>
    <?php endif; ?>
  </form>
</div>

</div>
</body>
</html>
