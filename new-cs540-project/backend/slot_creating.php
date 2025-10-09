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
$provider_id = $_SESSION['provider_profiles_id'];
$user_id = $_SESSION['user_id'];
$category_id = $_SESSION['provider_profiles_category_id'];


// Sanitize input:
$startDate = trim($startDate);
$startTime = trim($startTime);
$endDate = trim($endDate);
$endTime = trim($endTime);
$notes = trim($notes);


// Validate format (basic check)
if (!empty($startDate) && !empty($startTime)) {
    // Combine into datetime string
    $start_time = date('Y-m-d H:i:s', strtotime("$startDate $startTime"));
}

if (!empty($endDate) && !empty($endTime)) {
    // Combine into datetime string
    $end_time = date('Y-m-d H:i:s', strtotime("$endDate $endTime"));
}




// DSN and PDO setup
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, // use native prepared statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Sample input (replace with actual POST or validated data)
    
    $slot_id     = 1;

    // Insert query
    $stmt = $pdo->prepare("
        INSERT INTO appointment_slots (
            provider_id, category_id, start_time, end_time, notes
        ) VALUES (
            :provider_id, :category_id, :start_time, :end_time, :notes
        )
    ");

    // Bind and execute
    $stmt->execute([
        ':provider_id' => $provider_id,
        ':category_id' => $category_id,
        ':start_time'  => $start_time,
        ':end_time'    => $end_time,
        ':notes'    => $notes
    ]);

    $_SESSION['slot_message'] = "✅ Appointment Slot Successfully Created!";
} catch (PDOException $e) {
    // Log error securely and show generic message
    // echo $e->getMessage();
    // error_log("Database error: " . $e->getMessage());

    $queryString = "
    INSERT INTO appointment_slots (
        provider_id, category_id, start_time, end_time, notes
    ) VALUES (
        '$provider_id', '$category_id', '$start_time', '$end_time', '$notes'
    )
";

    $_SESSION['slot_message'] = $queryString . " " . $e->getMessage() . "❌ Failed to create appointment slot. Please try again!";
}

header("Location: ../slot_creating.php");
exit;
?>