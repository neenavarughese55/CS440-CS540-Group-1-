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

    <style>
        /* passed appointments hidden by default */
        .passed-row { display: none; }
        /* optional small spacing for the dropdown */
        .show-passed-container { margin: 12px 0; text-align: left; width: 90%; margin-left: auto; margin-right: auto; }
    </style>
</head>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("myForm");
    const buttons = document.querySelectorAll(".cancel-button");

    buttons.forEach(btn => {
        btn.addEventListener("click", function() {
            const row = btn.closest("tr");
            const appointmentId = row.querySelector(".appointment_id")?.textContent.trim();
            const slotId = row.querySelector(".slot_id")?.textContent.trim();
            const username = row.querySelector(".booked_by")?.textContent.trim();

            // Fill hidden inputs
            document.getElementById("appointment_id").value = appointmentId || "";
            document.getElementById("slot_id").value = slotId || "";
            document.getElementById("username").value = username || "";

            // Submit the form
            form.submit();
        });
    });

// Toggle passed rows using dropdown
const toggle = document.getElementById('showPassed');
if (toggle) {
    // init based on current value (in case it's not "hide")
    const initShow = toggle.value === 'show';
    document.querySelectorAll('.passed-row').forEach(r => r.style.display = initShow ? 'table-row' : 'none');

    toggle.addEventListener('change', function() {
        const passed = document.querySelectorAll('.passed-row');
        if (this.value === 'show') {
            passed.forEach(r => r.style.display = 'table-row'); // use table-row for <tr>
        } else {
            passed.forEach(r => r.style.display = 'none');
        }
    });
}
});
</script>

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
            // Database connection for categories dropdown
            $conn = new mysqli("localhost", "root", "", "cs540");
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            $sql = "SELECT id, name FROM categories";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while ($r = $result->fetch_assoc()) {
                    $id = htmlspecialchars($r["id"]);
                    $name = htmlspecialchars($r["name"]);
                    echo "<option value=\"$id\">$name</option>";
                }
            }
            $conn->close();
          ?>
        </select>
    </div>
    <br>

    <form id="myForm" method="post" action="./backend/cancel.php">
        <div class='table-wrapper' style='display: block; margin: 0 auto; width: 90%; text-align: left;'>
        <?php
            $conn = new mysqli("localhost", "root", "", "cs540");
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            function formatDateTime($value) {
                if (empty($value)) {
                    return "";
                }
                try {
                    return (new DateTime($value))->format("Y-m-d h:i A");
                } catch (Exception $e) {
                    return htmlspecialchars($value);
                }
            }

            // server-local now
            $now = new DateTime();

            if (isset($_SESSION['user_role'])) {
                $user_role = $_SESSION['user_role'];

                // SERVICE PROVIDER view
                if ($user_role == "service-provider") {
                    $provider_profiles_id_stmt = "";
                    if (isset($_SESSION['provider_profiles_id'])) {
                        $provider_profiles_id = (int)$_SESSION['provider_profiles_id'];
                        $provider_profiles_id_stmt = " AND asl.provider_id = " . $provider_profiles_id;
                    }

                    $sql = "SELECT asl.id, asl.start_time, asl.end_time, asl.created_at, asl.updated_at, asl.notes,
                            u.username, a.status, a.id as appointment_id
                            FROM `appointment_slots` asl 
                            LEFT JOIN `appointments` a ON asl.id = a.slot_id
                            LEFT JOIN `users` u ON a.user_id = u.id WHERE TRUE " . $provider_profiles_id_stmt;
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
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
                            $startRaw = $row["start_time"] ?? '';
                            $endRaw = $row["end_time"] ?? '';
                            $start = formatDateTime($startRaw);
                            $end = formatDateTime($endRaw);
                            $username = htmlspecialchars($row["username"] ?? '', ENT_QUOTES, 'UTF-8');
                            $statusRaw = $row["status"] ?? '';
                            $appointmentID = htmlspecialchars($row["appointment_id"] ?? '', ENT_QUOTES, 'UTF-8');

                            // decide whether start is in the past
                            $isPast = false;
                            if (!empty($startRaw)) {
                                try {
                                    $startDt = new DateTime($startRaw);
                                    if ($startDt <= $now) $isPast = true;
                                } catch (Exception $e) {
                                    // ignore parse errors
                                }
                            }

                            // determine status text and color for provider:
                            // Cancelled -> red
                            // Passed -> grey
                            // Booked (future) -> green
                            // Active (no booking) -> blue
                            $low = strtolower(trim((string)$statusRaw));
                            if ($low === "cancelled") {
                                $statusText = "Cancelled";
                                $rowStyle = " style='color: red;'";
                            } elseif ($isPast) {
                                $statusText = "Passed";
                                $rowStyle = " style='color: grey;'";
                            } elseif ($low === "booked") {
                                $statusText = "Booked";
                                $rowStyle = " style='color: green;'";
                            } else {
                                $statusText = empty(trim($statusRaw)) ? "Active" : $statusRaw;
                                $rowStyle = " style='color: blue;'";
                            }

                            // Only show cancel when appointment not passed and not cancelled
                            $actionHtml = "";
                            if (!$isPast && strtolower($statusText) !== "cancelled") {
                                $actionHtml = "<button type='button' class='cancel-button'>Cancel</button>";
                            }

                            // add passed-row class if passed
                            $rowClass = $isPast ? "passed-row" : "";

                            echo "<tr class='$rowClass' $rowStyle>
                                    <td class='slot_id'>$slotId</td>
                                    <td class='notes'>$notes</td>
                                    <td class='start_time'>$start</td>
                                    <td class='end_time'>$end</td>
                                    <td class='status'>$statusText</td>
                                    <td class='booked_by'>$username</td>
                                    <td>$actionHtml</td>
                                    <td style='display:none;' class='appointment_id'>$appointmentID</td>
                                  </tr>";
                        }

                        echo "</tbody></table>";
                    } else {
                        echo "<p>No appointment slots found.</p>";
                    }

                    $conn->close();
                }
                // CUSTOMER view
                else if ($user_role == "customer") {
                    $userID = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

                    $sql = "SELECT a.id, a.slot_id, a.provider_id, pp.business_name, c.id as category_id, 
                        c.name as category_name, a.notes, a.start_time, a.end_time, a.status, a.updated_by
                        FROM `appointments` a
                        LEFT JOIN provider_profiles pp ON a.provider_id = pp.id
                        LEFT JOIN categories c ON a.category_id = c.id
                        WHERE a.user_id = '". $userID. "'";

                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
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
                                    <th>Action</th>
                                </tr>
                                </thead>";
                        echo "<tbody>";

                        while ($row = $result->fetch_assoc()) {
                            // prepare variables
                            $appointmentID = htmlspecialchars($row["id"] ?? '', ENT_QUOTES, 'UTF-8');
                            $slotIdHidden  = htmlspecialchars($row["slot_id"] ?? '', ENT_QUOTES, 'UTF-8');
                            $providerId    = htmlspecialchars($row["provider_id"] ?? '', ENT_QUOTES, 'UTF-8');
                            $business_name = htmlspecialchars($row["business_name"] ?? '', ENT_QUOTES, 'UTF-8');
                            $category_name = htmlspecialchars($row["category_name"] ?? '', ENT_QUOTES, 'UTF-8');
                            $notes = htmlspecialchars($row["notes"] ?? '', ENT_QUOTES, 'UTF-8');
                            $category_id   = htmlspecialchars($row["category_id"] ?? '', ENT_QUOTES, 'UTF-8');
                            $startRaw = $row["start_time"] ?? '';
                            $endRaw = $row["end_time"] ?? '';
                            $start = formatDateTime($startRaw);
                            $end = formatDateTime($endRaw);
                            $statusRaw = $row["status"] ?? '';
                            $updated_by = htmlspecialchars($row["updated_by"] ?? '', ENT_QUOTES, 'UTF-8');
                            $bookedByUser = htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8');

                            // decide if start is in the past
                            $isPast = false;
                            if (!empty($startRaw)) {
                                try {
                                    $startDt = new DateTime($startRaw);
                                    if ($startDt <= $now) $isPast = true;
                                } catch (Exception $e) {
                                    // ignore parse errors
                                }
                            }

                            // determine status text and color for customer:
                            // Cancelled -> red
                            // Passed -> grey
                            // Booked (future) -> green
                            // Active/other -> blue (fallback)
                            $low = strtolower(trim((string)$statusRaw));
                            if ($low === "cancelled") {
                                $statusText = "Cancelled";
                                $rowStyle = " style='color: red;'";
                            } elseif ($isPast) {
                                $statusText = "Passed";
                                $rowStyle = " style='color: grey;'";
                            } elseif ($low === "booked") {
                                $statusText = "Booked";
                                $rowStyle = " style='color: green;'";
                            } else {
                                $statusText = empty(trim($statusRaw)) ? "Active" : $statusRaw;
                                $rowStyle = " style='color: blue;'";
                            }

                            // Only render cancel button when NOT past and not cancelled
                            $actionHtml = "";
                            if (!$isPast && strtolower($statusText) !== "cancelled") {
                                $actionHtml = "<button type='button' class='cancel-button'>Cancel</button>";
                            }

                            // add passed-row class if passed
                            $rowClass = $isPast ? "passed-row" : "";

                            // output row
                            echo "<tr class='$rowClass' $rowStyle>
                                    <td class='slot_id'>$slotIdHidden</td>
                                    <td style='display:none;' class='provider_id'>$providerId</td>
                                    <td class='business_name'>$business_name</td>
                                    <td style='display:none;' class='category_id'>$category_id</td>
                                    <td class='category_name'>$category_name</td>
                                    <td class='notes'>$notes</td>
                                    <td class='start_time'>$start</td>
                                    <td class='end_time'>$end</td>
                                    <td class='status'>$statusText</td>
                                    <td class='status_updated_by'>$updated_by</td>
                                    <td>$actionHtml</td>
                                    <td style='display:none;' class='appointment_id'>$appointmentID</td>
                                    <td style='display:none;' class='slot_id'>$slotIdHidden</td>
                                    <td style='display:none;' class='booked_by'>$bookedByUser</td>
                                  </tr>";
                        }

                        echo "</tbody></table>";
                    } else {
                        echo "<p>No appointment slots found.</p>";
                    }

                    $conn->close();
                }
                // ADMIN view
                else if ($user_role == "admin") {
                    $sql = "SELECT asl.id, asl.start_time, asl.end_time, asl.created_at, asl.updated_at, asl.notes,
                            u.username as username, a.status
                            FROM `appointment_slots` asl 
                            LEFT JOIN `appointments` a ON asl.id = a.slot_id
                            LEFT JOIN `users` u ON a.user_id = u.id";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        $rows = [];
                        while ($row = $result->fetch_assoc()) {
                            $rows[] = $row;
                        }

                        echo "<h3>Appointments booked by</h3>";

                        $usernames = [];
                        foreach ($rows as $r) {
                            $u = trim($r["username"] ?? '');
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
                            $start = formatDateTime($row["start_time"] ?? '');
                            $end = formatDateTime($row["end_time"] ?? '');
                            $username = htmlspecialchars($row["username"] ?? '', ENT_QUOTES, 'UTF-8');
                            $statusRaw = $row["status"] ?? '';

                            if (strtolower($statusRaw) === "booked") {
                                $statusText = "Booked";
                                $style = " style='color: green;'";
                            } elseif (strtolower($statusRaw) === "cancelled") {
                                $statusText = "Cancelled";
                                $style = " style='color: red;'";
                            } else {
                                $statusText = empty(trim($statusRaw)) ? "Active" : $statusRaw;
                                $style = " style='color: blue;'";
                            }

                            echo "<tr $style>
                                    <td class='slot_id'>$slotId</td>
                                    <td class='notes'>$notes</td>
                                    <td class='start_time'>$start</td>
                                    <td class='end_time'>$end</td>
                                    <td class='status'>$statusText</td>
                                    <td class='booked_by'>$username</td>
                                </tr>";
                        }

                        echo "</tbody></table>";
                    } else {
                        echo "<p>No appointment slots found.</p>";
                    }

                    $conn->close();
                } // end roles
            } // end if user_role
        ?>
        </div>
        <!-- End "table-wrapper" class -->

        <!-- Dropdown to show/hide passed appointments (hidden by default) -->
        <div class="show-passed-container">
            <label for="showPassed">Show passed appointments: </label>
            <select id="showPassed" name="showPassed">
                <option value="hide" selected>Hide passed</option>
                <option value="show">Show passed</option>
            </select>
        </div>

        <input type="hidden" name="appointment_id" id="appointment_id">
        <input type="hidden" name="slot_id" id="slot_id">
        <input type="hidden" name="username" id="username">

    </form>
</body>
</html>
