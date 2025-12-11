<?php
    // Ensure the user is logged in before accessing the report page.
    // Redirects or blocks unauthorized access inside session_check.php.
    require 'include/session_check.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Generate Report</title>

    <!-- CSS for report layout and styling -->
    <link rel="stylesheet" href="./css/report.css">
    <link rel="stylesheet" href="./css/header.css">

    <!-- JavaScript for handling report actions and fetching data -->
    <script src="./js/report.js"></script>
</head>

<body>

    <!-- Shared navigation header -->
    <?php require 'include/header.php'; ?>

    <!-- Display one-time session message (errors, status, etc.) -->
    <span class="errorMsg">
        <?php
        if (isset($_SESSION['booking_message'])) {
            echo $_SESSION['booking_message'];
            unset($_SESSION['booking_message']);  // Prevent duplicate display
        }
        ?>
    </span>

    <!-- ======================== USER REPORT SECTION ======================== -->
    <h3 style="margin-top: 20px;">User Report</h3>

    <div class="form-group" id="user-report-container">

        <!-- Category dropdown (hidden — kept for optional filtering or future use) -->
        <label style="display: none;">Category</label>
        <select style="display: none;" name="category" id="category">
            <option value="ALL">-All Categories-</option>

            <?php
                // Store categories so they can be reused later in the Appointment Report
                $categories = [];

                // Connect to the database
                $conn = new mysqli("localhost", "root", "", "cs540");

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Retrieve all categories for filtering
                $sql = "SELECT id, name FROM categories";
                $result = $conn->query($sql);

                // Generate dropdown items
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $categories[] = $row;
                        $id   = htmlspecialchars($row["id"]);
                        $name = htmlspecialchars($row["name"]);
                        echo "<option value=\"$id\">$name</option>";
                    }
                }

                $conn->close();
            ?>
        </select>

        <!-- Choose specific user or ALL users -->
        <label>User</label>
        <select id="user-report-username-dropdown" name="user-report-username-dropdown">
            <option value="ALL">-All Users-</option>

            <?php
                // Will store list of users for re-use
                $users = [];

                // Reconnect to DB
                $conn = new mysqli("localhost", "root", "", "cs540");

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Fetch all usernames alphabetically
                $sql = "SELECT id, username FROM users ORDER BY username";
                $result = $conn->query($sql);

                // Populate <option> elements
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $users[]  = $row;
                        $id       = htmlspecialchars($row["id"]);
                        $username = htmlspecialchars($row["username"]);
                        echo "<option value=\"$id\">$username</option>";
                    }
                }

                $conn->close();
            ?>
        </select>

        <!-- Date range for report -->
        <label for="from">From</label>
        <input type="date" id="user-report-from" name="from" required>

        <label for="to">To</label>
        <input type="date" id="user-report-to" name="to" required>

        <!-- Button triggers JS fetch to generate report -->
        <button type="button" id="user-report-run-btn" class="run-btn">Run Report</button>
    </div>


    <!-- ======================== APPOINTMENT REPORT SECTION ======================== -->
    <h3 style="margin-top: 30px;">Appointment Report</h3>

    <div class="form-group" id="appointment-report-container">

        <!-- Category filter -->
        <label>Category</label>
        <select name="appointment-report-category-dropdown" id="appointment-report-category-dropdown">
            <option value="ALL">-All Categories-</option>
            <?php
                // Re-use categories array populated earlier
                foreach ($categories as $row) {
                    $id   = htmlspecialchars($row["id"]);
                    $name = htmlspecialchars($row["name"]);
                    echo "<option value=\"$name\">$name</option>";
                }
            ?>
        </select>

        <!-- Select specific user to filter appointments -->
        <label>User</label>
        <select id="appointment-report-username-dropdown" name="appointment-report-username-dropdown">
            <option value="ALL">-All Users-</option>

            <?php
                // Reconnect to database
                $conn = new mysqli("localhost", "root", "", "cs540");

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Fetch all usernames
                $sql = "SELECT id, username FROM users ORDER BY username";
                $result = $conn->query($sql);

                // Populate dropdown
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $users[]  = $row;
                        $username = htmlspecialchars($row["username"]);
                        echo "<option value=\"$username\">$username</option>";
                    }
                }

                $conn->close();
            ?>
        </select>

        <!-- Date range inputs -->
        <label for="from">From</label>
        <input type="date" id="appointment-report-from" name="appointment-report-from" required>

        <label for="to">To</label>
        <input type="date" id="appointment-report-to" name="appointment-report-to" required>

        <!-- Button triggers JS to call appointment_report.php -->
        <button type="button" id="appointment-report-run-btn" class="run-btn">Run Report</button>
    </div>


    <!-- ======================== REPORT RESULT TABLE ======================== -->
    <div class='table-wrapper' style='display: block; margin: 0 auto; width: 90%; text-align: left; margin-top: 30px;'>
        <table id="table" border='1' cellpadding='8' cellspacing='0'>
            <!-- JS populates this <tbody> with rows -->
            <tbody></tbody>
        </table>
    </div>

</body>
</html>
