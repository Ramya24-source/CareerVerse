<?php
// feedback.php (single file: PHP + HTML + CSS)
session_start();

// CSRF token
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "rama_auth"; // your existing database

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic CSRF check
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        $error = "Invalid request. Please try again.";
    } else {
        // Honeypot (hidden field to deter bots)
        if (!empty($_POST['website'])) {
            $error = "Spam detected.";
        } else {
            // Validate inputs
            $name       = trim($_POST['name'] ?? "");
            $email      = trim($_POST['email'] ?? "");
            $suggestion = trim($_POST['suggestion'] ?? "");

            if ($name === "" || $email === "" || $suggestion === "") {
                $error = "All fields are required.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Please enter a valid email address.";
            } elseif (mb_strlen($name) > 100) {
                $error = "Name is too long.";
            } else {
                // Store to DB
                $mysqli = @new mysqli($host, $user, $pass, $db);
                if ($mysqli->connect_error) {
                    $error = "Database connection failed.";
                } else {
                    $mysqli->set_charset('utf8mb4');
                    $stmt = $mysqli->prepare("INSERT INTO feedback (name, email, suggestion) VALUES (?, ?, ?)");
                    if (!$stmt) {
                        $error = "Server error. Please try later.";
                    } else {
                        $stmt->bind_param("sss", $name, $email, $suggestion);
                        if ($stmt->execute()) {
                            $success = "Thank you! Your feedback has been received.";
                            // Reset form token (optional)
                            $_SESSION['csrf'] = bin2hex(random_bytes(32));
                        } else {
                            $error = "Could not save your feedback. Please try again.";
                        }
                        $stmt->close();
                    }
                    $mysqli->close();
                }
            }
        }
    }
}

// Helper function to safely output HTML
function h($v) { return htmlspecialchars($v ?? "", ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CareerPro | Feedback</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{
--primary:#1a1a2e;--secondary:#16213e;
--accent1:#00c6ff;--accent2:#0072ff;--accent3:#ff6b6b;
--text:#e6e6e6;--card-bg:rgba(255,255,255,0.05);
--transition:all .4s cubic-bezier(0.175,0.885,0.32,1.275);
--gradient:linear-gradient(90deg,var(--accent1) 0%,var(--accent2) 100%);
}
body{
background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);
color:var(--text);min-height:100vh;font-family:'Poppins',sans-serif;
line-height:1.6;display:flex;flex-direction:column;
}
.container{max-width:1200px;margin:0 auto;padding:0 20px;}
header{
padding:20px 0;background:rgba(22,33,62,.95);
backdrop-filter:blur(10px);
box-shadow:0 5px 20px rgba(0,0,0,.2);
position:sticky;top:0;z-index:1000;
}
.nav-row{display:flex;justify-content:space-between;align-items:center;}
.logo{display:flex;align-items:center;gap:10px;color:#fff;text-decoration:none;font-size:24px;font-weight:700;}
.logo .logo-icon{color:var(--accent1);font-size:28px;}
.back-link{color:var(--text);text-decoration:none;font-weight:600;border:2px solid rgba(255,255,255,.2);padding:8px 14px;border-radius:10px;transition:var(--transition);}
.back-link:hover{color:#fff;border-color:var(--accent1);transform:translateY(-2px);}
.page-hero{padding:60px 0 20px;text-align:center;}
.page-hero h1{font-size:2.4rem;margin-bottom:10px;background:var(--gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.page-hero p{color:#cfcfcf;max-width:800px;margin:0 auto;}
.card{
background:var(--card-bg);border:1px solid rgba(255,255,255,.1);
backdrop-filter:blur(10px);border-radius:20px;padding:30px;
max-width:720px;margin:30px auto 60px;
box-shadow:0 20px 40px rgba(0,0,0,.25);
}
.alert{padding:14px 16px;border-radius:12px;margin-bottom:20px;font-weight:600;}
.alert-success{background:rgba(46,196,182,.12);border-left:4px solid #2ec4b6;color:#c9fff7;}
.alert-error{background:rgba(255,107,107,.12);border-left:4px solid var(--accent3);color:#ffd7d7;}
.feedback-form{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
.feedback-form .full{grid-column:1/-1;}
.input{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);color:#fff;border-radius:12px;padding:14px 16px;font-size:16px;width:100%;transition:var(--transition);}
.input:focus{outline:none;border-color:var(--accent1);box-shadow:0 0 0 3px rgba(0,198,255,.15);}
.input::placeholder{color:#cfcfcf;}
textarea.input{min-height:150px;resize:vertical;}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:14px 26px;border:none;border-radius:50px;cursor:pointer;font-weight:700;background:var(--gradient);color:#fff;box-shadow:0 6px 20px rgba(0,198,255,.25);transition:var(--transition);}
.btn:hover{transform:translateY(-2px);box-shadow:0 12px 26px rgba(0,198,255,.35);}
.btn:active{transform:translateY(0);}
.muted{color:#bdbdbd;font-size:.9rem;margin-top:10px;}
footer{text-align:center;color:#cfcfcf;padding:30px 0 50px;}
@media (max-width:768px){.feedback-form{grid-template-columns:1fr;}}
</style>
</head>
<body>
<header>
<div class="container nav-row">
<a href="index.html" class="logo"><i class="fas fa-brain logo-icon"></i><span>CareerPro</span></a>
<a href="index.html" class="back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>
</div>
</header>

<section class="page-hero">
<div class="container">
<h1>We Value Your Feedback</h1>
<p>Tell us what you love, what could be better, or any ideas you’d like to see. Your input helps us improve CareerPro for everyone.</p>
</div>
</section>

<div class="container">
<div class="card">
<?php if ($success): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo h($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo h($error); ?></div>
<?php endif; ?>

<form method="POST" class="feedback-form" autocomplete="off" novalidate>
<input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf']); ?>">
<input type="text" name="website" value="" style="display:none !important; visibility:hidden;" tabindex="-1" autocomplete="off">

<div>
<label for="name" class="muted">Name</label>
<input class="input" type="text" id="name" name="name" placeholder="Your Name" required maxlength="100">
</div>

<div>
<label for="email" class="muted">Email</label>
<input class="input" type="email" id="email" name="email" placeholder="your@email.com" required>
</div>

<div class="full">
<label for="suggestion" class="muted">Suggestions / Feedback</label>
<textarea class="input" id="suggestion" name="suggestion" placeholder="Share your thoughts..." required></textarea>
</div>

<div class="full" style="display:flex; gap:12px; align-items:center; justify-content:flex-start; margin-top:4px;">
<button type="submit" class="btn"><i class="fas fa-paper-plane"></i> Send Feedback</button>
<a href="index.html" class="back-link">Cancel</a>
</div>
</form>

<p class="muted">We read every message. If a reply is needed, we’ll reach out to the email you provided.</p>
</div>
</div>

<footer>
<div class="container">
&copy; <?php echo date('Y'); ?> CareerPro. All rights reserved.
</div>
</footer>
</body>
</html>
