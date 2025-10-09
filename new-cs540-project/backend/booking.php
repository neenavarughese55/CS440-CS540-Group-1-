<?php
session_start();

// Database configuration
$host = 'localhost';
$db   = 'cs540';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Get form input:
$slot_id = $_POST['slot_id'] ?? '';
$user_id = $_SESSION['user_id'];
$category_id = $_POST['category_id'] ?? '';
$provider = $_POST['provider_id'] ?? '';
// $startDate = $_POST['start-date'] ?? '';
$start_time = $_POST['start_time'] ?? '';
// $endDate = $_POST['end-date'] ?? '';
$end_time = $_POST['end_time'] ?? '';
$notes = $_POST['notes'] ?? '';


// Sanitize input:
$provider_id = trim($provider);
$startDate = trim($startDate);
$startTime = trim($startTime);
$endDate = trim($endDate);
$endTime = trim($endTime);
$notes = trim($notes);


// Validate format (basic check)
// if (!empty($startDate) && !empty($startTime)) {
//     // Combine into datetime string
//     $start_time = date('Y-m-d H:i:s', strtotime("$startDate $startTime"));
// }

// if (!empty($endDate) && !empty($endTime)) {
//     // Combine into datetime string
//     $end_time = date('Y-m-d H:i:s', strtotime("$endDate $endTime"));
// }




// DSN and PDO setup
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, // use native prepared statements
];

$queryString = "
    INSERT INTO appointments (
        slot_id, user_id, provider_id, category_id,
        start_time, end_time, notes,
        created_at, updated_at
    ) VALUES (
        '$slot_id', '$user_id', '$provider_id', '$category_id',
        '$start_time', '$end_time', '$notes',
        CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
    )
";

echo $queryString;


try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Insert query
    $stmt = $pdo->prepare("
        INSERT INTO appointments (
            slot_id, user_id, provider_id, category_id,
            start_time, end_time, notes,
            created_at, updated_at
        ) VALUES (
            :slot_id, :user_id, :provider_id, :category_id,
            :start_time, :end_time, :notes,
            CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
        )
    ");

    // Bind and execute
    $stmt->execute([
        ':slot_id'     => $slot_id,
        ':user_id'     => $user_id,
        ':provider_id' => $provider_id,
        ':category_id' => $category_id,
        ':start_time'  => $start_time,
        ':end_time'    => $end_time,
        ':notes'       => $notes
    ]);

    $_SESSION['booking_message'] = "✅ Appointment successfully booked!";
} catch (PDOException $e) {
    // Log error securely and show generic message
    // echo $e->getMessage();
    // error_log("Database error: " . $e->getMessage());

    $_SESSION['booking_message'] = $queryString . " " . $e->getMessage() . "❌ Failed to book appointment. Please try again!";
}

header("Location: ../booking.php");
exit;
?>
