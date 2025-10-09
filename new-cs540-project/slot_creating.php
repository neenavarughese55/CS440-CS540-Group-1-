<?php
    session_start();
    // Show up all PHP errors for debugging:
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

?>


<!DOCTYPE html>
<html>
<head>
    <title>Create Appointment Slot</title>
    <link rel="stylesheet" href="./css/booking.css">
    <link rel="stylesheet" href="./css/header.css">
    <script>
        function setMinDateTime() {
            const now = new Date();
            const dateInput = document.getElementById("date");
            const timeInput = document.getElementById("time");

            // Format date as YYYY-MM-DD
            const today = now.toISOString().split("T")[0];
            dateInput.min = today;

            // Update time only if selected date is today
            dateInput.addEventListener("change", function () {
                if (this.value === today) {
                    const hours = now.getHours().toString().padStart(2, '0');
                    const minutes = now.getMinutes().toString().padStart(2, '0');
                    timeInput.min = `${hours}:${minutes}`;
                } else {
                    timeInput.removeAttribute("min");
                }
            });
        }

        window.onload = setMinDateTime;
    </script>
</head>
<body>
    <?php require 'include/header.php'; ?>
    <span class="errorMsg">
        <?php
            if (isset($_SESSION['slot_message'])) {
                echo $_SESSION['slot_message'];
                unset($_SESSION['slot_message']);
            }
        ?>
    </span>

    <h2>Create Appointment Slot</h2>
    <form method="post" action="./backend/slot_creating.php">

        <label for="start-date">Start Date:</label><br>
        <input type="date" id="start-date" name="start-date" required><br><br>

        <label for="start-time">Start Time:</label><br>
        <input type="time" id="start-time" name="start-time" required><br><br>

        <label for="end-date">End Date:</label><br>
        <input type="date" id="end-date" name="end-date" required><br><br>

        <label for="end-time">End Time:</label><br>
        <input type="time" id="end-time" name="end-time" required><br><br>

        <label for="note">Note:</label><br>
        <textarea id="notes" name="notes" rows="5" cols="40" placeholder="Enter any notes here..."></textarea><br><br>

        <input type="submit" value="Create">
    </form>
</body>
</html>


