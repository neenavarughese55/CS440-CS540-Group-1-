<?php
    // Start session to store user login data and error messages.
    session_start();
    
    // ============================
    // Get form input values safely
    // ============================
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Trim whitespace from input to avoid invalid login due to spaces.
    $username = trim($username);
    $password = trim($password);

    // ============================
    // Database connection settings
    // Using PDO for secure & modern database interaction.
    // ============================
    $dsn = 'mysql:host=localhost;dbname=cs540;charset=utf8mb4';
    $db_user = 'root';
    $db_pass = '';

    try {
        // Create PDO instance with error mode set to throw exceptions.
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    
        // ======================================
        // Check whether the username exists
        // ======================================
        $stmt = $pdo->prepare("
            SELECT id, password_hash, role, is_active
            FROM users 
            WHERE username = :username
            LIMIT 1
        ");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // ======================================================
        // Validate credentials: Does user exist & password match?
        // ======================================================
        if ($user && password_verify($password, $user['password_hash'])) {

            // Check account activation status:
            $isActive = $user['is_active'];
            if ($isActive != "1") {
                // User exists but account is inactive → Reject login
                $_SESSION['message'] = "User is not active. Unable to login!";
                header("Location: ../index.php");
                return;
            }

            // ===========================================
            // Store login info in session for later usage
            // ===========================================
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['user_role'] = $user['role'];

            // =============================
            // If user is a service provider,
            // load their provider profile ID
            // =============================
            if ($_SESSION['user_role'] == "service-provider") {

                $stmt = $pdo->prepare("
                    SELECT id, category_id 
                    FROM provider_profiles 
                    WHERE user_id = :user_id 
                    LIMIT 1
                ");
                $stmt->execute([':user_id' => $_SESSION['user_id']]);
                $provider_profiles = $stmt->fetch(PDO::FETCH_ASSOC);
            
                if ($provider_profiles) {
                    // Store provider profile details in session
                    $_SESSION['provider_profiles_id'] = $provider_profiles['id'];
                    $_SESSION['provider_profiles_category_id'] = $provider_profiles['category_id'];
                }
            }

            // =============================
            // Login successful → redirect
            // =============================
            $_SESSION['message'] = "Login Successfully!";
            header("Location: ../homepage.php");
            exit;

        } else {
            // Invalid login attempt (incorrect username or password)
            $_SESSION['message'] = "Invalid username or password.";
            header("Location: ../index.php");
            exit;
        }
    
    } catch (PDOException $e) {

        // Database errors displayed safely with escaping.
        echo "Database error: " . htmlspecialchars($e->getMessage());
    }
?>
