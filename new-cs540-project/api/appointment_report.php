<?php
// /cs540project/api/appointment_report.php
session_start();
header('Content-Type: application/json');

// --- DB connection ---
$dsn  = "mysql:host=localhost;dbname=cs540;charset=utf8mb4";
$user = "root";
$pass = "";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Exception $e) {
    echo json_encode([
        "error"     => "DB connection failed",
        "exception" => $e->getMessage()
    ]);
    exit;
}

// --- Incoming filters from JS or form ---
$from = $_POST['from'] ?? '';  // "YYYY-MM-DD"
$to   = $_POST['to']   ?? '';  // "YYYY-MM-DD"

// From your dropdowns:
$categoryDropdown = $_POST['category'] ?? 'ALL';
$usernameDropdown = $_POST['username'] ?? 'ALL';

// --- Build base query ---
// ASSUMPTIONS – adjust names if your schema is different:
//
//  appointments table : appointments (alias a)
//  date/time column   : start_time
//  status column      : status      (value 'canceled' for canceled)
//  user id column     : user_id
//  category relation  : a.category_id -> categories.id
//  category name col  : categories.name
//  user relation      : a.user_id -> users.id
//  username column    : users.username
//
$sql = "
    SELECT
        DATE_FORMAT(asl.start_time, '%b %Y') AS month,
        c.name AS category,
        COUNT(*) AS total_appointments,
        SUM(CASE WHEN a.status = 'Cancelled' THEN 1 ELSE 0 END) AS canceled,
        COUNT(DISTINCT a.user_id) AS unique_users
    FROM `appointment_slots` asl 
                            LEFT JOIN `appointments` a ON asl.id = a.slot_id
    INNER JOIN categories c ON asl.category_id = c.id
    LEFT JOIN users u ON a.user_id = u.id
";

$conditions = [];
$params     = [];

// Date range filters
if (!empty($from)) {
    $conditions[]        = "a.start_time >= :fromDate";
    $params[":fromDate"] = $from . " 00:00:00";
}

if (!empty($to)) {
    $conditions[]       = "a.start_time <= :toDate";
    $params[":toDate"]  = $to . " 23:59:59";
}

// Category filter (dropdown). Expecting "ALL" or category name like "Beauty"
if (!empty($categoryDropdown) && strtoupper($categoryDropdown) !== 'ALL') {
    $conditions[]              = "c.name = :categoryName";
    $params[":categoryName"]   = $categoryDropdown;
}

// Username filter (dropdown). Expecting "ALL" or actual username
if (!empty($usernameDropdown) && strtoupper($usernameDropdown) !== 'ALL') {
    $conditions[]              = "u.username = :username";
    $params[":username"]       = $usernameDropdown;
}

// Combine WHERE conditions
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= "
    GROUP BY
        YEAR(asl.start_time),
        MONTH(asl.start_time),
        c.name
    ORDER BY
        YEAR(asl.start_time),
        MONTH(asl.start_time),
        c.name
";

// Debug info (super helpful when "No data found")
$debugInfo = [
    "sql"                  => $sql,
    "params"               => $params,
    "from"                 => $from,
    "to"                   => $to,
    "categoryDropdown"     => $categoryDropdown,
    "usernameDropdown"     => $usernameDropdown,
];

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    echo json_encode([
        "debug" => $debugInfo,
        "rows"  => $rows
    ]);
} catch (Exception $e) {
    echo json_encode([
        "error"     => "Query failed",
        "debug"     => $debugInfo,
        "exception" => $e->getMessage()
    ]);
}
