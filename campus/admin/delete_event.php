<?php
include 'includes/auth_check.php';
include 'includes/db.php';

$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM events WHERE id='$id'");
header("Location: manage_events.php");
exit();
?>
