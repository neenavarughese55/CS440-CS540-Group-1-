<!-- ===== Header ===== -->
<?php
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
?>

<header class="header">
  <div class="container">
    <nav class="nav">
      <div class="logo"><a href="homepage.php">Let's Book</a></div>
      <ul>
        <li><a href="homepage.php">Home</a></li>
        <li><a href="booking.php">Booking</a></li>
        <li 
          <?php 
            
            if ($_SESSION['user_role'] == "customer") {
              echo " style = 'display: none;' ";
            }
          ?>><a href="slot_creating.php">Create Slot</a></li>
        <!-- <li><a href="setting.php">Setting</a></li> -->
        <li><a href="backend/logout.php">Logout</a></li>
      </ul>
    </nav>
  </div>
</header>