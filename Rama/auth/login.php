<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="box">
    <h2>Login</h2>
    <?php if (isset($_GET['error'])) echo "<p class='error'>".$_GET['error']."</p>"; ?>
    <form action="authenticate.php" method="post">
      <input type="email" name="email" placeholder="Enter Email" required>
      <input type="password" name="password" placeholder="Enter Password" required>
      <button type="submit" class="btn">Login</button>
    </form>
    <a href="signup.php" class="link">Don’t have an account? Sign Up</a>
  </div>
</body>
</html>
