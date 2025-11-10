<?php
session_start();

// Database configuration
$host = 'localhost';
$db   = 'cs540';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Get form input
$startDate = trim($_POST['start-date'] ?? '');
$startTime = trim($_POST['start-time'] ?? '');
$endDate   = trim($_POST['end-date'] ?? '');
$endTime   = trim($_POST['end-time'] ?? '');
$notes     = trim($_POST['notes'] ?? '');

// Session variables
$provider_id = $_SESSION['provider_profiles_id'] ?? null;
$category_id = $_SESSION['provider_profiles_category_id'] ?? null;

// Basic presence check
if (empty($provider_id)) {
    $_SESSION['slot_message'] = "❌ Provider not authenticated. Please log in.";
    header("Location: ../slot_creating.php");
    exit;
}
if (empty($startDate) || empty($startTime) || empty($endDate) || empty($endTime)) {
    $_SESSION['slot_message'] = "❌ Start and end date/time are required.";
    header("Location: ../slot_creating.php");
    exit;
}

// PDO setup
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // -------------------------------
    // Parse provider-local input
    // -------------------------------
    $startLocal = DateTime::createFromFormat('Y-m-d H:i', "$startDate $startTime");
    $endLocal   = DateTime::createFromFormat('Y-m-d H:i', "$endDate $endTime");

    if (!$startLocal || !$endLocal) {
        $_SESSION['slot_message'] = "❌ Invalid date/time format. Use YYYY-MM-DD and HH:MM.";
        header("Location: ../slot_creating.php");
        exit;
    }

    // Ensure start/end minute are multiples of 15
    $startMinutes = (int)$startLocal->format('i');
    $endMinutes   = (int)$endLocal->format('i');

    if ($startMinutes % 15 !== 0 || $endMinutes % 15 !== 0) {
        $_SESSION['slot_message'] = "❌ Start and end times must be on a 15-minute boundary.";
        header("Location: ../slot_creating.php");
        exit;
    }

    // Ensure duration > 0
    if ($endLocal <= $startLocal) {
        $_SESSION['slot_message'] = "❌ End time must be after start time.";
        header("Location: ../slot_creating.php");
        exit;
    }

    // Ensure start is in the future (server local)
    $now = new DateTime();
    if ($startLocal <= $now) {
        $_SESSION['slot_message'] = "❌ Cannot create a slot in the past.";
        header("Location: ../slot_creating.php");
        exit;
    }

    // Format times for DB
    $start_time = $startLocal->format('Y-m-d H:i:s');
    $end_time   = $endLocal->format('Y-m-d H:i:s');

    // --------------------------
    // Insert into DB (local time)
    // --------------------------
    $stmt = $pdo->prepare("
        INSERT INTO appointment_slots (
            provider_id, category_id, start_time, end_time, notes, created_at, updated_at
        ) VALUES (
            :provider_id, :category_id, :start_time, :end_time, :notes, NOW(), NOW()
        )
    ");

    $stmt->execute([
        ':provider_id' => $provider_id,
        ':category_id' => $category_id,
        ':start_time'  => $start_time,
        ':end_time'    => $end_time,
        ':notes'       => $notes
    ]);

    $_SESSION['slot_message'] = "✅ Appointment Slot Successfully Created!";
} catch (PDOException $e) {
    $errMsg = $e->getMessage() ?? '';

    // Friendly message for overlapping slot trigger
    if (stripos($errMsg, 'Overlapping appointment slot') !== false) {
        $_SESSION['slot_message'] = "❌ You already have a slot during this time. Please choose a different time.";
    } else {
        $_SESSION['slot_message'] = "❌ Failed to create appointment slot. Please try again!";
    }

    error_log("Database error (slot_creating): " . $errMsg);
} catch (Exception $e) {
    $_SESSION['slot_message'] = "Error: " . htmlspecialchars($e->getMessage() ?? '');
}

header("Location: ../slot_creating.php");
exit;
?>
