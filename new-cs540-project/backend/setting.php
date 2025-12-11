<?php
session_start();

// Database configuration
$dsn = 'mysql:host=localhost;dbname=cs540;charset=utf8mb4';
$dbUser = 'root';
$dbPass = '';

// Get form values
$id = trim($_POST['id'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = trim($_POST['role'] ?? '');
$business_name = trim($_POST['business-name'] ?? '');
$category_id = trim($_POST['category'] ?? '');

// Basic server-side validation
if ($username === '') {
    $_SESSION['settings_message'] = "❌ Username cannot be empty.";
    header("Location: ../setting.php");
    exit;
}

if ($email === '' || strpos($email, '@') === false) {
    $_SESSION['settings_message'] = "❌ Please enter a valid email address.";
    header("Location: ../setting.php");
    exit;
}

try {
    // Secure PDO connection
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // ---------- CHECK IF USERNAME EXISTS ----------
    $sql = "SELECT id FROM users WHERE username = ? AND id != ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $id]);

    
    if ($stmt->fetch()) {
        $_SESSION['settings_message'] = "❌ Username already exists";
        header("Location: ../setting.php");
        exit;
    }
    

    // Update information for user:
    $updateSql = "
        UPDATE users
        SET username = :username,
            email = :email,
            role = :role
        WHERE id = :id
    ";

    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([
        ':username'      => $username,
        ':email'         => $email,
        ':role'          => $role,
        ':id'            => $id,
    ]);

    

    // For Business name: 
    // If $role = "service-provider", and $id is not in "provider_profiles" table yet 
    // -> user switch from Customer 
    // -> Service Provider: Insert Into provider_profiles values business_name:
    if ($role == "service-provider") {
        $stmt = $pdo->prepare("SELECT user_id FROM provider_profiles WHERE user_id = ?");
        $stmt->execute([$id]);

        
        if (!$stmt->fetch()) {
            // INSERT query:
            $sql = "INSERT INTO provider_profiles 
                    (user_id, business_name, category_id, timezone, created_at, updated_at)
                    VALUES (:user_id, :business_name, :category_id, 'America/Chicago', NOW(), NOW())";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':user_id'       => $id,
                ':business_name' => $business_name,
                ':category_id'   => $category_id
            ]);

            // Get the auto-increment ID
            $insertedId = $pdo->lastInsertId();

            // Store in session
            $_SESSION['provider_profiles_id'] = $insertedId;

        } else {
            // If $role = "service-provider", and $id is already in "provider_profiles" table,
            // Update "provider_profiles" table:
            $updateSql = "
                UPDATE provider_profiles
                SET business_name = :business_name,
                    category_id = :category_id
                WHERE user_id = :id
            ";

            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([
                ':business_name'    => $business_name,
                ':category_id'      => $category_id,
                ':id'               => $id
            ]);

            // 2. Retrieve provider_profiles.id for this user
            $getIdSql = "SELECT id FROM provider_profiles WHERE user_id = :id LIMIT 1";
            $getIdStmt = $pdo->prepare($getIdSql);
            $getIdStmt->execute([':id' => $id]);

            $providerProfile = $getIdStmt->fetch(PDO::FETCH_ASSOC);

            // 3. Save to session
            if ($providerProfile) {
                $_SESSION['provider_profiles_id'] = $providerProfile['id'];
            }
        }

        $_SESSION['user_role'] = "service-provider";
    } else if ($role == "customer") {
        $_SESSION['user_role'] = "customer";
    }



    // If $role = "customer", $id is in "provider_profiles" table 
    // -> user switch from Service Provider 
    // -> Customer: Do NOT delete from provider_profiles. Take NO action


    $_SESSION['settings_message'] = "✅ Settings updated successfully.";
    $_SESSION['user_role'] = $role;
    header("Location: ../setting.php");
    exit;

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $_SESSION['settings_message'] = "❌ An internal error occurred. Try again later.";
    header("Location: ../setting.php");
    exit;
}
?>
