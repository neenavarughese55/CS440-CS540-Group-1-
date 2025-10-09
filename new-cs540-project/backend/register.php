<?php
    session_start(); // To transfer every $_SESSION[] variable

    // Show up all PHP errors for debugging:
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    // Get variables from index.php:
    $username = $_POST['registered-username'] ?? '';
    $email = $_POST['registered-email'] ?? '';
    $phoneNumber = $_POST['registered-phonenumber'] ?? '';
    $pwd = $_POST['registered-password'] ?? '';
    $role = $_POST['role'] ?? '';
    

    // Hash the password securely
    $password_hash = password_hash($pwd, PASSWORD_DEFAULT);

    $is_active = 1;
    // End get variables



    $dsn = 'mysql:host=localhost;dbname=cs540;charset=utf8mb4';
    $db_username = 'root';
    $db_password = '';

    try {
        $pdo = new PDO($dsn, $db_username, $db_password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Check if username already existed in the 'users' table:
         
        // End Check

        // Add registered user to the DB:
        $stmt = $pdo->prepare("
            INSERT INTO users (email, password_hash, username, role, is_active)
            VALUES (:email, :password_hash, :username, :role, :is_active)
        ");

        $stmt->execute([
            ':email' => $email,
            ':password_hash' => $password_hash,
            ':username' => $username,
            ':role' => $role,
            ':is_active' => $is_active
        ]);

        $user_id = $pdo->lastInsertId();

        // If role is "service-provider", insert into “provider_profiles” table:
        if ($role == "service-provider") {

            $business_name = $_POST['business-name'] ?? '';
            $category_id = $_POST['category'] ?? '';

            $stmt = $pdo->prepare("
                INSERT INTO provider_profiles (user_id, business_name, category_id)
                VALUES (:user_id, :business_name, :category_id)
            ");

            $stmt->execute([
                ':user_id' => $user_id,
                ':business_name' => $business_name,
                ':category_id' => $category_id
            ]);
        }

        echo "User inserted successfully!";
        $_SESSION['message'] = "Registered Successfully!";
    } catch (PDOException $e) {
        echo "Database error: " . htmlspecialchars($e->getMessage());
        $_SESSION['message'] = $e->getMessage() . " Failed to register!";
    }

    
    header("Location: ../index.php");
    exit;
?>