<!-- ===== Header ===== -->

<?php
  // Ensure a session is active before accessing session variables.
  // If the session is not started yet, start it now.
  if (session_status() === PHP_SESSION_NONE) {
    session_start();

    // If session was just started and no user info exists,
    // redirect user to the project home (public welcome page).
    // This prevents unauthorized access to internal pages.
    header("Location: http://localhost/cs540project/");
    exit();
  }
?>


<header class="header">
  <div class="container">
    <nav class="nav">
      <div class="logo"><a href="homepage.php">Let's Book</a></div>

      <!-- Navigation Menu -->
      <ul>

        <!-- Home link (visible for all logged-in users) -->
        <li><a href="homepage.php">Home</a></li>

        <!-- ===============================
             Booking link (HIDDEN for providers)
             Providers cannot "book"; they create slots.
             =============================== -->
        <li 
          <?php
            if ($_SESSION['user_role'] == "service-provider") {
              // Hide booking link for service providers
              echo " style='display: none;' ";
            }
          ?>>
          <a href="booking.php">Booking</a>
        </li>

        <!-- ===============================
             Slot creation link (ONLY providers)
             Customers + Admins do NOT create slots.
             =============================== -->
        <li 
          <?php
            if ($_SESSION['user_role'] != "service-provider") {
              // Hide slot creation for non-providers
              echo " style='display: none;' ";
            }
          ?>>
          <a href="slot_creating.php">Create Slot</a>
        </li>

        <!-- ===============================
             View Appointments (label changes by role)
             Admin → "View All Appointments"
             Providers → "View My Appointment Slots"
             Customers → "View My Appointments"
             =============================== -->
        <li>
          <a href="view-appointments.php">
            <?php
              if ($_SESSION['user_role'] == "admin") {
                echo "View All Appointments";
              } 
              else if ($_SESSION['user_role'] == "service-provider") {
                echo "View My Appointment Slots";
              } 
              else {
                echo "View My Appointments";
              }
            ?>
          </a>
        </li>

        <!-- ===============================
             Admin-only link: Run Report
             Hidden for everyone except admins
             =============================== -->
        <li 
          <?php
            if ($_SESSION['user_role'] != "admin") {
              echo " style='display: none;' ";
            }
          ?>>
          <a href="report.php">Run Report</a>
        </li>

        <!-- ===============================
             Admin-only link: Account Manager
             Manage all user accounts
             =============================== -->
        <li 
          <?php
            if ($_SESSION['user_role'] != "admin") {
              echo " style='display: none;' ";
            }
          ?>>
          <a href="account_manager.php">Account Manager</a>
        </li>

        <!-- Notification center (visible to all logged-in users) -->
        <li><a href="notifications.php">Notification</a></li>

        <!-- User account settings page (visible to all logged-in users) -->
        <li><a href="setting.php">My Account</a></li>

        <!-- User Manual (visible to all users) -->
        <li><a href="user_manual.php">User Manual</a></li>

      </ul>
    </nav>
  </div>
</header>
