<?php
require __DIR__ . '/../include/session_check.php';

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
$username = trim($_POST['username'] ?? '');

// Basic checks
if (empty($user_id)) {
    $_SESSION['booking_message'] = "❌ You must be logged in to book an appointment.";
    header("Location: ../booking.php");
    exit;
}


// ✅ Always use PDO for modern, secure database interaction
$dsn = 'mysql:host=localhost;dbname=cs540;charset=utf8mb4';
$dbUser = 'root';
$dbPass = '';

try {
    // Create PDO instance with secure settings
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Enable exceptions
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Use real prepared statements
    ]);

    // Suppose you received an appointment ID safely (e.g., from POST)
    $appointmentId = $_POST['appointment_id'] ?? null;
    $slotId = $_POST['slot_id'] ?? null;
    $_SESSION['cancel_message'] =  "Unable to cancel.";

    /* --- START: minimal insertion implementing cancellation windows & auth --- */
    if (empty($appointmentId) || !ctype_digit($appointmentId)) {
        // No appointment id -> attempt to delete an unbooked slot (only provider owner can delete)
        if (!empty($slotId)) {
            // verify slot exists and belongs to provider in session
            $slotStmt = $pdo->prepare("SELECT provider_id FROM appointment_slots WHERE id = :id LIMIT 1");
            $slotStmt->execute([':id' => $slotId]);
            $slotRow = $slotStmt->fetch();

            if (!$slotRow) {
                $_SESSION['cancel_message'] = "Unable to cancel: slot not found.";
            } else {
                $provider_profiles_id = $_SESSION['provider_profiles_id'] ?? null;

                // ensure no appointment is already booked for this slot
                $countStmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE slot_id = :slot_id");
                $countStmt->execute([':slot_id' => $slotId]);
                $count = (int)$countStmt->fetchColumn();

                if ($count > 0) {
                    $_SESSION['cancel_message'] = "❌ Cannot delete slot: there is already a booked appointment.";
                } elseif ($provider_profiles_id === null || $provider_profiles_id != $slotRow['provider_id']) {
                    $_SESSION['cancel_message'] = "❌ Only the provider who owns this slot can delete it.";
                } else {
                    $del = $pdo->prepare("DELETE FROM appointment_slots WHERE id = :id");
                    $del->execute([':id' => $slotId]);
                    if ($del->rowCount() > 0) {
                        $_SESSION['cancel_message'] = "✅ Appointment slot successfully deleted.";
                    } else {
                        $_SESSION['cancel_message'] = "Unable to cancel.";
                    }
                }
            }
        }
    } else {
        // appointmentId provided -> attempt to cancel booked appointment
        $apptStmt = $pdo->prepare("
            SELECT id, user_id AS customer_user_id, provider_id, start_time, status
            FROM appointments
            WHERE id = :id
            LIMIT 1
        ");
        $apptStmt->execute([':id' => $appointmentId]);
        $appt = $apptStmt->fetch();

        if (!$appt) {
            $_SESSION['cancel_message'] = "Unable to cancel: appointment not found.";
        } else {
            // local server time convention (same as booking.php)
            try {
                $now = new DateTime();
                $startDt = new DateTime($appt['start_time']);
            } catch (Exception $ex) {
                $_SESSION['cancel_message'] = "❌ Invalid appointment datetime.";
                $startDt = null;
            }

            $secondsToStart = $startDt ? ($startDt->getTimestamp() - $now->getTimestamp()) : PHP_INT_MAX;

            // roles
            $isCustomer = ($appt['customer_user_id'] == $user_id);
            $isProvider = (isset($_SESSION['provider_profiles_id']) && $_SESSION['provider_profiles_id'] == $appt['provider_id']);

            if (!$isCustomer && !$isProvider) {
                $_SESSION['cancel_message'] = "❌ You are not authorized to cancel this appointment.";
            } elseif (strtolower($appt['status']) === 'cancelled') {
                $_SESSION['cancel_message'] = "❗ Appointment is already cancelled.";
            } elseif ($isCustomer && $secondsToStart < 2 * 3600) {
                $_SESSION['cancel_message'] = "❌ Too late to cancel — customers must cancel at least 2 hours before the appointment.";
            } elseif ($isProvider && $secondsToStart < 24 * 3600) {
                $_SESSION['cancel_message'] = "❌ Too late to cancel — providers must cancel at least 24 hours before the appointment.";
            } else {
                // allowed: mark appointment cancelled
                $stmt = $pdo->prepare("UPDATE appointments SET status = :status, updated_by = :updated_by WHERE id = :id");
                $stmt->execute([
                    ':status' => 'Cancelled',
                    ':updated_by' => $user_id,
                    ':id' => $appointmentId
                ]);

                if ($stmt->rowCount() > 0) {
                    $_SESSION['cancel_message'] = "Appointment successfully cancelled.";

                    // notify customer (keeps your existing functions/flow)
                    $email = getEmail($username);
                    $content = getAppointmentEmailContent($appointmentId, $pdo);
                    sendEmail($email, $content);
                } else {
                    $_SESSION['cancel_message'] = "Unable to cancel.";
                }
            }
        }
    }
    /* --- END: minimal insertion implementing cancellation windows & auth --- */

} catch (Exception $e) {
    // Handle all errors cleanly
    error_log("Error updating appointment: " . $e->getMessage());
    echo "An error occurred. Please try again later.";
}


// Send email to customer:
function sendEmail($email, $content) {
    $subject = "Appointment Cancelled";
    $headers = "From: doquang123400@gmail.com";

    if(mail($email, $subject, $content, $headers)){
        echo "Email sent successfully!";
    } else {
        echo "Email sending failed.";
    }
}


// End Send email to customer

// Function to get email by username
function getEmail($username) {
    global $dsn, $dbUser, $dbPass;

    try {
        $pdo = new PDO($dsn, $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT email FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $email = $stmt->fetchColumn();

        return $email ?: null; // returns null if username not found

    } catch (PDOException $e) {
        // Handle error
        echo "Database error: " . $e->getMessage();
        return null;
    }
}



function getAppointmentEmailContent($appointmentID, $pdo) {
    // Prepare SQL query
    $sql = "SELECT a.id, a.provider_id, pp.business_name, c.id as category_id, 
                   c.name as category_name, a.notes, start_time, end_time 
            FROM appointments a
            LEFT JOIN provider_profiles pp ON a.provider_id = pp.id
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.id = :appointmentID";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['appointmentID' => $appointmentID]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Build plain-text email content
    if ($row) {
        $slotId = $row["id"];
        $business_name = $row["business_name"];
        $category_name = $row["category_name"];
        $notes = $row["notes"];
        $start = date("M d, Y H:i", strtotime($row["start_time"]));
        $end = date("M d, Y H:i", strtotime($row["end_time"]));

        $emailContent = "Appointment Details:\n";
        $emailContent .= "ID: $slotId\n";
        $emailContent .= "Provider Name: $business_name\n";
        $emailContent .= "Category: $category_name\n";
        $emailContent .= "Notes: $notes\n";
        $emailContent .= "Start Time: $start\n";
        $emailContent .= "End Time: $end\n";
    } else {
        $emailContent = "No appointment found for ID $appointmentID.";
    }

    return $emailContent;
}


header("Location: ../view-appointments.php");
exit;
?>
