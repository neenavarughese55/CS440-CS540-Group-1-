<?php
    // Prevent unauthorized access to the "My Account" page.
    // session_check.php verifies that the user is logged in and has a valid session.
    require 'include/session_check.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Account</title>

    <!-- Styling for the settings page and header -->
    <link rel="stylesheet" href="./css/setting.css">
    <link rel="stylesheet" href="./css/header.css">

    <!-- JavaScript controlling showing/hiding panels and form submission -->
    <script src="./js/setting.js"></script>
</head>

<body>

    <!-- Shared site navigation header -->
    <?php require 'include/header.php'; ?>

    <?php
        // Display any status/error message after saving settings
        if (isset($_SESSION['settings_message'])) {
            echo "<span class='errorMsg'>";
            echo $_SESSION['settings_message'];
            echo "</span>";
            unset($_SESSION['settings_message']); // Prevent repeating on refresh
        }

        // Ensure user is logged in; otherwise, stop loading additional account data
        if (!isset($_SESSION["user_id"])) {
            return;
        }

        // Load current user session details
        $id = $_SESSION["user_id"];
        $username = $_SESSION["username"];
        $email = "";
        $role = $_SESSION["user_role"];
        $business_name = "";
        $category_id = "";

        // Connect to the database
        $conn = new mysqli("localhost", "root", "", "cs540");

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Load user's main account information
        $sql = "SELECT id, username, email, role FROM users WHERE id = '" . $id . "'";
        $result = $conn->query($sql);

        // Fill in fields with existing database values
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $id       = htmlspecialchars($row["id"]);
                $username = htmlspecialchars($row["username"]);
                $email    = htmlspecialchars($row["email"]);
                $role     = htmlspecialchars($row["role"]);
            }
        }

        // Connection is intentionally kept open for later queries
        // $conn->close();
    ?>

    <!-- Page title -->
    <h2>My Account</h2>

    <!-- ======================== ACCOUNT SETTINGS FORM ======================== -->
    <form id="myForm" method="post" action="./backend/setting.php">

        <!-- Username input -->
        <div class="inline">
            <label>Username:</label>
            <input type="text" id="username" name="username" value=<?php echo $username?>>
        </div>

        <!-- Email input -->
        <div class="inline">
            <label>Email:</label>
            <input type="text" id="email" name="email" value=<?php echo $email?>>
        </div>

        <!-- Role dropdown -->
        <div class="inline">
            <label>Role:</label>

            <select id="role" name="role"
                <?php 
                    // Role selection is locked for admin users
                    $customerSelected = "";
                    $providerSelected = "";

                    if ($role == "admin") {
                        echo ' disabled';
                    } 
                    else if ($role == "customer") {
                        $customerSelected = " selected";
                    } 
                    else if ($role == "service-provider") {
                        $providerSelected = " selected";
                    }
                ?>
            >
                <option value="customer" <?php echo $customerSelected ?>>Customer</option>
                <option value="service-provider" <?php echo $providerSelected ?>>Service Provider</option>

                <?php
                    // Admin role shown if user is currently an admin
                    if ($role == "admin") {
                        echo '<option value="admin" selected>Admin</option>';
                    }
                ?>
            </select>
        </div>

        <!-- ======================== BUSINESS NAME PANEL ======================== -->
        <div class="inline" id="business-name-panel"
            <?php
                /**
                 * Show this panel only for service providers.
                 * If user is service provider, load business info from provider_profiles.
                 */
                if ($role != "service-provider") {
                    echo " style='display: none;' ";
                } else {
                    $sql = "SELECT business_name, category_id FROM provider_profiles WHERE user_id = '" . $id . "'";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $business_name = htmlspecialchars($row["business_name"]);
                            $category_id   = htmlspecialchars($row["category_id"]);
                        }
                    }
                }
            ?>
        >
            <label>Business Name:</label>
            <input type="text" id="business-name" name="business-name" 
                value="<?php echo htmlspecialchars($business_name); ?>">
        </div>
        <!-- End Business Name Panel -->

        <!-- ======================== CATEGORY PANEL ======================== -->
        <div class="inline" id="category-panel"
            <?php
                // Only show category panel for service providers
                if ($role != "service-provider") {
                    echo " style='display: none;' ";
                }
            ?>
        >
            <label>Category:</label>

            <select id="category" name="category">
                <?php
                    $categories = [];

                    // Load categories again (new DB connection)
                    $conn = new mysqli("localhost", "root", "", "cs540");

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    $sql = "SELECT id, name FROM categories";
                    $result = $conn->query($sql);

                    // Generate dropdown options and pre-select the user's category
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $categories[] = $row;

                            $cat_id   = htmlspecialchars($row["id"]);
                            $name     = htmlspecialchars($row["name"]);
                            $selected = ($cat_id == $category_id) ? " selected" : "";

                            echo "<option value=\"$cat_id\" $selected>$name</option>";
                        }
                    }

                    $conn->close();
                ?>
            </select>
        </div>
        <!-- End Category Panel -->

        <!-- Store user ID in hidden input for backend -->
        <input type="hidden" id="id" name="id" value="<?php echo htmlspecialchars($id); ?>">
    </form>
    <!-- ======================== END ACCOUNT SETTINGS FORM ======================== -->

    <!-- Save Button triggers JS to validate & submit form -->
    <div class="center-btn">
        <a href="" class="btn" id="save-btn">Save</a>    
    </div>

    <!-- Logout button -->
    <div class="center-btn">
        <a href="backend/logout.php" class="btn" id="logout-btn">Logout</a>    
    </div>

</body>
</html>
