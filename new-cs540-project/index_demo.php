<?php
  require 'include/session_check.php';

  // Show up all PHP errors for debugging:
  error_reporting(E_ALL);
  ini_set('display_errors', '1');
?>


<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>CS 540 Project</title>

    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <link rel="stylesheet" href="./css/file.css">

    <!-- Load jQuery from CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./js/index.js"></script>

  </head>


  <body>
    <div class="card">
      <div class="tabs">
        <button id="loginTab" class="active" onclick="switchPanel('login')">Login</button>
        <button id="regTab" onclick="switchPanel('reg')">Register</button>
      </div>

      <!-- Login Panel -->
      <div id="loginPanel" class="panel active">
        <!-- Warning message for when Login panel is active -->
        <span class="errorMsg">
          <?php
            if (isset($_SESSION['message'])) {
              echo $_SESSION['message'];
              unset($_SESSION['message']);
            }
          ?>
        </span>

        <form id="loginForm" action="./backend/login.php" method="post">
          <input type="hidden" name="action" value="login" />
          <div class="form-group">
            <label>Username</label>
            <input type="username" name="username" value="" id="username-login" required />
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" minlength="6" required />
          </div>
          <button type="submit">Login</button>
        </form>
      </div>
      <!-- End Login Panel -->

      <!-- Registration Panel -->
      <div id="regPanel" class="panel">
        <span class="errorMsg">
          <?php
            if (isset($_SESSION['message'])) {
              echo $_SESSION['message'];
              unset($_SESSION['message']);
            }
          ?>
        </span>

        <form id="registerForm" action="./backend/register.php" method="post">
          <input type="hidden" name="action" value="register" />
          <div class="form-group">
            <label>Username</label>
            <input type="username" name="registered-username" id="registered-username" required />
          </div>

          <div class="form-group">
            <label>Email</label>
            <input type="email" name="registered-email" id="registered-email" />
          </div>

          <div class="form-group">
            <label>Register As</label>
            <select name="role" id="role">
              <option value = "customer">Customer</option>
              <option value = "service-provider">Service Provider</option>
            </select>
          </div>

          <div class="form-group" id="business-name-container">
            <label>Business Name</label>
            <input type="text" name="business-name" id="business-name" />
          </div>

          <div class="form-group" id="category-container">
            <label>Category</label>
            <select name="category" id="category">
              <?php
                $conn = new mysqli("localhost", "root", "", "cs540");
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }
                $sql = "SELECT id, name FROM categories";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $id = htmlspecialchars($row["id"]);
                        $name = htmlspecialchars($row["name"]);
                        echo "<option value=\"$id\">$name</option>";
                    }
                }
                $conn->close();
              ?>
            </select>
          </div>

          <div class="form-group" id="qualifications-container">
            <label>Qualifications</label>
            <input type="file" name="qualifications" id="qualifications" />
          </div>

          <div class="form-group">
            <label>Phone Number</label>
            <input type="phonenumber" name="registered-phonenumber" id="registered-phonenumber" />
          </div>
          <div class="form-group">
            <label>Password (≥6 characters)</label>
            <input type="password" id="registered-password" name="registered-password" minlength="6" />
          </div>
          <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" id="registered-password-2" name="registered-password-2" minlength="6"/>
          </div>

          <!-- Timezone detection -->
          <input type="hidden" name="timezone" id="tz-input" value="UTC" />
          <script>
            (function() {
              try {
                const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                if (tz) document.getElementById('tz-input').value = tz;
              } catch (e) {
                // leave default UTC if detection fails
              }
            })();
          </script>
          <!-- End timezone detection -->

          <button type="submit" id="sub-btn">Create Account</button>
        </form>
      </div>
      <!-- End Registration Panel -->
    </div>
  </body>
</html>

<?php
  header("Location: index.php");
  exit();
?>
