<!DOCTYPE html>
<html>
<head>
  <title>Signup</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="box">
    <h2>Create Account</h2>
    <?php if (isset($_GET['error'])) echo "<p class='error'>".$_GET['error']."</p>"; ?>
    <form action="register.php" method="post">
      <input type="text" name="username" placeholder="Enter Username" required>
      <input type="email" name="email" placeholder="Enter Email" required>
      <input type="password" name="password" placeholder="Enter Password" required>
      <button type="submit" class="btn">Sign Up</button>
    </form>
    <a href="login.php" class="link">Already have an account? Login</a>
  </div>
</body>
</html>
