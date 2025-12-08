<?php
    require 'include/session_check.php';
    
    // Show up all PHP errors for debugging:
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

?>


<!DOCTYPE html>
<html>
<head>
    <title>Generate Report</title>
    <link rel="stylesheet" href="./css/report.css">
    <link rel="stylesheet" href="./css/header.css">
   
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

    <!-- User Report: -->
    <h3>User Report</h3>

    <div class="form-group" id="category-container">
        <label>Category</label>
        <select name="category" id="category">
            <?php
                $categories = [];

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
                        $categories[] = $row;
                        $id = htmlspecialchars($row["id"]);
                        $name = htmlspecialchars($row["name"]);
                        echo "<option value=\"$id\">$name</option>";
                    }
                }

                $conn->close();
            ?>
        </select>

        <!-- User -->
        <label>User</label>
        <select>
            <option value="ALL">All Users</option>
            <?php
                $users = [];

                // Database connection
                $conn = new mysqli("localhost", "root", "", "cs540");

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Query provider profiles
                $sql = "SELECT id, username FROM users ORDER BY username";
                $result = $conn->query($sql);

                // Populate dropdown
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $users[] = $row;
                        $id = htmlspecialchars($row["id"]);
                        $username = htmlspecialchars($row["username"]);
                        echo "<option value=\"$id\">$username</option>";
                    }
                }

                $conn->close();
            ?>
        </select>
        <!-- From -->
        <label for="from">From</label>
        <input type="date" id="from" name="from" required>

        <!-- To -->
        <label for="to">To</label>
        <input type="date" id="to" name="to" required>

        <button type="button" class="run-btn">Run Report</button>
    </div>

    <!-- Appointment Report -->
    <h3 style="margin-top: 30px;">Appointment Report</h3>

    <div class="form-group" id="category-container">
        <label>Category</label>
        <select name="category" id="category">
            <?php
                // Populate dropdown
                foreach ($categories as $row) {
                    $id = htmlspecialchars($row["id"]);
                    $name = htmlspecialchars($row["name"]);
                    echo "<option value=\"$id\">$name</option>";
                }             
            ?>
        </select>

        <!-- User -->
        <label>User</label>
        <select name="user" id="user">
            <option value="ALL">All Users</option>
            <?php
                // Populate dropdown
                foreach ($users as $r) {
                    $id = htmlspecialchars($r["id"]);
                    $username = htmlspecialchars($r["username"]);
                    echo "<option value=\"$id\">$name</option>";
                }             
            ?>
        </select>

        <!-- From -->
        <label for="from">From</label>
        <input type="date" id="from" name="from" required>

        <!-- To -->
        <label for="to">To</label>
        <input type="date" id="to" name="to" required>

        <button type="button" class="run-btn">Run Report</button>

    </div>

    

</body>
</html>


