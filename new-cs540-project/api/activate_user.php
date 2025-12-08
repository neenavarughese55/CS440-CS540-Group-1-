<?php
require 'include/session_check.php';

if (!isset($_POST['username'])) {
    die("No username provided");
}

$isActive = $_POST['isActive'];
$username = $_POST['username'];

// Update Active Status of the user:
$sql = "UPDATE users SET is_active = :isActive WHERE username = :username";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':isActive', $isActive, PDO::PARAM_STR);
$stmt->bindParam(':username', $username, PDO::PARAM_STR);

// Cancel all appointments of the user:

// Send notification to this user and all other users:

if ($stmt->execute()) {
    echo "username: $username " . "isActive: " . $isActive;
} else {
    echo "Error updating appointment";
}
?>






