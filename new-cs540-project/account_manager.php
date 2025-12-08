<?php
    require 'include/session_check.php';
    
    // Show up all PHP errors for debugging:
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

?>


<!DOCTYPE html>
<html>
<head>
    <title>Account Manager</title>
    <link rel="stylesheet" href="./css/booking.css">
    <link rel="stylesheet" href="./css/account_manager.css">
    <link rel="stylesheet" href="./css/header.css">
    <script src="./js/account_manager.js"></script>
   
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

    <h3 style="text-align: center;">Manage Accounts</h3>

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
            if ($user_role == "admin") {
                $sql = "SELECT id, username, email, role, is_active, created_at, updated_at
                        FROM `users`";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $rows = [];
                    while ($row = $result->fetch_assoc()) {
                        $rows[] = $row;
                    }

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

                    foreach ($rows as $row) {
                        $id = htmlspecialchars($row["id"] ?? '', ENT_QUOTES, 'UTF-8');
                        $username = htmlspecialchars($row["username"] ?? '', ENT_QUOTES, 'UTF-8');
                        $email = htmlspecialchars($row["email"] ?? '', ENT_QUOTES, 'UTF-8');
                        $role = htmlspecialchars($row["role"] ?? '', ENT_QUOTES, 'UTF-8');
                        $isActive = htmlspecialchars($row["is_active"] ?? '', ENT_QUOTES, 'UTF-8');
                        $createdAt = htmlspecialchars($row["created_at"] ?? '', ENT_QUOTES, 'UTF-8');
                        $updatedAt = htmlspecialchars($row["updated_at"] ?? '', ENT_QUOTES, 'UTF-8');

                        // Role:
                        if ($role == "service-provider") {
                            $role = "Service Provider";
                        } else if ($role == "customer") {
                            $role = "Customer";
                        } else if ($role == "admin") {
                            $role = "Admin";
                        }

                        $class = "";
                        $btnName = "";

                        if ($isActive == "1") {
                            $isActive = "Active";
                            $class = "deactivate-btn";
                            $btnName = "Deactivate";
                        } else {
                            $isActive = "Inactive";
                            $class = "activate-btn";
                            $btnName = "Activate";
                        }

                        $createdAt = formatDateTime($createdAt);
                        $updatedAt = formatDateTime($updatedAt);

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

                    echo "</tbody></table>";
                } else {
                    echo "<p>No appointment slots found.</p>";
                }

                $conn->close();
            } // End if the user is Admin
        }
    ?>
    </div>

</body>
</html>


