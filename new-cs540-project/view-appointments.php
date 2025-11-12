<?php
    require 'include/session_check.php';
    
    // Show up all PHP errors for debugging:
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

?>


<!DOCTYPE html>
<html>
<head>
    <title>View Appointments</title>
    <link rel="stylesheet" href="./css/booking.css">
    <link rel="stylesheet" href="./css/view-appointments.css">
    <link rel="stylesheet" href="./css/header.css">
    <script>

        // Submit form
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".cancel-button").forEach(function(button) {
                button.addEventListener("click", function() {
                    const row = this.closest("tr");

                    document.getElementById("appointment_id").value = row.querySelector(".appointment_id").textContent.trim();
                    console.log("appointment_id: " + document.getElementById("appointment_id").value);

                    document.getElementById("slot_id").value = row.querySelector(".slot_id").textContent.trim();
                    console.log("slot_id: " + document.getElementById("slot_id").value);

                    document.getElementById("username").value = row.querySelector(".booked_by").textContent.trim();
                    console.log("username: " + document.getElementById("username").value);

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
        if (isset($_SESSION['cancel_message'])) {
            echo $_SESSION['cancel_message'];
            unset($_SESSION['cancel_message']);
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

    <!-- <h4>Select Available Spots:</h4> -->

    <form id="myForm" method="post" action="./backend/cancel.php">
        <?php
            $conn = new mysqli("localhost", "root", "", "cs540");

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            if (isset($_SESSION['user_role'])) {
                $user_role = $_SESSION['user_role'];

                // If the user is Service Provider:
                // We do not need "Provider Name", "Category" columns:
                if ($user_role == "service-provider") {
                    $provider_profiles_id_stmt = "";

                    if (isset($_SESSION['provider_profiles_id'])) {
                        $provider_profiles_id = $_SESSION['provider_profiles_id'];
                        $provider_profiles_id_stmt = " AND asl.provider_id = " . $provider_profiles_id;
                    }

                    $sql = "SELECT asl.id, asl.start_time, asl.end_time, asl.created_at, asl.updated_at, asl.notes,
                            u.username, a.status, a.id as appointment_id
                            FROM `appointment_slots` asl 
                            LEFT JOIN `appointments` a ON asl.id = a.slot_id
                            LEFT JOIN `users` u ON a.user_id = u.id WHERE TRUE " . $provider_profiles_id_stmt;
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        echo "<table border='1' cellpadding='8' cellspacing='0'>";
                        echo "<thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Notes</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Status</th>
                                    <th>Booked By</th>
                                    <th>Action</th>
                                </tr></thead>";
                        echo "<tbody>";

                        while ($row = $result->fetch_assoc()) {
                            $slotId = htmlspecialchars($row["id"] ?? '', ENT_QUOTES, 'UTF-8');
                            $notes = htmlspecialchars($row["notes"] ?? '', ENT_QUOTES, 'UTF-8');
                            $start = htmlspecialchars($row["start_time"] ?? '', ENT_QUOTES, 'UTF-8');
                            $end = htmlspecialchars($row["end_time"] ?? '', ENT_QUOTES, 'UTF-8');
                            $username = htmlspecialchars($row["username"] ?? '', ENT_QUOTES, 'UTF-8');
                            $status = htmlspecialchars($row["status"] ?? '', ENT_QUOTES, 'UTF-8');
                            $appointmentID = htmlspecialchars($row["appointment_id"] ?? '', ENT_QUOTES, 'UTF-8');

                            $style = "";

                            if ($status == "booked") {
                                $status = "Booked";
                                $style = " style = 'color: green;' ";
                            } 
                            
                            if ($status == "Cancelled") {
                                $style = " style = 'color: red;' ";
                                $disabled = " disabled ";
                            } else {
                                $disabled = "";
                            }

                            if (empty($status)) {
                                $status = "Active";
                            }
                            
                            echo "<tr $style>
                                    <td class='slot_id'>$slotId</td>
                                    <td class='notes'>$notes</td>
                                    <td class='start_time'>$start</td>
                                    <td class='end_time'>$end</td>
                                    <td class='status'>$status</td>
                                    <td class='booked_by'>$username</td>
                                    <td><button type='button' class='cancel-button' $disabled>Cancel</button></td>
                                    <td style = 'display:none;' class='appointment_id'>$appointmentID</td>
                                </tr>";
                        }

                        echo "</tbody></table>";
                    } else {
                        echo "<p>No appointment slots found.</p>";
                    }

                    $conn->close();
                } // End if the user is Service Provider
                
                // If the user is Customer:
                else if ($user_role == "customer") {
                    $userID = "";
                    if (isset($_SESSION['user_id'])) {
                        $userID = $_SESSION['user_id'];
                    }

                    $sql = "SELECT a.id, a.provider_id, pp.business_name, c.id as category_id, 
                        c.name as category_name, a.notes, start_time, end_time FROM `appointments` a
                        LEFT JOIN provider_profiles pp ON a.provider_id = pp.id
                        LEFT JOIN categories c ON a.category_id = c.id
                        WHERE a.user_id = '". $userID. "'";

                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        echo "<table border='1' cellpadding='8' cellspacing='0'>";
                        echo "<thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Provider Name</th>
                                    <th>Category</th>
                                    <th>Notes</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                </tr>
                                </thead>";
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
                                </tr>";
                        }

                        echo "</tbody></table>";
                    } else {
                        echo "<p>No appointment slots found.</p>";
                    }

                    $conn->close();
                }
                // End if the user is Customer

                // If the user is Admin:
                // Show All Appointments:
                else if ($user_role == "admin") {
                    $sql = "SELECT asl.id, asl.start_time, asl.end_time, asl.created_at, asl.updated_at, asl.notes,
                            u.username, a.status
                            FROM `appointment_slots` asl 
                            LEFT JOIN `appointments` a ON asl.id = a.slot_id
                            LEFT JOIN `users` u ON a.user_id = u.id";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        echo "<table border='1' cellpadding='8' cellspacing='0'>";
                        echo "<thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Notes</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Status</th>
                                    <th>Booked By</th>
                                </tr></thead>";
                        echo "<tbody>";

                        while ($row = $result->fetch_assoc()) {
                            $slotId = htmlspecialchars($row["id"] ?? '', ENT_QUOTES, 'UTF-8');
                            $notes = htmlspecialchars($row["notes"] ?? '', ENT_QUOTES, 'UTF-8');
                            $start = htmlspecialchars($row["start_time"] ?? '', ENT_QUOTES, 'UTF-8');
                            $end = htmlspecialchars($row["end_time"] ?? '', ENT_QUOTES, 'UTF-8');
                            $username = htmlspecialchars($row["username"] ?? '', ENT_QUOTES, 'UTF-8');
                            $status = htmlspecialchars($row["status"] ?? '', ENT_QUOTES, 'UTF-8');

                            $style = "";

                            if ($status == "booked") {
                                $status = "Booked";
                                $style = " style = 'color: green;' ";
                            } else if ($status == "Cancelled") {
                                $style = " style = 'color: red;' ";
                            }

                            if (empty($status)) {
                                $status = "Active";
                            }
                            echo "<tr $style>
                                    <td class='slot_id'>$slotId</td>
                                    <td class='notes'>$notes</td>
                                    <td class='start_time'>$start</td>
                                    <td class='end_time'>$end</td>
                                    <td class='status'>$status</td>
                                    <td class='booked_by'>$username</td>
                                </tr>";
                        }

                        echo "</tbody></table>";
                    } else {
                        echo "<p>No appointment slots found.</p>";
                    }

                    $conn->close();
                } // End if the user is Admin
            }
            
        ?>

        <input type="hidden" name="appointment_id" id="appointment_id">
        <input type="hidden" name="slot_id" id="slot_id">
        <input type="hidden" name="username" id="username">

    </form>
</body>
</html>


