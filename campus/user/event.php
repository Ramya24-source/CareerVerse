<?php include '../config.php';
$id = $_GET['id'];
$e = mysqli_fetch_assoc(mysqli_query($conn, "SELECT e.*, c.name AS catname FROM events e JOIN categories c ON e.category_id=c.id WHERE e.id='$id'"));
?>
<!DOCTYPE html>
<html>
<head><title><?php echo $e['title']; ?></title></head>
<body>
<h2><?php echo $e['title']; ?></h2>
<img src="../admin/uploads/<?php echo $e['image']; ?>" width="300"><br><br>
<p><b>Category:</b> <?php echo $e['catname']; ?></p>
<p><b>Date:</b> <?php echo $e['event_date']; ?></p>
<p><?php echo nl2br($e['description']); ?></p>
<a href="<?php echo $e['registration_link']; ?>" target="_blank">Go to Registration</a>
</body>
</html>
