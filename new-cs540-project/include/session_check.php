<?php
  // Start session if not already started
  if (session_status() === PHP_SESSION_NONE) {
      session_start();
  }

  // Check if user session is set (e.g., 'user_id' or 'logged_in')
  if (!isset($_SESSION['user_id'])) {
      // Redirect to login page
      header("Location: http://localhost/cs540project/");
      exit();
  }
?>