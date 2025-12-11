<?php
    // Ensure user is logged in; prevents unauthorized access to this page.
    require 'include/session_check.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Appointments</title>

    <!-- Shared CSS styles -->
    <link rel="stylesheet" href="./css/booking.css">
    <link rel="stylesheet" href="./css/view-appointments.css">
    <link rel="stylesheet" href="./css/header.css">

    <!-- JavaScript that handles table interaction, filtering, and cancellation -->
    <script src="./js/view-appointments.js"></script>
</head>
<body>

    <!-- Include the unified site header -->
    <?php require 'include/header.php'; ?>

    <!-- Display cancellation-related messages from session -->
    <span class="errorMsg">
        <?php
        if (isset($_SESSION['cancel_message'])) {
            echo $_SESSION['cancel_message'];
            unset($_SESSION['cancel_message']); // Prevent showing message again on refresh
        }
        ?>
    </span>

    <!-- Hidden category dropdown (currently unused, but structure preserved) -->
    <div class="form-group" id="category-container" style="display: none;">
        <label>Category</label>
        <select name="category" id="category">
            <?php
                // Connect to the database
                $conn = new mysqli("localhost", "root", "", "cs540");

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Retrieve all categories for dropdown
                $sql = "SELECT id, name FROM categories";
                $result = $conn->query($sql);

                // Fill dropdown with categories
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
    <br><br>

    <!-- Main form handles cancel actions -->
    <form id="myForm" method="post" action="./backend/cancel.php">

        <!-- Table wrapper for appointment lists -->
        <div class='table-wrapper' style='display: block; margin: 0 auto; width: 90%; text-align: left;'>

        <?php
            // Establish DB connection again for appointment queries
            $conn = new mysqli("localhost", "root", "", "cs540");

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Function to format datetime into a readable format
            function formatDateTime($value) {
                if (empty($value)) {
                    return "";
                }
                return (new DateTime($value))->format("Y-m-d h:i A");
            }

            // Determine user role and display the correct appointment table
            if (isset($_SESSION['user_role'])) {
                $user_role = $_SESSION['user_role'];

                /* ============================================================
                   SERVICE PROVIDER VIEW
                   Shows all appointment slots created by the logged-in provider
                   ============================================================ */
                if ($user_role == "service-provider") {

                    $provider_profiles_id_stmt = "";

                    // Restrict results to only the current provider's slots
                    if (isset($_SESSION['provider_profiles_id'])) {
                        $provider_profiles_id = $_SESSION['provider_profiles_id'];
                        $provider_profiles_id_stmt = " AND asl.provider_id = " . $provider_profiles_id;
                    }

                    // Join: appointment_slots + appointments + users
                    $sql = "SELECT asl.id, asl.start_time, asl.end_time, asl.created_at, asl.updated_at, asl.notes,
                            u.username, a.status, a.id as appointment_id
                            FROM `appointment_slots` asl 
                            LEFT JOIN `appointments` a ON asl.id = a.slot_id
                            LEFT JOIN `users` u ON a.user_id = u.id 
                            WHERE TRUE " . $provider_profiles_id_stmt;

                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {

                        // Build table header for provider view
                        echo "<table id='table' border='1' cellpadding='8' cellspacing='0'>";
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

                        // Loop through provider's slots and display them
                        while ($row = $result->fetch_assoc()) {
                            $slotId        = htmlspecialchars($row["id"] ?? '');
                            $notes         = htmlspecialchars($row["notes"] ?? '');
                            $start         = htmlspecialchars($row["start_time"] ?? '');
                            $end           = htmlspecialchars($row["end_time"] ?? '');
                            $username      = htmlspecialchars($row["username"] ?? '');
                            $status        = htmlspecialchars($row["status"] ?? '');
                            $appointmentID = htmlspecialchars($row["appointment_id"] ?? '');

                            $style = "";

                            // Apply Tag Color Logic:
                            if ($status == "booked") {
                                $status = "Booked";
                            } 
                            if ($status == "Cancelled") {
                                $style = " style='color: red;' ";
                                $disabled = " disabled ";
                            } else {
                                $disabled = "";
                            }
                            if (empty($status)) {
                                $status = "Active";
                                $style  = " style='color: green;' ";
                            }

                            // Format times
                            $start = formatDateTime($start);
                            $end   = formatDateTime($end);

                            // Table row output
                            echo "<tr $style>
                                    <td class='slot_id'>$slotId</td>
                                    <td class='notes'>$notes</td>
                                    <td class='start_time'>$start</td>
                                    <td class='end_time'>$end</td>
                                    <td class='status'>$status</td>
                                    <td class='booked_by'>$username</td>
                                    <td><button type='button' class='cancel-button' $disabled>Cancel</button></td>
                                    <td style='display:none;' class='appointment_id'>$appointmentID</td>
                                </tr>";
                        }

                        echo "</tbody></table>";

                    } else {
                        echo "<p>No appointment slots found.</p>";
                    }

                    $conn->close();
                }

                /* ============================================================
                   CUSTOMER VIEW
                   Shows only the appointments booked by the logged-in customer
                   ============================================================ */
                else if ($user_role == "customer") {

                    $userID = $_SESSION['user_id'] ?? "";

                    // Query appointments joined with provider and category info
                    $sql = "SELECT a.id, a.provider_id, pp.business_name, c.id as category_id, 
                            c.name as category_name, a.notes, start_time, end_time, a.status, a.updated_by
                            FROM `appointments` a
                            LEFT JOIN provider_profiles pp ON a.provider_id = pp.id
                            LEFT JOIN categories c ON a.category_id = c.id
                            WHERE a.user_id = '". $userID. "'";

                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {

                        echo "<table id='table' border='1' cellpadding='8' cellspacing='0'>";
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

                        // Loop through customer appointments
                        while ($row = $result->fetch_assoc()) {

                            $slotId        = htmlspecialchars($row["id"] ?? '');
                            $providerId    = htmlspecialchars($row["provider_id"] ?? '');
                            $business_name = htmlspecialchars($row["business_name"] ?? '');
                            $category_name = htmlspecialchars($row["category_name"] ?? '');
                            $notes         = htmlspecialchars($row["notes"] ?? '');
                            $category_id   = htmlspecialchars($row["category_id"] ?? '');
                            $start         = htmlspecialchars($row["start_time"] ?? '');
                            $end           = htmlspecialchars($row["end_time"] ?? '');
                            $status        = htmlspecialchars($row["status"] ?? '');
                            $updated_by    = htmlspecialchars($row["updated_by"] ?? '');

                            $style = "";

                            // Status color coding
                            if ($status == "booked") {
                                $status = "Booked";
                                $style = " style='color: green;' ";
                            } 
                            else if ($status == "Cancelled") {
                                $style = " style='color: red;' ";
                            }
                            else if (empty($status)) {
                                $status = "Active";
                            }

                            $start = formatDateTime($start);
                            $end   = formatDateTime($end);

                            echo "<tr $style>
                                    <td class='slot_id'>$slotId</td>
                                    <td style='display:none;' class='provider_id'>$providerId</td>
                                    <td class='business_name'>$business_name</td>
                                    <td style='display:none;' class='category_id'>$category_id</td>
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

                /* ============================================================
                   ADMIN VIEW
                   Shows ALL appointment slots system-wide (full system view)
                   ============================================================ */
                else if ($user_role == "admin") {

                    // Query: fetch appointment slots + appointment details + provider name + customer username
                    $sql = "SELECT asl.id, asl.start_time, asl.end_time, asl.created_at, asl.updated_at, asl.notes,
                            u.username as username, a.status, p.business_name, a.id as appointment_id
                            FROM `appointment_slots` asl 
                            LEFT JOIN `appointments` a ON asl.id = a.slot_id
                            LEFT JOIN `users` u ON a.user_id = u.id
                            LEFT JOIN `provider_profiles` p ON asl.provider_id = p.id";

                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {

                        // Convert result set into array for filtering dropdowns
                        $rows = [];
                        while ($row = $result->fetch_assoc()) {
                            $rows[] = $row;
                        }

                        /* ---------------- SERVICE PROVIDER DROPDOWN ---------------- */
                        echo "<div style='margin-bottom: 20px;'>";

                        echo "<div id='service-provider-container'>";
                        echo "<h3>Service Provider</h3>";

                        echo "<select class='modern-select' id='service-provider-username-dropdown'>";

                        // Reconnect to DB to fetch all provider business names
                        $conn = new mysqli("localhost", "root", "", "cs540");

                        if ($conn->connect_error) {
                            die("Connection failed: " . $conn->connect_error);
                        }

                        $sql = "SELECT business_name FROM provider_profiles ORDER BY business_name;";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $business_name = htmlspecialchars($row["business_name"]);
                                echo "<option value=\"$business_name\">$business_name</option>";
                            }
                        }

                        echo "</select>";
                        echo "</div>";

                        /* ---------------- APPOINTMENT BOOKED-BY DROPDOWN ---------------- */
                        echo "<div id='booked-by-container'>";
                        echo "<h3>Appointments booked by</h3>";

                        // Collect unique usernames of customers who booked appointments
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
                            $safe = htmlspecialchars($username);
                            echo "<option value='$safe'>$safe</option>";
                        }

                        echo "</select>";
                        echo "</div>";
                        echo "</div>"; // End dropdown container

                        /* ---------------- ADMIN TABLE ---------------- */
                        echo "<table id='table' border='1' cellpadding='8' cellspacing='0'>";
                        echo "<thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Service Provider</th>
                                    <th>Notes</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Status</th>
                                    <th>Booked By</th>
                                    <th>Action</th>
                                </tr>
                              </thead>";
                        echo "<tbody>";

                        // Loop through all system-wide appointment slots
                        foreach ($rows as $row) {
                            $slotId        = htmlspecialchars($row["id"] ?? '');
                            $provider      = htmlspecialchars($row["business_name"] ?? '');
                            $notes         = htmlspecialchars($row["notes"] ?? '');
                            $start         = htmlspecialchars($row["start_time"] ?? '');
                            $end           = htmlspecialchars($row["end_time"] ?? '');
                            $username      = htmlspecialchars($row["username"] ?? '');
                            $status        = htmlspecialchars($row["status"] ?? '');
                            $appointmentID = htmlspecialchars($row["appointment_id"] ?? '');

                            $style = "";

                            // Status formatting
                            if ($status == "booked") {
                                $status = "Booked";
                                $style  = " style='color: green;' ";
                            }
                            if ($status == "Cancelled") {
                                $style = " style='color: red;' ";
                                $disabled = " disabled ";
                            } else {
                                $disabled = "";
                            }
                            if (empty($status)) {
                                $status = "Active";
                            }

                            $start = formatDateTime($start);
                            $end   = formatDateTime($end);

                            echo "<tr $style>
                                    <td class='slot_id'>$slotId</td>
                                    <td class='service_provider'>$provider</td>
                                    <td class='notes'>$notes</td>
                                    <td class='start_time'>$start</td>
                                    <td class='end_time'>$end</td>
                                    <td class='status'>$status</td>
                                    <td class='booked_by'>$username</td>
                                    <td><button type='button' class='cancel-button' $disabled>Cancel</button></td>
                                    <td style='display:none;' class='appointment_id'>$appointmentID</td>
                                </tr>";
                        }

                        echo "</tbody></table>";
                    } else {
                        echo "<p>No appointment slots found.</p>";
                    }

                    $conn->close();
                }
            }
        ?>
        </div>  
        <!-- End table-wrapper -->

        <!-- Hidden inputs used by JS to populate data before submitting cancel.php -->
        <input type="hidden" name="appointment_id" id="appointment_id">
        <input type="hidden" name="slot_id" id="slot_id">
        <input type="hidden" name="username" id="username">
        <input type="hidden" name="notes" id="notes">
        <input type="hidden" name="service_provider" id="service_provider">

    </form>

</body>
</html>

