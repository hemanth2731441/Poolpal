<form action="check-otp.php" method="POST">
  <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email']); ?>">
  <input type="text" name="otp" required placeholder="Enter OTP">
  <button type="submit">Verify OTP</button>
</form>
