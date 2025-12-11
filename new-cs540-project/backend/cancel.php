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
$service_provider = trim($_POST['service_provider'] ?? '');
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

    if (empty($appointmentId) || !ctype_digit($appointmentId)) {
        if (!empty($slotId)) {
            // If the appointment is not booked by any customer yet (appointment_slot), 
            // then delete this appointment slot from the database:

            // Prepare and execute a secure DELETE query
            $stmt = $pdo->prepare("DELETE FROM appointment_slots WHERE id = :id");
            $stmt->execute([':id' => $slotId]);

            // Confirm deletion
            if ($stmt->rowCount() > 0) {
                $_SESSION['cancel_message'] =  "Appointment successfully cancelled.";
            } else {
                $_SESSION['cancel_message'] =  "Unable to cancel.";
            }
        }
    } else {
        // If the appointment is already booked by a customer, 
        // then update its status to “Cancelled”,
        // then update the username who cancelled it:
        // Use a prepared statement to prevent SQL injection
        $stmt = $pdo->prepare("UPDATE appointments SET status = :status, updated_by = :updated_by WHERE id = :id");
        $stmt->execute([
            ':status' => 'Cancelled',
            ':updated_by'     => $user_id,
            ':id'     => $appointmentId
        ]);

        // Optional: check if any row was updated
        if ($stmt->rowCount() > 0) {
            $_SESSION['cancel_message'] =  "Appointment successfully cancelled.";

            $email = getEmail($username);
            $content = getAppointmentEmailContent($appointmentId, $pdo);
            sendEmail($email, $content);
        } else {
            $_SESSION['cancel_message'] =  "Unable to cancel.";
        }
    }

} catch (Exception $e) {
    // Handle all errors cleanly
    error_log("Error updating appointment: " . $e->getMessage());
    echo "An error occurred. Please try again later.";
}

// Add to "notifications" table of the person who booked this appointment:
try {
    // Get user_id (id of the user who booked this appointment) of this appointment:
    $appointmentId = $_POST['appointment_id'] ?? null;
    $sql = "SELECT user_id FROM appointments WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $appointmentId]);

    $userId = $stmt->fetchColumn();

    if ($userId !== false) {
        // Add to "notifications" table with user_id value:
        $sql = "
            INSERT INTO notifications (user_id, appointment_id, created_at, notes)
            VALUES (:user_id, :appointment_id, NOW(), :notes)
        ";

        $finalNotes = "Your appointment " . '"' . $notes . '"' . " with " . $service_provider . " was canceled";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id'        => $userId,
            ':appointment_id' => $appointmentId,  // can be null
            ':notes'          => $finalNotes
        ]);
    } 

} catch (Exception $e) {

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
