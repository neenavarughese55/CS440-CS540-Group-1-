<!-- ===== Header ===== -->
<?php
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
    header("Location: http://localhost/cs540project/");
    exit();
  }
?>

<header class="header">
  <div class="container">
    <nav class="nav">
      <div class="logo"><a href="homepage.php">Let's Book</a></div>
      <ul>
        <li><a href="homepage.php">Home</a></li>
        <li 
          <?php
            if ($_SESSION['user_role'] == "service-provider") {
              echo " style = 'display: none;' ";
            }
          ?>><a href="booking.php">Booking</a></li>
        <li 
          <?php
            if ($_SESSION['user_role'] != "service-provider") {
              echo " style = 'display: none;' ";
            }
          ?>><a href="slot_creating.php">Create Slot</a>
        </li>
        <li><a href="view-appointments.php">
          <?php
            if ($_SESSION['user_role'] == "admin") {
              echo "View All Appointments";
            } else if ($_SESSION['user_role'] == "service-provider") {
              echo "View My Appointment Slots";
            } else {
              echo "View My Appointments";
            }
          ?>
        </a></li>
        <li><a href="report.php">Run Report</a></li>
        <li 
          <?php
            if ($_SESSION['user_role'] != "admin") {
              echo " style = 'display: none;' ";
            }
          ?>><a href="account_manager.php">Account Manager</a>
        </li>
        <li><a href="backend/logout.php">Logout</a></li>
      </ul>
    </nav>
  </div>
</header>