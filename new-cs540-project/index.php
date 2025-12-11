<?php
  // Start or resume the current session so we can store/read login messages,
  // authentication status, and other user session data.
  session_start();

  // Show all PHP errors on-screen (for debugging purposes only).
  // IMPORTANT: Disable this in production for security.
  error_reporting(E_ALL);
  ini_set('display_errors', '1');
?>


<html lang="en">
  <head>
    <!-- Standard metadata -->
    <meta charset="UTF-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />

    <title>Let's Book</title>

    <!-- Makes layout adapt to mobile screens -->
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <!-- Page styling -->
    <link rel="stylesheet" href="./css/index.css">

    <!-- Load jQuery from CDN (used by index.js) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom page JavaScript for switching login/register panels -->
    <script src="./js/index.js"></script>

  </head>

  <body>
    <!-- Main login/register container card -->
    <div class="card">

      <!-- Tabs for switching between Login and Register panels -->
      <div class="tabs">
        <!-- Login tab is active by default -->
        <button id="loginTab" class="active" onclick="switchPanel('login')">Login</button>

        <!-- Registration tab -->
        <button id="regTab" onclick="switchPanel('reg')">Register</button>
      </div>

      <!-- ======================= LOGIN PANEL ======================= -->
      <div id="loginPanel" class="panel active">

        <!-- Display login error/success messages stored in the session -->
        <span class="errorMsg">
          <?php
            if (isset($_SESSION['message'])) {
              echo $_SESSION['message'];
              unset($_SESSION['message']);   // Remove so it shows only once
            }
          ?>
        </span>

        <!-- Login form -->
        <form id="loginForm" action="./backend/login.php" method="post">

          <!-- Hidden action flag so login.php knows which action is being performed -->
          <input type="hidden" name="action" value="login" />

          <!-- Username input -->
          <div class="form-group">
            <label>Username</label>
            <input type="username" name="username" value="" id="username-login" required />
          </div>

          <!-- Password input -->
          <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" minlength="6" required />
          </div>

          <!-- Submit login -->
          <button type="submit">Login</button>
        </form>
      </div>
      <!-- ======================= END LOGIN PANEL ======================= -->


      <!-- ======================= REGISTRATION PANEL ======================= -->
      <div id="regPanel" class="panel">

        <!-- Registration errors/success display -->
        <span class="errorMsg">
          <?php
            if (isset($_SESSION['message'])) {
              echo $_SESSION['message'];
              unset($_SESSION['message']);
            }
          ?>
        </span>

        <!-- Registration form -->
        <form id="registerForm" action="./backend/register.php" method="post">
          
          <!-- Hidden input telling register.php what action is being performed -->
          <input type="hidden" name="action" value="register" />

          <!-- Username input -->
          <div class="form-group">
            <label>Username</label>
            <input type="username" name="registered-username" id="registered-username" required />
          </div>

          <!-- Email input -->
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="registered-email" id="registered-email" />
          </div>

          <!-- Select user role -->
          <div class="form-group">
            <label>Register As</label>
            <select name="role" id="role">
              <option value="customer">Customer</option>
              <option value="service-provider">Service Provider</option>
            </select>
          </div>

          <!-- Business name only shown when role = service-provider -->
          <div class="form-group" id="business-name-container">
            <label>Business Name</label>
            <input type="text" name="business-name" id="business-name" />
          </div>

          <!-- Category dropdown populated from database -->
          <div class="form-group" id="category-container">
            <label>Category</label>
            <select name="category" id="category">
              <?php
                // Connect to MySQL database
                $conn = new mysqli("localhost", "root", "", "cs540");

                // Stop execution if connection fails
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Get list of categories for service providers
                $sql = "SELECT id, name FROM categories";
                $result = $conn->query($sql);

                // Output options
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $id   = htmlspecialchars($row["id"]);
                        $name = htmlspecialchars($row["name"]);
                        echo "<option value=\"$id\">$name</option>";
                    }
                }

                // Close DB connection
                $conn->close();
              ?>
            </select>
          </div>

          <!-- File upload for provider qualifications -->
          <div class="form-group" id="qualifications-container">
            <label>Qualifications</label>
            <input type="file" name="qualifications" id="qualifications" />
          </div>

          <!-- Password creation -->
          <div class="form-group">
            <label>Password (≥6 characters)</label>
            <input type="password" id="registered-password" name="registered-password" minlength="6" />
          </div>

          <!-- Confirm password -->
          <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" id="registered-password-2" name="registered-password-2" minlength="6" />
          </div>

          <!-- Automatically detect user's timezone -->
          <input type="hidden" name="timezone" id="tz-input" value="UTC" />
          <script>
            (function() {
              try {
                // Use browser API to detect timezone
                const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                if (tz) document.getElementById('tz-input').value = tz;
              } catch (e) {
                // If detection fails, keep UTC default
              }
            })();
          </script>

          <!-- Submit registration -->
          <button type="submit" id="sub-btn">Create Account</button>
        </form>
      </div>
      <!-- ======================= END REGISTRATION PANEL ======================= -->

    </div> <!-- End main card -->
  </body>
</html>
