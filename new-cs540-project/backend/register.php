<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Get variables from POST:
$username = trim($_POST['registered-username'] ?? '');
$email = trim($_POST['registered-email'] ?? '');
$phoneNumber = trim($_POST['registered-phonenumber'] ?? '');
$pwd = $_POST['registered-password'] ?? '';
$role = $_POST['role'] ?? '';
$timezone = $_POST['timezone'] ?? 'UTC'; // client provided

if (!in_array($timezone, timezone_identifiers_list())) {
    // Fallback to UTC if invalid
    $timezone = 'UTC';
}

// Basic validation (you can expand as needed)
if (empty($email) || empty($pwd) || empty($username)) {
    $_SESSION['message'] = 'Please provide username, email and password.';
    header("Location: ..index.php");
    exit;
}

// Hash the password securely
$password_hash = password_hash($pwd, PASSWORD_DEFAULT);

$dsn = 'mysql:host=localhost;dbname=cs540;charset=utf8mb4';
$db_username = 'root';
$db_password = '';

try {
    $pdo = new PDO($dsn, $db_username, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        $_SESSION['message'] = "Email already registered.";
        header("Location: ..index.php");
        exit;
    }

    // Insert the user with timezone
    $stmt = $pdo->prepare("
        INSERT INTO users (email, password_hash, username, timezone, role, is_active)
        VALUES (:email, :password_hash, :username, :timezone, :role, :is_active)
    ");

    $stmt->execute([
        ':email' => $email,
        ':password_hash' => $password_hash,
        ':username' => $username,
        ':timezone' => $timezone,
        ':role' => $role,
        ':is_active' => 1
    ]);

    $user_id = $pdo->lastInsertId();

    // If role is "service-provider", insert into provider_profiles table (including timezone)
    if ($role === "service-provider") {
        $business_name = $_POST['business-name'] ?? null;
        $category_id = $_POST['category'] ?? null;

        $stmt = $pdo->prepare("
            INSERT INTO provider_profiles (user_id, business_name, category_id, timezone)
            VALUES (:user_id, :business_name, :category_id, :timezone)
        ");

        $stmt->execute([
            ':user_id' => $user_id,
            ':business_name' => $business_name,
            ':category_id' => $category_id,
            ':timezone' => $timezone
        ]);
    }

    $_SESSION['message'] = "Registered Successfully!";
} catch (PDOException $e) {
    // Don't reveal DB internals in production
    $_SESSION['message'] = "Database error: " . htmlspecialchars($e->getMessage());
}

header("Location: ..index.php");
exit;
?>
