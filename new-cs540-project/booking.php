<?php
    // Make sure the user is logged in; `session_check.php` should block or redirect
    // unauthenticated users from accessing this booking page.
    require 'include/session_check.php';
?>


<!DOCTYPE html>
<html>
<head>
    <title>Booking Form</title>

    <!-- Main page styling -->
    <link rel="stylesheet" href="./css/booking.css">
    <link rel="stylesheet" href="./css/header.css">

    <!-- JavaScript for booking behavior (search/filter, click events, etc.) -->
    <script src="./js/booking.js"></script>
</head>
<body>
    <!-- Shared header (navigation bar, logo, etc.) -->
    <?php require 'include/header.php'; ?>

    <!-- Display one-time message (success/error/info) related to booking -->
    <span class="errorMsg">
        <?php
        if (isset($_SESSION['booking_message'])) {
            echo $_SESSION['booking_message'];
            // Remove the message so it doesn't show again on refresh
            unset($_SESSION['booking_message']);
        }
        ?>
    </span>

    <!-- Hidden category selector (can be used if you later show category filter here) -->
    <div class="form-group" id="category-container" style="display: none;">
        <label>Category</label>
        <select name="category" id="category">
            <?php
            // Connect to the database using MySQLi.
            $conn = new mysqli("localhost", "root", "", "cs540");

            // Stop execution if connection fails.
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Load all categories from the `categories` table.
            $sql = "SELECT id, name FROM categories";
            $result = $conn->query($sql);

            // Populate category dropdown options.
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $id   = htmlspecialchars($row["id"]);
                    $name = htmlspecialchars($row["name"]);
                    echo "<option value=\"$id\">$name</option>";
                }
            }

            // Close the database connection for this block.
            $conn->close();
            ?>
        </select>
    </div>
    <br>

    <!-- Section title and search bar -->
    <h3>Select Available Appointments:</h3>
    <input type="text"
           id="searchInput"
           style="width: 50%; display: block; margin: 40px auto 20px auto;"
           placeholder="Type to search...">
           <!-- (searchInput is usually used by JS in booking.js to filter rows) -->

    <!-- Category dropdown (visible) for filtering displayed slots by category -->
    <?php
        echo "<select class='modern-select' id='category-dropdown' style='width: 50%; margin: 20px auto; display: block;'>";
        echo "<option value='any'>-Any Categories-</option>";

        // Connect again to the database to fetch categories for this visible dropdown.
        $conn = new mysqli("localhost", "root", "", "cs540");

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Load all categories again.
        $sql = "SELECT id, name FROM categories";
        $result = $conn->query($sql);

        // Fill dropdown with category names.
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $id   = htmlspecialchars($row["id"]);
                $name = htmlspecialchars($row["name"]);
                // Use category name as the value (for filtering by name in JS).
                echo "<option value=\"$name\">$name</option>";
            }
        }
        echo "</select>";
    ?>

    <!-- Main booking form. Hidden inputs are filled in by JavaScript before submit. -->
    <form id="myForm" method="post" action="./backend/booking.php">
        <?php
            // Connect to database to retrieve available appointment slots.
            $conn = new mysqli("localhost", "root", "", "cs540");

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Optional: if a provider is logged in, only show their own slots.
            $provider_profiles_id_stmt = "";

            if (isset($_SESSION['provider_profiles_id'])) {
                $provider_profiles_id      = $_SESSION['provider_profiles_id'];
                // This string is injected into the WHERE clause.
                $provider_profiles_id_stmt = "provider_id = " . $provider_profiles_id . " AND ";
            }

            /**
             * Appointment slot query:
             * - Select slots from `appointment_slots` (alias `s`)
             * - Join with `provider_profiles` to get provider business name
             * - Join with `categories` to get category name
             * - Optionally restrict by provider_id if logged-in user is a provider
             * - Exclude slots that already exist in the `appointments` table
             */
            $sql = "SELECT s.id,
                           s.provider_id,
                           pp.business_name,
                           c.id   AS category_id,
                           c.name AS category_name,
                           s.notes,
                           start_time,
                           end_time
                    FROM appointment_slots s
                    LEFT JOIN provider_profiles pp ON s.provider_id = pp.id
                    LEFT JOIN categories c ON s.category_id = c.id
                    WHERE " . $provider_profiles_id_stmt . "
                    s.id NOT IN (SELECT slot_id FROM appointments)";

            $result = $conn->query($sql);

            // If there are available slots, build the HTML table.
            if ($result->num_rows > 0) {
                echo "<table id='myTable' border='1' cellpadding='8' cellspacing='0'>";
                echo "<thead>
                        <tr>
                            <th>ID</th>
                            <th>Provider Name</th>
                            <th>Category</th>
                            <th>Notes</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Book</th>
                        </tr>
                      </thead>";
                echo "<tbody>";

                // Output each available slot as a table row.
                while ($row = $result->fetch_assoc()) {
                    $slotId        = htmlspecialchars($row["id"] ?? '');
                    $providerId    = htmlspecialchars($row["provider_id"] ?? '');
                    $business_name = htmlspecialchars($row["business_name"] ?? '');
                    $category_name = htmlspecialchars($row["category_name"] ?? '');
                    $notes         = htmlspecialchars($row["notes"] ?? '');
                    $category_id   = htmlspecialchars($row["category_id"] ?? '');

                    // Display times as-is; assume stored in local time or pre-converted.
                    $startDisplay = htmlspecialchars($row["start_time"]);
                    $endDisplay   = htmlspecialchars($row["end_time"]);

                    // Each <td> gets a class so JavaScript can read the values
                    // when a user clicks the "Book" button.
                    echo "<tr>
                            <td class='slot_id'>$slotId</td>
                            <td style='display:none;' class='provider_id'>$providerId</td>
                            <td class='business_name'>$business_name</td>
                            <td style='display:none;' class='category_id'>$category_id</td>
                            <td class='category_name'>$category_name</td>
                            <td class='notes'>$notes</td>
                            <td class='start_time'>$startDisplay</td>
                            <td class='end_time'>$endDisplay</td>
                            <td><button type='button' class='book'>Book</button></td>
                          </tr>";
                }

                echo "</tbody></table>";
            } else {
                // No open appointment slots exist.
                echo "<p>No appointment slots found.</p>";
            }

            // Close connection after we finish using it.
            $conn->close();
        ?>

        <!-- Hidden inputs that will be populated via JS when clicking "Book" -->
        <input type="hidden" name="slot_id"        id="slot_id">
        <input type="hidden" name="provider_id"    id="provider_id">
        <input type="hidden" name="business_name"  id="business_name">
        <input type="hidden" name="category_id"    id="category_id">
        <input type="hidden" name="category_name"  id="category_name">
        <input type="hidden" name="notes"          id="notes">
        <input type="hidden" name="start_time"     id="start_time">
        <input type="hidden" name="end_time"       id="end_time">
    </form>
</body>
</html>
