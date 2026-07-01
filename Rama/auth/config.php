<?php
$host = "localhost";
$user = "root";     // default in XAMPP
$pass = "";         // empty password
$db   = "rama1"; // 👈 must match the DB you created

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
