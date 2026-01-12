<?php

require_once __DIR__. '/backend/bootstrap.php';
$username = $_SESSION['username'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cloud Services Home</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<?php include __DIR__ . '/includes/logout_nav.php'; ?>
<?php include __DIR__ . '/includes/hero.php'; ?>
<?php include __DIR__ . '/includes/services.php'; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="assets/js/scripts.js"></script>
</body>
</html>