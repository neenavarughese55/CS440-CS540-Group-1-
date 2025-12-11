<?php
    // Start / resume the session and make sure the user is logged in.
    // `session_check.php` should handle redirecting unauthenticated users, etc.
    require 'include/session_check.php';

?>


<!DOCTYPE html>
<html>
<head>
    <title>Account Manager</title>

    <!-- Page styles -->
    <link rel="stylesheet" href="./css/booking.css">
    <link rel="stylesheet" href="./css/account_manager.css">
    <link rel="stylesheet" href="./css/header.css">

    <!-- JavaScript for handling activate/deactivate account actions -->
    <script src="./js/account_manager.js"></script>
   
</head>
<body>
    <!-- Include the navigation bar on the top -->
    <?php require 'include/header.php'; ?>

    <!-- Area to display one-time messages (e.g., success or error messages) -->
    <span class="errorMsg">
        <?php
            if (isset($_SESSION['booking_message'])) {
                echo $_SESSION['booking_message'];
                unset($_SESSION['booking_message']);
            }
        ?>
    </span>

    <!-- Page title -->
    <h3 style="text-align: center; margin-top: 20px; margin-bottom: 20px;">Manage Accounts</h3>

    <!-- Wrapper for the accounts table -->
    <div class='table-wrapper' style='display: block; margin: 0 auto; width: 90%; text-align: left;'>
    <?php

        // Connect to the database (MySQLi is used here).
        $conn = new mysqli("localhost", "root", "", "cs540");

        // If connection failed, stop execution and show error.
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        /**
         * Format a datetime string into "Y-m-d h:i A".
         * If the value is empty or null, return an empty string.
         *
         * @param string|null $value
         * @return string
         */
        function formatDateTime($value) {
            if (empty($value)) {
                // Return empty string for missing datetimes
                return ""; // or return null;
            }

            // Use PHP's DateTime object to safely format the value
            return (new DateTime($value))->format("Y-m-d h:i A");
        }

        // 2. Make sure the logged-in user has a role, and check if it is "admin".
        if (isset($_SESSION['user_role'])) {
            $user_role = $_SESSION['user_role'];

            // Only admins are allowed to see and manage all accounts.
            if ($user_role == "admin") {

                // Query all users and their relevant fields from the database.
                $sql = "SELECT id, username, email, role, is_active, created_at, updated_at
                        FROM `users`";
                $result = $conn->query($sql);

                // If there are any users in the result set:
                if ($result->num_rows > 0) {
                    $rows = [];

                    // Fetch all rows into an array for easier manipulation/looping.
                    while ($row = $result->fetch_assoc()) {
                        $rows[] = $row;
                    }

                    // Begin the HTML table structure.
                    echo "<table id='table' border='1' cellpadding='8' cellspacing='0'>";
                    echo "<thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Is Active?</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th></th>
                            </tr></thead>";
                    echo "<tbody>";

                    // Loop over each user row and build the table body.
                    foreach ($rows as $row) {
                        // Safely escape all values for output in HTML.
                        $id = htmlspecialchars($row["id"] ?? '', ENT_QUOTES, 'UTF-8');
                        $username = htmlspecialchars($row["username"] ?? '', ENT_QUOTES, 'UTF-8');
                        $email = htmlspecialchars($row["email"] ?? '', ENT_QUOTES, 'UTF-8');
                        $role = htmlspecialchars($row["role"] ?? '', ENT_QUOTES, 'UTF-8');
                        $isActive = htmlspecialchars($row["is_active"] ?? '', ENT_QUOTES, 'UTF-8');
                        $createdAt = htmlspecialchars($row["created_at"] ?? '', ENT_QUOTES, 'UTF-8');
                        $updatedAt = htmlspecialchars($row["updated_at"] ?? '', ENT_QUOTES, 'UTF-8');

                        // Convert internal role values into human-readable labels.
                        if ($role == "service-provider") {
                            $role = "Service Provider";
                        } else if ($role == "customer") {
                            $role = "Customer";
                        } else if ($role == "admin") {
                            $role = "Admin";
                        }

                        // Decide what to display based on whether the user is active.
                        // Also decide which button and class to use for JS handling.
                        $class = "";
                        $btnName = "";

                        if ($isActive == "1") {
                            // User is currently active
                            $isActive = "Active";
                            $class = "deactivate-btn"; // JS will treat this as a "Deactivate" action
                            $btnName = "Deactivate";
                        } else {
                            // User is currently inactive
                            $isActive = "Inactive";
                            $class = "activate-btn"; // JS will treat this as an "Activate" action
                            $btnName = "Activate";
                        }

                        // Format the created_at and updated_at columns for display
                        $createdAt = formatDateTime($createdAt);
                        $updatedAt = formatDateTime($updatedAt);

                        // Output one table row for this user.
                        // Note: class names on <td> are used by JavaScript to read values.
                        echo "<tr>
                                <td class='id'>$id</td>
                                <td class='username'>$username</td>
                                <td class='email'>$email</td>
                                <td class='role'>$role</td>
                                <td class='is_active'>$isActive</td>
                                <td class='created_at'>$createdAt</td>
                                <td class='updated_at'>$updatedAt</td>
                                <td><button type='button' class='$class'>$btnName</button></td>
                            </tr>";
                    }

                    // Close the table body and table tags.
                    echo "</tbody></table>";
                } else {
                    // No users found in the database (or query returned 0 rows).
                    // Message text can be updated to something like "No users found."
                    echo "<p>No appointment slots found.</p>";
                }

                // Close the database connection once we're done.
                $conn->close();
            } // End if the user is Admin
        }
    ?>
    </div>

</body>
</html>


