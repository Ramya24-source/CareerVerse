<?php
session_start();

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "rama1";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: auth.php");
    exit();
}

// Handle signup
if (isset($_POST['signup'])) {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $hashed   = password_hash($password, PASSWORD_DEFAULT);

    // Input validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = "Email already registered!";
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed);
            
            if ($stmt->execute()) {
                $_SESSION['username'] = $username;
                header("Location: auth.php");
                exit();
            } else {
                $error = "Signup failed! Please try again.";
            }
        }
        $stmt->close();
    }
}

// Handle login
if (isset($_POST['login'])) {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Email and password are required!";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['username'] = $row['username'];
                header("Location: auth.php");
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "No account found with that email!";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CareerVerse | Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #006CE4; /* Power BI blue */
      --secondary: #323348; /* Power BI dark blue */
      --accent: #00B8A2; /* Power BI teal */
      --success: #4cc9f0;
      --danger: #FF4F4F;
      --warning: #FFB74D;
      --light: #f8f9fa;
      --dark: #212529;
      --gray: #6c757d;
      --transition: all 0.3s ease;
      --shadow: 0 10px 30px rgba(0, 108, 228, 0.15);
      --radius: 12px;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      color: var(--secondary);
    }
    
    .container, .welcome-container {
      background: white;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      width: 100%;
      max-width: 450px;
      padding: 40px;
      position: relative;
      overflow: hidden;
       gap: 15px; 
    }
    
    .welcome-container {
      max-width: 600px;
      text-align: center;
      gap: 15px; 
    }
    
    .logo {
      text-align: center;
      margin-bottom: 25px;
    }
    
    .logo i {
      font-size: 42px;
      color: var(--primary);
      background: rgba(0, 108, 228, 0.1);
      width: 80px;
      height: 80px;
      line-height: 80px;
      border-radius: 50%;
      margin-bottom: 15px;
    }
    
    h1 {
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 10px;
      color: var(--secondary);
      text-align: center;
    }
    
    p {
      text-align: center;
      margin-bottom: 25px;
      color: var(--gray);
      font-size: 15px;
    }
    
    .tabs {
      display: flex;
      background: #f6f8fc;
      border-radius: 8px;
      padding: 5px;
      margin-bottom: 25px;
    }
    
    .tab {
      flex: 1;
      text-align: center;
      padding: 12px;
      cursor: pointer;
      border-radius: 6px;
      font-weight: 500;
      transition: var(--transition);
    }
    
    .tab.active {
      background: white;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
      color: var(--primary);
    }
    
    .form {
      display: none;
    }
    
    .form.active {
      display: block;
      animation: fadeIn 0.5s ease;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .input-group {
      position: relative;
      margin-bottom: 20px;
    }
    
    .input-group i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--gray);
      font-size: 16px;
    }
    
    .input-group input {
      width: 100%;
      padding: 15px 15px 15px 45px;
      border: 1px solid #e1e5eb;
      border-radius: 8px;
      font-size: 15px;
      transition: var(--transition);
      background: #fafbfd;
    }
    
    .input-group input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(0, 108, 228, 0.2);
      background: white;
    }
    
   .btn {
  display: block;       /* Ensures margin works properly */
  width: 100%;
  padding: 15px;
  border: none;
  border-radius: 8px;
  background: var(--primary);
  color: white;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
  margin: 15px 0; /* Adds spacing above and below buttons */
}
    
    .btn:hover {
      background: #0056b3;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 108, 228, 0.3);
    }
    
    .dashboard-btn {
      background: var(--accent);
      margin-bottom: 20px;
    }
    
    .dashboard-btn:hover {
      background: #009680;
    }
    
    .logout-btn {
      background: var(--danger);
    }
    
    .logout-btn:hover {
      background: #e04444;
    }
    
    .error {
      background: rgba(255, 79, 79, 0.1);
      border: 1px solid rgba(255, 79, 79, 0.2);
      color: var(--danger);
      padding: 12px 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
      display: flex;
      align-items: center;
    }
    
    .error i {
      margin-right: 10px;
    }
    
    .powerbi-demo {
      background: #f8f9fc;
      border-radius: var(--radius);
      padding: 25px;
      margin: 30px 0;
      text-align: left;
    }
    
    .powerbi-demo h3 {
      color: var(--secondary);
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      font-size: 18px;
    }
    
    .powerbi-demo h3 i {
      color: var(--primary);
      margin-right: 10px;
      font-size: 20px;
    }
    
    .powerbi-demo p {
      text-align: left;
      margin-bottom: 15px;
      font-size: 14px;
    }
    
    .demo-chart {
      height: 200px;
      background: white;
      border-radius: 8px;
      padding: 15px;
      margin: 20px 0;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    .bars {
      display: flex;
      align-items: flex-end;
      height: 100%;
      gap: 15px;
      padding: 0 10px;
    }
    
    .bar {
      flex: 1;
      background: linear-gradient(to top, var(--primary), #4a92ff);
      border-radius: 4px 4px 0 0;
      height: 40%;
      position: relative;
      animation: grow 1.5s ease-in-out;
    }
    
    .bar:nth-child(2) {
      height: 70%;
      background: linear-gradient(to top, var(--accent), #00d6bd);
    }
    
    .bar:nth-child(3) {
      height: 90%;
      background: linear-gradient(to top, #ffb74d, #ffd699);
    }
    
    .bar:nth-child(4) {
      height: 60%;
      background: linear-gradient(to top, #9c27b0, #d16ee5);
    }
    
    .bar:nth-child(5) {
      height: 30%;
      background: linear-gradient(to top, var(--danger), #ff7a7a);
    }
    
    @keyframes grow {
      from { height: 0; }
    }
    
    .bar::after {
      content: attr(data-value);
      position: absolute;
      top: -25px;
      left: 50%;
      transform: translateX(-50%);
      font-size: 12px;
      font-weight: 600;
      color: var(--secondary);
    }
    
    .bar:nth-child(1)::after { content: "42%"; }
    .bar:nth-child(2)::after { content: "68%"; }
    .bar:nth-child(3)::after { content: "85%"; }
    .bar:nth-child(4)::after { content: "55%"; }
    .bar:nth-child(5)::after { content: "30%"; }
    
    @media (max-width: 576px) {
      .container, .welcome-container {
        padding: 25px;
      }
      
      h1 {
        font-size: 24px;
      }
    }
  </style>
</head>
<body>
  <?php if (isset($_SESSION['username'])): ?>
    <div class="welcome-container">
      <div class="logo">
        <i class="fa-solid fa-user"></i>
      </div>
      <h1>Welcome, <?= htmlspecialchars($_SESSION['username']); ?>! 🎉</h1>
      <p>You are successfully logged in to <strong>CareerVerse</strong></p>
<!--       
       <div class="powerbi-demo">
        <h3><i class="fab fa-microsoft"></i> Power BI Integration</h3>
        <p>Your analytics dashboard is ready with real-time data visualization</p>
        
        <div class="demo-chart">
          <div class="bars">
            <div class="bar" data-value="42%"></div>
            <div class="bar" data-value="68%"></div>
            <div class="bar" data-value="85%"></div>
            <div class="bar" data-value="55%"></div>
            <div class="bar" data-value="30%"></div>
          </div>
        </div>
         -->
        <!-- <p>Access your customized reports and insights to drive data-informed decisions</p>
      </div> -->
      
      <a href="http://localhost/Rama/index.html" class="btn dashboard-btn">
        <i class="fas fa-tachometer-alt"></i> Go to Dashboard
      </a>
      <a href="auth.php?logout=true" class="btn logout-btn">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  <?php else: ?>
    <div class="container">
      <div class="logo">
       <i class="fa-solid fa-user"></i>
      </div>
      <h1>CareerVerse</h1>
      <p>Sign in to access your career analytics dashboard</p>
      
      <?php if (!empty($error)): ?>
        <div class="error">
          <i class="fas fa-exclamation-circle"></i> <?= $error; ?>
        </div>
      <?php endif; ?>
      
      <div class="tabs">
        <div class="tab active" id="login-tab">Login</div>
        <div class="tab" id="signup-tab">Sign Up</div>
      </div>
      
      <div class="form-container">
        <!-- Login Form -->
        <form method="POST" class="form active" id="login-form" autocomplete="off">
          <input type="hidden" name="login" value="1">
          <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" placeholder="Email" required autocomplete="off">
          </div>
          <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Password" required autocomplete="new-password">
          </div>
          <button type="submit" class="btn">
            <i class="fas fa-sign-in-alt"></i> Login
          </button>
        </form>
        
        <!-- Signup Form -->
        <form method="POST" class="form" id="signup-form" autocomplete="off">
          <input type="hidden" name="signup" value="1">
          <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="username" placeholder="Username" required autocomplete="off">
          </div>
          <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" placeholder="Email" required autocomplete="off">
          </div>
          <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Password" required autocomplete="new-password">
          </div>
          <button type="submit" class="btn">
            <i class="fas fa-user-plus"></i> Create Account
          </button>
        </form>
      </div>
    </div>
    
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const loginTab = document.getElementById('login-tab');
        const signupTab = document.getElementById('signup-tab');
        const loginForm = document.getElementById('login-form');
        const signupForm = document.getElementById('signup-form');
        
        loginTab.addEventListener('click', function() {
          loginTab.classList.add('active');
          signupTab.classList.remove('active');
          loginForm.classList.add('active');
          signupForm.classList.remove('active');
        });
        
        signupTab.addEventListener('click', function() {
          signupTab.classList.add('active');
          loginTab.classList.remove('active');
          signupForm.classList.add('active');
          loginForm.classList.remove('active');
        });
        
        // Add animation to inputs on focus
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
          input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
          });
          
          input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
          });
        });
      });
    </script>
  <?php endif; ?>
</body>
</html>