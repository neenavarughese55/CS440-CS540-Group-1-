<?php
    // Ensure the user is logged in before accessing this page.
    // session_check.php handles authentication and redirection for unauthorized users.
    require 'include/session_check.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Appointment Slot</title>

    <!-- Styles for slot creation form -->
    <link rel="stylesheet" href="./css/slot_creating.css">

    <!-- Shared header styling (navigation bar etc.) -->
    <link rel="stylesheet" href="./css/header.css">

    <!-- JavaScript for validating slot creation form -->
    <script src="./js/slot_creating.js"></script>
</head>

<body>

    <!-- Include navigation header -->
    <?php require 'include/header.php'; ?>
    
    <?php
        // Display any session message related to slot creation
        if (isset($_SESSION['slot_message'])) {
            echo "<span class='errorMsg'>";
            echo $_SESSION['slot_message'];
            echo "</span>";

            // Remove message so it doesn't show again on refresh
            unset($_SESSION['slot_message']);
        }
    ?>

    <!-- Page title -->
    <h2 style="text-align: center;">Create Appointment Slot</h2>

    <!-- ==================== SLOT CREATION FORM ==================== -->
    <!-- When submitted, the form sends data to backend/slot_creating.php -->
    <form method="post" action="./backend/slot_creating.php">

        <!-- Start Date -->
        <label for="start-date">Start Date:</label>
        <input type="date"
               id="start-date"
               name="start-date"
               class="date-input"
               required>
        <br>

        <!-- Start Time -->
        <label for="start-time">Start Time:</label>
        <input type="time"
               id="start-time"
               name="start-time"
               required>
        <br>

        <!-- End Date -->
        <label for="end-date">End Date:</label>
        <input type="date"
               id="end-date"
               name="end-date"
               required>
        <br>

        <!-- End Time -->
        <label for="end-time">End Time:</label>
        <input type="time"
               id="end-time"
               name="end-time"
               required>
        <br>

        <!-- Note field (optional) -->
        <label for="note">Note:</label>
        <textarea id="notes"
                  name="notes"
                  rows="5"
                  cols="40"
                  placeholder="Enter any notes here..."></textarea>
        <br>

        <!-- Submit button -->
        <input type="submit" value="Create">
    </form>

</body>
</html>
