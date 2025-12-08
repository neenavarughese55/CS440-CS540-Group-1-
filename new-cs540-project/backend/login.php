<?php
require __DIR__ . '/../include/session_check.php';
    
    // Get form input
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Sanitize input
    $username = trim($username);
    $password = trim($password);

    // Connect to database using PDO
    $dsn = 'mysql:host=localhost;dbname=cs540;charset=utf8mb4';
    $db_user = 'root';
    $db_pass = '';


    try {
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    
        // Check if username exists
        $stmt = $pdo->prepare("SELECT id, password_hash, role FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($user && password_verify($password, $user['password_hash'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['user_role'] = $user['role'];
            // echo "user_role: " . $_SESSION['user_role'];
            // return;

            if ($_SESSION['user_role'] == "service-provider") {
                // Get provider_profiles_id:
                $stmt = $pdo->prepare("SELECT id, category_id FROM provider_profiles WHERE user_id = :user_id LIMIT 1");
                $stmt->execute([':user_id' => $_SESSION['user_id']]);
                $provider_profiles = $stmt->fetch(PDO::FETCH_ASSOC);
            
                if ($provider_profiles) {
                    $_SESSION['provider_profiles_id'] = $provider_profiles['id'];
                    $_SESSION['provider_profiles_category_id'] = $provider_profiles['category_id'];
                    // echo "provider_profiles_id: " . $_SESSION['provider_profiles_id'];
                }
            }


            echo "✅ Login successful!";
            $_SESSION['message'] = "Login Successfully!";
            header("Location: ../homepage.php");
            exit;
        } else {
            $_SESSION['message'] = "Invalid username or password.";
            header("Location: ../index.php");
            exit;
        }
    
    } catch (PDOException $e) {
        echo "Database error: " . htmlspecialchars($e->getMessage());
    }


    
    
?>