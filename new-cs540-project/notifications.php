<?php
    // Ensure the user is logged in; redirect/block access if the session is invalid.
    require 'include/session_check.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifications</title>

    <!-- Stylesheets for layout, notifications UI, and global header -->
    <link rel="stylesheet" href="./css/booking.css">
    <link rel="stylesheet" href="./css/notifications.css">
    <link rel="stylesheet" href="./css/header.css">

    <!-- Custom JavaScript for notification interactions -->
    <script src="./js/notifications.js"></script>
</head>

<body>

    <!-- Insert the shared header component (navbar, links, etc.) -->
    <?php require 'include/header.php'; ?>

    <!-- Area to display one-time messages (status/error info) -->
    <span class="errorMsg">
        <?php
        if (isset($_SESSION['booking_message'])) {
            echo $_SESSION['booking_message'];
            unset($_SESSION['booking_message']);  // Prevent message from reappearing on refresh
        }
        ?>
    </span>

    <!-- Page title -->
    <h3 style="text-align: center; margin-top: 20px; margin-bottom: 20px;">Notifications</h3>

    <!-- Main notifications container -->
    <div class='table-wrapper' style='display: block; margin: 0 auto; width: 90%; text-align: left;'>
        <?php
            // Connect to MySQL database
            $conn = new mysqli("localhost", "root", "", "cs540");

            // Stop execution if connection fails
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            /**
             * Convert database datetime to readable format:
             * Example: "2025-01-01 15:22:00" → "2025-01-01 03:22 PM"
             */
            function formatDateTime($value) {
                if (empty($value)) {
                    return "";  
                }
                return (new DateTime($value))->format("Y-m-d h:i A");
            }

            // Retrieve user role to determine which notifications they can see
            $user_role = $_SESSION['user_role'];

            /**
             * Base SQL:
             * - Regular users see only their own notifications
             * - Admins see ALL notifications
             */
            $sql = "SELECT id, created_at, notes FROM `notifications` 
                    WHERE user_id = '" . $_SESSION["user_id"] . "'
                    ORDER BY created_at";

            if ($user_role == "admin") {
                // Admins can view all notifications in the system
                $sql = "SELECT id, created_at, notes FROM `notifications` ORDER BY created_at";
            }

            // Execute query
            $result = $conn->query($sql);

            // Display notifications if any exist
            if ($result->num_rows > 0) {

                // Container for notification "cards"
                echo '<div class="email-list">';

                while ($row = $result->fetch_assoc()) {

                    // Individual notification fields
                    $notification_id = $row["id"];
                    $created_at      = $row["created_at"];
                    $notes           = $row["notes"];

                    /**
                     * Output each notification:
                     * - <div class='email unread'> = visual unread styling
                     * - Custom attribute notification_id stores the notification identifier
                     */
                    echo "<div class='email unread' notification_id='$notification_id'>
                            <h4>" . formatDateTime($created_at) . "</h4>
                            <p>$notes</p>
                          </div>";
                }

                echo '</div>'; // Close .email-list

            } else {
                // No notifications available for this user
                echo "<div style='text-align: center;'>There is no notification</div>";
            }
        ?>
    </div>

</body>
</html>
