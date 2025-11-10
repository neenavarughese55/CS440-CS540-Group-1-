<?php
session_start();

// Database configuration
$host = 'localhost';
$db   = 'cs540';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Get form input
$slot_id = isset($_POST['slot_id']) ? (int)$_POST['slot_id'] : 0;
$user_id = $_SESSION['user_id'] ?? null;
$category_id = $_POST['category_id'] ?? null; // optional
$notes = trim($_POST['notes'] ?? '');

// Basic checks
if (empty($user_id)) {
    $_SESSION['booking_message'] = "❌ You must be logged in to book an appointment.";
    header("Location: ../booking.php");
    exit;
}
if ($slot_id <= 0) {
    $_SESSION['booking_message'] = "❌ Invalid slot selected.";
    header("Location: ../booking.php");
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

    // Begin transaction
    $pdo->beginTransaction();

    // 1) Fetch slot row with FOR UPDATE
    $slotStmt = $pdo->prepare("
        SELECT id, provider_id, category_id, start_time, end_time, capacity, is_active
        FROM appointment_slots
        WHERE id = :slot_id
        FOR UPDATE
    ");
    $slotStmt->execute([':slot_id' => $slot_id]);
    $slot = $slotStmt->fetch();

    if (!$slot) {
        $pdo->rollBack();
        $_SESSION['booking_message'] = "❌ Slot not found.";
        header("Location: ../booking.php");
        exit;
    }
    if ((int)$slot['is_active'] !== 1) {
        $pdo->rollBack();
        $_SESSION['booking_message'] = "❌ This slot is not active.";
        header("Location: ../booking.php");
        exit;
    }
    if (empty($slot['start_time']) || empty($slot['end_time'])) {
        $pdo->rollBack();
        $_SESSION['booking_message'] = "❌ Slot has invalid times.";
        header("Location: ../booking.php");
        exit;
    }

    // 2) Parse times (assume they are local already)
    try {
        $startDt = new DateTime($slot['start_time']);
        $endDt   = new DateTime($slot['end_time']);
    } catch (Exception $ex) {
        $pdo->rollBack();
        $_SESSION['booking_message'] = "❌ Slot has invalid datetime format.";
        header("Location: ../booking.php");
        exit;
    }

    // 3) Ensure slot is in the future (local time)
    $now = new DateTime();
    if ($startDt <= $now) {
        $pdo->rollBack();
        $_SESSION['booking_message'] = "❌ Cannot book a slot in the past.";
        header("Location: ../booking.php");
        exit;
    }

    // 4) Check overlapping appointments
    $overlapStmt = $pdo->prepare("
        SELECT 1 FROM appointments
        WHERE user_id = :user_id
          AND :slot_start < end_time
          AND :slot_end > start_time
        LIMIT 1
    ");
    $overlapStmt->execute([
        ':user_id'    => $user_id,
        ':slot_start' => $slot['start_time'],
        ':slot_end'   => $slot['end_time']
    ]);
    if ($overlapStmt->fetch()) {
        $pdo->rollBack();
        $_SESSION['booking_message'] = "❌ You already have an appointment during that time. Please choose another slot.";
        header("Location: ../booking.php");
        exit;
    }

    // 5) Check slot capacity
    $countStmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM appointments WHERE slot_id = :slot_id");
    $countStmt->execute([':slot_id' => $slot_id]);
    $count = (int)$countStmt->fetchColumn();
    $capacity = (int)$slot['capacity'];
    if ($count >= $capacity) {
        $pdo->rollBack();
        $_SESSION['booking_message'] = "❌ This slot is already full.";
        header("Location: ../booking.php");
        exit;
    }

    // 6) Insert appointment (local time stored as-is)
    $insert = $pdo->prepare("
        INSERT INTO appointments (
            slot_id, user_id, provider_id, category_id,
            start_time, end_time, notes, created_at, updated_at
        ) VALUES (
            :slot_id, :user_id, :provider_id, :category_id,
            :start_time, :end_time, :notes, NOW(), NOW()
        )
    ");
    $insert->execute([
        ':slot_id'     => $slot['id'],
        ':user_id'     => $user_id,
        ':provider_id' => $slot['provider_id'],
        ':category_id' => $slot['category_id'] ?? $category_id,
        ':start_time'  => $slot['start_time'],
        ':end_time'    => $slot['end_time'],
        ':notes'       => $notes
    ]);

    $pdo->commit();

    // 7) Display confirmation (local time)
    $_SESSION['booking_message'] = "✅ Appointment successfully booked! Time: {$slot['start_time']} – {$slot['end_time']}";

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    $_SESSION['booking_message'] = "❌ Failed to book appointment. Please try again.";
    error_log("Database error (booking.php): " . $e->getMessage());
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    $_SESSION['booking_message'] = "Error: " . htmlspecialchars($e->getMessage() ?? '');
}

header("Location: ../booking.php");
exit;
?>
