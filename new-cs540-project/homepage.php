<?php
  // Ensure the user is logged in before showing this page.
  // If the session is invalid, session_check.php should redirect/block access.
  require 'include/session_check.php';
?>

<html lang="en">
<head>
  <meta charset="UTF-8">

  <!-- Ensures compatibility mode in older Internet Explorer versions -->
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <title>Login Successful</title>

  <!-- Makes the page responsive on mobile devices -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Shared header/navigation bar styling -->
  <link rel="stylesheet" href="./css/header.css">

  <!-- Styling for homepage layout and success card -->
  <link rel="stylesheet" href="./css/homepage.css">
</head>

<body>

<!-- Load the site header (navigation bar / branding) -->
<?php require 'include/header.php'; ?>

<!-- ===== Main Content Section ===== -->
<main class="main">

  <!-- Simple success message card container -->
  <div class="card">
    <h2>Login Successful!</h2>

    <!-- Message shown after successful login -->
    <p>You are now logged in. You can start booking or manage your account.</p>

    <!-- Button linking to the booking page -->
    <a href="booking.php" class="btn">Booking</a>
  </div>

</main>

<!-- ===== Footer ===== -->
<footer class="footer">
  <div class="container">
    <!-- Course/project footer message -->
    <p>&copy; 2025 UW-La Crosse CS 540 Project · All rights reserved · </p>
  </div>
</footer>

</body>
</html>
