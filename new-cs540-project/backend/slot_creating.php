// Codereview by Anton Cortes

/**
 * Slot Creation Handler
 *
 * This script processes the form submission for creating appointment slots.
 * It validates user authentication, checks required date/time inputs,
 * enforces business rules (15-minute intervals, future times, valid ranges),
 * and inserts the slot into the database using prepared statements.
 * Any errors are stored in the session and the user is redirected back
 * to the slot creation page.
 */


<?php

// Loads session_check.php, which starts the session, sets timezone, and redirects unauthenticated users to login.
require __DIR__ . '/../include/session_check.php';        

// Database configuration. These are local/dev defaults
$host = 'localhost';
$db   = 'cs540';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Get form input to create a new slot
$startDate = trim($_POST['start-date'] ?? '');
$startTime = trim($_POST['start-time'] ?? '');
$endDate   = trim($_POST['end-date'] ?? '');
$endTime   = trim($_POST['end-time'] ?? '');
$notes     = trim($_POST['notes'] ?? '');

// Session variables
// Expected in session: provider_profiles_id, provider_profiles_category_id
// Variables used to identify provider and category
$provider_id = $_SESSION['provider_profiles_id'] ?? null;
$category_id = $_SESSION['provider_profiles_category_id'] ?? null;

// Customer needs to be a service provider; only service providers should see the option to create a slot, however, just to make sure we added it
// Require provider authentication; redirect with message if not authenticated
if (empty($provider_id)) {
    // Protect route: redirect unauthenticated/unauthorized users back to slot page with message
    $_SESSION['slot_message'] = "❌ Provider not authenticated. Please log in.";
    header("Location: ../slot_creating.php");
    exit;
}

// Server-side required-field check. The client is expected to submit
// start-date/end-date as YYYY-MM-DD and start-time/end-time as HH:MM.
// This check prevents empty values from reaching the parser below.
// redirect with message if missing
if (empty($startDate) || empty($startTime) || empty($endDate) || empty($endTime)) {
    $_SESSION['slot_message'] = "❌ Start and end date/time are required.";
    header("Location: ../slot_creating.php");
    exit;
}

// PDO configuration for DB connection
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Create PDO connection
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Parse provider-local date/time strings into DateTime objects. Uses the timezone set by session_check.ph
    $startLocal = DateTime::createFromFormat('Y-m-d H:i', "$startDate $startTime");
    $endLocal   = DateTime::createFromFormat('Y-m-d H:i', "$endDate $endTime");
	
     // Reject if parsing failed
    if (!$startLocal || !$endLocal) {
        $_SESSION['slot_message'] = "❌ Invalid date/time format. Use YYYY-MM-DD and HH:MM.";
        header("Location: ../slot_creating.php");
        exit;
    }

    // Business rule: slots must start/end on 15-minute boundaries.
    // This enforces consistent time slots (00, 15, 30, 45).
    $startMinutes = (int)$startLocal->format('i');
    $endMinutes   = (int)$endLocal->format('i');

    if ($startMinutes % 15 !== 0 || $endMinutes % 15 !== 0) {
        $_SESSION['slot_message'] = "❌ Start and end times must be on a 15-minute boundary.";
        header("Location: ../slot_creating.php");
        exit;
    }

    // Ensure duration of an appoinments is > 0 (Ensure end time is after start time)
    if ($endLocal <= $startLocal) {
        $_SESSION['slot_message'] = "❌ End time must be after start time.";
        header("Location: ../slot_creating.php");
        exit;
    }

    // Ensure appoinment start time is in the future compared to current server time
    $now = new DateTime();
    if ($startLocal <= $now) {
        $_SESSION['slot_message'] = "❌ Cannot create a slot in the past.";
        header("Location: ../slot_creating.php");
        exit;
    }

    // Format DateTime objects for DB insertion
    $start_time = $startLocal->format('Y-m-d H:i:s');
    $end_time   = $endLocal->format('Y-m-d H:i:s');

    // Prepare and execute INSERT into appointment_slots
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

    // On success, set positive session message
    $_SESSION['slot_message'] = "✅ Appointment Slot Successfully Created!";
} catch (PDOException $e) {
    // Handle database exceptions; set user-facing message based on error content
    $errMsg = $e->getMessage() ?? '';

    // Prevent overlapping slots trigger
    if (stripos($errMsg, 'Overlapping appointment slot') !== false) {
        $_SESSION['slot_message'] = "❌ You already have a slot during this time. Please choose a different time.";
    } else {
        $_SESSION['slot_message'] = "❌ Failed to create appointment slot. Please try again!";
    }

    // Log DB error for debugging (not shown to user)
    error_log("Database error (slot_creating): " . $errMsg);
} catch (Exception $e) {
    // Handle any other exceptions and store escaped message in session
    $_SESSION['slot_message'] = "Error: " . htmlspecialchars($e->getMessage() ?? '');
}


// Redirect back to slot creation page
header("Location: ../slot_creating.php"); 
exit;
?>
