<?php
    session_start();
    // Show up all PHP errors for debugging:
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    
    // Handle form submission
    // if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //     $appointmentName = htmlspecialchars($_POST["appointment_name"]);
    //     $date = $_POST["date"];
    //     $time = $_POST["time"];

    //     echo "<h2>Booking Confirmed!</h2>";
    //     echo "<p><strong>Appointment:</strong> $appointmentName</p>";
    //     echo "<p><strong>Date:</strong> $date</p>";
    //     echo "<p><strong>Time:</strong> $time</p>";
    // }
?>


<!DOCTYPE html>
<html>
<head>
    <title>Booking Form</title>
    <link rel="stylesheet" href="./css/booking.css">
    <link rel="stylesheet" href="./css/header.css">
    <script>
        // function setMinDateTime() {
        //     const now = new Date();
        //     const dateInput = document.getElementById("date");
        //     const timeInput = document.getElementById("time");

        //     // Format date as YYYY-MM-DD
        //     const today = now.toISOString().split("T")[0];
        //     dateInput.min = today;

        //     // Update time only if selected date is today
        //     dateInput.addEventListener("change", function () {
        //         if (this.value === today) {
        //             const hours = now.getHours().toString().padStart(2, '0');
        //             const minutes = now.getMinutes().toString().padStart(2, '0');
        //             timeInput.min = `${hours}:${minutes}`;
        //         } else {
        //             timeInput.removeAttribute("min");
        //         }
        //     });
        // }

        // window.onload = setMinDateTime;

        // Submit form
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".book").forEach(function(button) {
                button.addEventListener("click", function() {
                    const row = this.closest("tr");

                    document.getElementById("slot_id").value = row.querySelector(".slot_id").textContent.trim();
                    document.getElementById("provider_id").value = row.querySelector(".provider_id").textContent.trim();
                    document.getElementById("business_name").value = row.querySelector(".business_name").textContent.trim();
                    document.getElementById("category_id").value = row.querySelector(".category_id").textContent.trim();
                    document.getElementById("category_name").value = row.querySelector(".category_name").textContent.trim();
                    document.getElementById("notes").value = row.querySelector(".notes").textContent.trim();
                    document.getElementById("start_time").value = row.querySelector(".start_time").textContent.trim();
                    document.getElementById("end_time").value = row.querySelector(".end_time").textContent.trim();

                    // console.log("slot_id: " + document.getElementById("slot_id").value);
                    // console.log("business_name: " + document.getElementById("business_name").value);
                    // console.log("category_id: " + document.getElementById("category_id").value);
                    // console.log("category_name: " + document.getElementById("category_name").value);
                    // console.log("notes: " + document.getElementById("notes").value);
                    // console.log("start_time: " + document.getElementById("start_time").value);
                    // console.log("end_time: " + document.getElementById("end_time").value);
                    document.getElementById("myForm").submit();
                });
            });
        });


    </script>
</head>
<body>
    <?php require 'include/header.php'; ?>

    <span class="errorMsg">
        <?php
        if (isset($_SESSION['booking_message'])) {
            echo $_SESSION['booking_message'];
            unset($_SESSION['booking_message']);
        }
        ?>
    </span>

    <div class="form-group" id="category-container" style="display: none;">
            <label>Category</label>
            <select name="category" id="category">
              <?php
                // Database connection
                $conn = new mysqli("localhost", "root", "", "cs540");

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Query provider profiles
                $sql = "SELECT id, name FROM categories";
                $result = $conn->query($sql);

                // Populate dropdown
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
          </div><br><br>

    <h4>Select Available Spots:</h4>

    <form id="myForm" method="post" action="./backend/booking.php">
        <?php
            $conn = new mysqli("localhost", "root", "", "cs540");

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $provider_profiles_id_stmt = "";

            if (isset($_SESSION['provider_profiles_id'])) {
                $provider_profiles_id = $_SESSION['provider_profiles_id'];
                $provider_profiles_id_stmt = "WHERE provider_id = " . $provider_profiles_id;
            }

            $sql = "SELECT s.id, s.provider_id, pp.business_name, c.id as category_id, c.name as category_name, s.notes, start_time, end_time
                    FROM appointment_slots s 
                    LEFT JOIN provider_profiles pp ON s.provider_id = pp.id 
                    LEFT JOIN categories c ON s.category_id = c.id " . $provider_profiles_id_stmt . 
                    " AND s.id NOT IN (SELECT slot_id FROM appointments)";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo "<table border='1' cellpadding='8' cellspacing='0'>";
                echo "<thead><tr><th>ID</th><th>Provider Name</th><th>Category</th><th>Notes</th><th>Start Time</th><th>End Time</th><th>Book</th></tr></thead>";
                echo "<tbody>";

                while ($row = $result->fetch_assoc()) {
                    $slotId = htmlspecialchars($row["id"]);
                    $providerId = htmlspecialchars($row["provider_id"]);
                    $business_name = htmlspecialchars($row["business_name"]);
                    $category_name = htmlspecialchars($row["category_name"]);
                    $notes = htmlspecialchars($row["notes"]);
                    $category_id = htmlspecialchars($row["category_id"]);
                    $start = htmlspecialchars($row["start_time"]);
                    $end = htmlspecialchars($row["end_time"]);

                    echo "<tr>
                            <td class='slot_id'>$slotId</td>
                            <td style = 'display: none;' class='provider_id'>$providerId</td>
                            <td class='business_name'>$business_name</td>
                            <td style = 'display: none;' class='category_id'>$category_id</td>
                            <td class='category_name'>$category_name</td>
                            <td class='notes'>$notes</td>
                            <td class='start_time'>$start</td>
                            <td class='end_time'>$end</td>
                            <td><button type = 'button' class='book'>Book</button></td>
                        </tr>";
                }

                echo "</tbody></table>";
            } else {
                echo "<p>No appointment slots found.</p>";
            }

            $conn->close();
        ?>

        <input type="hidden" name="slot_id" id="slot_id">
        <input type="hidden" name="provider_id" id="provider_id">
        <input type="hidden" name="business_name" id="business_name">
        <input type="hidden" name="category_id" id="category_id">
        <input type="hidden" name="category_name" id="category_name">
        <input type="hidden" name="notes" id="notes">
        <input type="hidden" name="start_time" id="start_time">
        <input type="hidden" name="end_time" id="end_time">


    <!-- <label for="provider">Select Provider:</label><br>
    <select id="provider" name="provider" required>
        <option value="">-- Please choose a provider --</option>
        <?php
            // // Database connection
            // $conn = new mysqli("localhost", "root", "", "cs540");

            // // Check connection
            // if ($conn->connect_error) {
            //     die("Connection failed: " . $conn->connect_error);
            // }

            // // Query provider profiles
            // $sql = "SELECT id, business_name FROM provider_profiles";
            // $result = $conn->query($sql);

            // // Populate dropdown
            // if ($result->num_rows > 0) {
            //     while ($row = $result->fetch_assoc()) {
            //         $providerId = htmlspecialchars($row["id"]);
            //         $providerName = htmlspecialchars($row["business_name"]);
            //         echo "<option value=\"$providerId\">$providerName</option>";
            //     }
            // } else {
            //     echo "<option disabled>No providers available</option>";
            // }

            // $conn->close();
        ?>
    </select><br><br>


        <label for="start-date">Start Date:</label><br>
        <input type="date" id="start-date" name="start-date" required><br><br>

        <label for="start-time">Start Time:</label><br>
        <input type="time" id="start-time" name="start-time" required><br><br>

        <label for="end-date">End Date:</label><br>
        <input type="date" id="end-date" name="end-date" required><br><br>

        <label for="end-time">End Time:</label><br>
        <input type="time" id="end-time" name="end-time" required><br><br>

        <label for="note">Note:</label><br>
        <textarea id="notes" name="notes" rows="5" cols="40" placeholder="Enter any notes here..."></textarea><br><br> -->

        <!-- <input type="submit" value="Book"> -->
    </form>
</body>
</html>


