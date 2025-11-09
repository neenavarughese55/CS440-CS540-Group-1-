<?php
session_start();

// Database configuration
$host = 'localhost';
$db   = 'cs540';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Get form input:
$startDate = $_POST['start-date'] ?? '';
$startTime = $_POST['start-time'] ?? '';
$endDate = $_POST['end-date'] ?? '';
$endTime = $_POST['end-time'] ?? '';
$notes = $_POST['notes'] ?? '';

// NOTE: Confirm what these session vars contain in your app:
// - If provider_profiles_id == provider_profiles.user_id (the user's id), keep as-is.
// - If provider_profiles_id == provider_profiles.id (profile PK), see the comment below.
$provider_id = $_SESSION['provider_profiles_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;
$category_id = $_SESSION['provider_profiles_category_id'] ?? null;

// Sanitize input:
$startDate = trim($startDate);
$startTime = trim($startTime);
$endDate = trim($endDate);
$endTime = trim($endTime);
$notes = trim($notes);

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

// DSN and PDO setup
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // -------------------------------
    // Fetch provider timezone safely
    // -------------------------------
    $provTz = 'UTC'; // fallback

    // If provider_profiles_id stores profile PK instead of user_id, change WHERE clause to "WHERE id = :pid".
    $tzStmt = $pdo->prepare("SELECT timezone FROM provider_profiles WHERE user_id = :uid LIMIT 1");
    $tzStmt->execute([':uid' => $provider_id]);
    $tzRow = $tzStmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($tzRow['timezone'])) {
        try {
            // validate timezone identifier
            $tzTest = new DateTimeZone($tzRow['timezone']);
            $provTz = $tzRow['timezone'];
        } catch (Exception $ex) {
            // invalid timezone in DB, keep UTC fallback
            $provTz = 'UTC';
        }
    }

    // -------------------------------
    // Parse provider-local input and convert to UTC
    // -------------------------------
    $localStartStr = $startDate . ' ' . $startTime; // expected "YYYY-MM-DD HH:MM" or with seconds
    $localEndStr   = $endDate . ' ' . $endTime;

    // create timezone object
    $tzObj = new DateTimeZone($provTz);

    // Prefer 'Y-m-d H:i' (no seconds) then fall back to 'Y-m-d H:i:s'
    $startLocal = DateTime::createFromFormat('Y-m-d H:i', $localStartStr, $tzObj);
    if (!$startLocal) {
        $startLocal = DateTime::createFromFormat('Y-m-d H:i:s', $localStartStr, $tzObj);
    }
    $endLocal = DateTime::createFromFormat('Y-m-d H:i', $localEndStr, $tzObj);
    if (!$endLocal) {
        $endLocal = DateTime::createFromFormat('Y-m-d H:i:s', $localEndStr, $tzObj);
    }

    if (!$startLocal || !$endLocal) {
        $_SESSION['slot_message'] = "❌ Invalid date/time format. Use YYYY-MM-DD and HH:MM (browser date/time inputs are recommended).";
        header("Location: ../slot_creating.php");
        exit;
    }

    // convert to UTC (so DB stores UTC datetimes)
    $startLocal->setTimezone(new DateTimeZone('UTC'));
    $endLocal->setTimezone(new DateTimeZone('UTC'));
    $start_time = $startLocal->format('Y-m-d H:i:s');
    $end_time   = $endLocal->format('Y-m-d H:i:s');

    // --------------------------
    // Validate times in UTC: future & start < end
    // --------------------------
    $nowUtc = new DateTime('now', new DateTimeZone('UTC'));
    $startDateTime = new DateTime($start_time, new DateTimeZone('UTC'));
    $endDateTime   = new DateTime($end_time,   new DateTimeZone('UTC'));

    // Start must be strictly in the future (UTC)
    if ($startDateTime <= $nowUtc) {
        $_SESSION['slot_message'] = "❌ Cannot create a slot in the past. Please pick a start time in the future (your local time).";
        header("Location: ../slot_creating.php");
        exit;
    }

    // End must be after start
    if ($endDateTime <= $startDateTime) {
        $_SESSION['slot_message'] = "❌ End time must be after start time.";
        header("Location: ../slot_creating.php");
        exit;
    }

    // Optional: ensure start/end minute are multiples of 15
    /*
    $startMin = (int)$startDateTime->format('i');
    $endMin = (int)$endDateTime->format('i');
    if ($startMin % 15 !== 0 || $endMin % 15 !== 0) {
        $_SESSION['slot_message'] = "❌ Start and end times must be multiples of 15 minutes.";
        header("Location: ../slot_creating.php");
        exit;
    }
    */

    // --------------------------
    // Insert into DB (UTC datetimes)
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
    // Detect overlapping appointment slot trigger (friendly message)
    if (strpos($e->getMessage(), 'Overlapping appointment slot') !== false
        || strpos($e->getMessage(), 'Provider already has an overlapping appointment') !== false) {
        $_SESSION['slot_message'] = "❌ You already have an appointment during that time. Failed to create appointment slot. Please try again!";
    } else {
        $_SESSION['slot_message'] = "❌ Failed to create appointment slot. Please try again!";
        error_log("Database error (slot_creating): " . $e->getMessage());
    }
} catch (Exception $e) {
    // Other errors (e.g. timezone parsing)
    $_SESSION['slot_message'] = "Error: " . htmlspecialchars($e->getMessage());
}

header("Location: ../slot_creating.php");
exit;
?>
