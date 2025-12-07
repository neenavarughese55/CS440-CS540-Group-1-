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

    <script src="./js/view-appointments.js"></script>
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
        <div class='table-wrapper' style='display: block; margin: 0 auto; width: 90%; text-align: left;'>
        <?php
            $conn = new mysqli("localhost", "root", "", "cs540");

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            function formatDateTime($value) {
                if (empty($value)) {
                    return ""; // or return null;
                }
                return (new DateTime($value))->format("Y-m-d h:i A");
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
                            } 
                            
                            if ($status == "Cancelled") {
                                $style = " style = 'color: red;' ";
                                $disabled = " disabled ";
                            } else {
                                $disabled = "";
                            }

                            if (empty($status)) {
                                $status = "Active";
                                $style = " style = 'color: green;' ";
                            }

                            $start = formatDateTime($start);
                            $end = formatDateTime($end);
                            
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
                        c.name as category_name, a.notes, start_time, end_time, a.status, a.updated_by
                        FROM `appointments` a
                        LEFT JOIN provider_profiles pp ON a.provider_id = pp.id
                        LEFT JOIN categories c ON a.category_id = c.id
                        WHERE a.user_id = '". $userID. "'";


                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        // echo "<div class='table-wrapper' style='display: block; margin: 0 auto; width: 90%;'>";
                        echo "<table border='1' cellpadding='8' cellspacing='0'>";
                        echo "<thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Provider Name</th>
                                    <th>Category</th>
                                    <th>Notes</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Status</th>
                                    <th>Status Updated By</th>
                                </tr>
                                </thead>";
                        echo "<tbody>";

                        while ($row = $result->fetch_assoc()) {
                            $slotId        = htmlspecialchars($row["id"] ?? '', ENT_QUOTES, 'UTF-8');
                            $providerId    = htmlspecialchars($row["provider_id"] ?? '', ENT_QUOTES, 'UTF-8');
                            $business_name = htmlspecialchars($row["business_name"] ?? '', ENT_QUOTES, 'UTF-8');
                            $category_name = htmlspecialchars($row["category_name"] ?? '', ENT_QUOTES, 'UTF-8');
                            $notes         = htmlspecialchars($row["notes"] ?? '', ENT_QUOTES, 'UTF-8');
                            $category_id   = htmlspecialchars($row["category_id"] ?? '', ENT_QUOTES, 'UTF-8');
                            $start         = htmlspecialchars($row["start_time"] ?? '', ENT_QUOTES, 'UTF-8');
                            $end           = htmlspecialchars($row["end_time"] ?? '', ENT_QUOTES, 'UTF-8');
                            $status        = htmlspecialchars($row["status"] ?? '', ENT_QUOTES, 'UTF-8');
                            $updated_by    = htmlspecialchars($row["updated_by"] ?? '', ENT_QUOTES, 'UTF-8');


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

                            $start = formatDateTime($start);
                            $end = formatDateTime($end);

                            echo "<tr $style>
                                    <td class='slot_id'>$slotId</td>
                                    <td style = 'display: none;' class='provider_id'>$providerId</td>
                                    <td class='business_name'>$business_name</td>
                                    <td style = 'display: none;' class='category_id'>$category_id</td>
                                    <td class='category_name'>$category_name</td>
                                    <td class='notes'>$notes</td>
                                    <td class='start_time'>$start</td>
                                    <td class='end_time'>$end</td>
                                    <td class='status'>$status</td>
                                    <td class='status_updated_by'>$updated_by</td>
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
                            u.username as username, a.status
                            FROM `appointment_slots` asl 
                            LEFT JOIN `appointments` a ON asl.id = a.slot_id
                            LEFT JOIN `users` u ON a.user_id = u.id";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        $rows = [];
                        while ($row = $result->fetch_assoc()) {
                            $rows[] = $row;
                        }

                        // "Appointments booked by" dropdown:
                        echo "<h3>Appointments booked by</h3>";

                        // Build Distinct usernames:
                        $usernames = [];

                        foreach ($rows as $row) {
                            $u = trim($row["username"] ?? '');

                            if ($u !== '' && !in_array($u, $usernames, true)) {
                                $usernames[] = $u;
                            }
                        }

                        echo "<select class='modern-select' id='booked-by-username'>";
                        echo "<option value='any'>-Any User-</option>";

                        foreach ($usernames as $username) {
                            $safe = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
                            echo "<option value='$safe'>$safe</option>";
                        }
                        echo "</select>";

                        echo "<br>";
                        //

                        

                        echo "<table id='table' border='1' cellpadding='8' cellspacing='0'>";
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

                        foreach ($rows as $row) {
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

                            $start = formatDateTime($start);
                            $end = formatDateTime($end);

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
        </div> 
        <!-- End "table-wrapper" class -->

        <input type="hidden" name="appointment_id" id="appointment_id">
        <input type="hidden" name="slot_id" id="slot_id">
        <input type="hidden" name="username" id="username">

    </form>
</body>
</html>


