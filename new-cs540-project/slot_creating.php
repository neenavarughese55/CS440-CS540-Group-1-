<?php
    require 'include/session_check.php';
    // Show up all PHP errors for debugging:
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

?>


<!DOCTYPE html>
<html>
<head>
    <title>Create Appointment Slot</title>
    <link rel="stylesheet" href="./css/slot_creating.css">
    <link rel="stylesheet" href="./css/header.css">
    <script src = "./js/slot_creating.js"></script>
</head>
<body>
    <?php require 'include/header.php'; ?>
    
        <?php
            if (isset($_SESSION['slot_message'])) {
                echo "<span class='errorMsg'>";
                echo $_SESSION['slot_message'];
                echo "</span>";
                unset($_SESSION['slot_message']);
            }
        ?>

    <h2 style="text-align: center;">Create Appointment Slot</h2>
    <form method="post" action="./backend/slot_creating.php">

        <label for="start-date">Start Date:</label>
        <input type="date" id="start-date" name="start-date" class="date-input" required><br>

        <label for="start-time">Start Time:</label>
        <input type="time" id="start-time" name="start-time" required><br>

        <label for="end-date">End Date:</label>
        <input type="date" id="end-date" name="end-date" required><br>

        <label for="end-time">End Time:</label>
        <input type="time" id="end-time" name="end-time" required><br>

        <label for="note">Note:</label>
        <textarea id="notes" name="notes" rows="5" cols="40" placeholder="Enter any notes here..."></textarea><br>

        <input type="submit" value="Create">
    </form>
</body>
</html>


