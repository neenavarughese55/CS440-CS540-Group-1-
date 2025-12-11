<?php
// require 'include/session_check.php';
session_start();

if (!isset($_POST['username'])) {
    die("No username provided");
}

/**
 * Send account deactivation email.
 *
 * @param string $toEmail         Recipient's email address.
 * @param string $username        Deactivated account username.
 * @param string $deactivatedAt   Datetime string.
 * @param string $deactivatedBy   Who performed the deactivation.
 * @param string $appointmentsHtml Optional HTML list of appointments (can be empty).
 */
function sendDeactivationEmail(
    string $toEmail,
    string $username,
    string $deactivatedAt,
    string $deactivatedBy,
    string $appointmentsHtml = ''
): bool {
    $subject = "Your account has been deactivated.";

    // Build optional appointments section
    $appointmentsSection = '';
    if (!empty($appointmentsHtml)) {
        $appointmentsSection = "
            <p><strong>All your appointments you have created have been canceled.</strong></p>
            <p>Below is the list of all associated appointments:</p>
            {$appointmentsHtml}
        ";
    } else {
        $appointmentsSection = "
            <p><strong>All your appointments you have created have been canceled.</strong></p>
            <p>(Optional: In the future, this section can list all associated appointments.)</p>
        ";
    }

    // Email body (HTML)
    $message = "
    <html>
    <head>
        <title>Notification for users</title>
    </head>
    <body>
        <h2>Notification for users</h2>

        <h3>Your account has been deactivated.</h3>
        <p>
            Your account has been deactivated at <strong>Let’s Book</strong>.
            All appointments that your account has ever booked, and all appointments that have been
            booked to your account, have been cancelled.
        </p>

        <ul>
            <li><strong>Account username:</strong> " . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . "</li>
            <li><strong>Deactivated At:</strong> " . htmlspecialchars($deactivatedAt, ENT_QUOTES, 'UTF-8') . "</li>
            <li><strong>Deactivated By:</strong> " . htmlspecialchars($deactivatedBy, ENT_QUOTES, 'UTF-8') . "</li>
        </ul>

        {$appointmentsSection}
    </body>
    </html>
    ";

    // Headers for HTML email
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Let's Book <no-reply@letsbook.local>\r\n";

    // Use PHP's mail() function
    return mail($toEmail, $subject, $message, $headers);
}

$isActive = $_POST['isActive'];
$username = $_POST['username'];
$userid = $_POST['userid'];
$role = $_POST['role'];

// PDO setup
// Database configuration
$host = 'localhost';
$db   = 'cs540';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Update Active Status of the user:
    $sql = "UPDATE users SET is_active = :isActive WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':isActive', $isActive, PDO::PARAM_STR);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);

    if ($stmt->execute()) {
        if ($isActive == 1) {
            echo "Activated Successfully!";
        } else {
            echo "Deactivated Successfully!";
        }
        
    } else {
        echo "Error updating appointment";
    }

    // Cancel all appointments of the user:
    // If user is Customer, make all appointments that the user has ever booked, available again for everyone else:
    if ($role == "Customer") {
        $sql = "DELETE FROM appointments WHERE user_id = :userid";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':userid', (int)$userid, PDO::PARAM_INT);
        if ($stmt->execute()) {
            // echo "yes 1";
        } else {
            // echo "no 1";
        }
    }
    // If user is Service Provider, cancel all appointments that has been booked to this user:
    else if ($role == "Service Provider") {
        $sql = "UPDATE appointments SET status = 'Cancelled' WHERE provider_id = :providerid";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':providerid', (int)$userid, PDO::PARAM_INT);
        if ($stmt->execute()) {
            // echo "yes 2";
        } else {
            // echo "no 2";
        }
    }

    // Send notification to this user and all other related users:
    $deactivatedAt = date('Y-m-d H:i:s');
    $deactivatedBy = $_SESSION['username'] ?? 'System';

    // Optional: build an HTML list of appointments here and pass as 5th argument.
    $appointmentsHtml = ""; // e.g. "<ul><li>...</li></ul>"

    // For now, send to the fixed address "doquang123400@gmail.com" as you requested:
    $emailSent = sendDeactivationEmail(
        "doquang123400@gmail.com",
        $username,
        $deactivatedAt,
        $deactivatedBy,
        $appointmentsHtml
    );

    if (!$emailSent) {
        echo " Failed to send email ";
        // You can log this instead of echoing in production
        // error_log("Failed to send deactivation email for user {$username}");
    }

} catch (Exception $e) {
    echo "Error updating appointment";
}
?>






