<?php
  session_start();
?>

<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Login Successful</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="./css/homepage.css">
</head>

<body>

<?php require 'include/header.php'; ?>

<!-- ===== Main Content ===== -->
<main class="main">
  <div class="card">
    <h2>Login Successful!</h2>
    <p>You are now logged in. You can start booking or manage your account.</p>

    <a href="booking.php" class="btn">Booking</a>
  </div>
</main>

<!-- ===== Footer ===== -->
<footer class="footer">
  <div class="container">
    <p>&copy; 2025 UW-La Crosse CS 540 Project · All rights reserved · </p>
  </div>
</footer>
</body>
</html>