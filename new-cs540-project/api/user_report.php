<?php
session_start();
header('Content-Type: application/json');

// DB
$dsn = "mysql:host=localhost;dbname=cs540;charset=utf8mb4";
$user = "root";
$pass = "";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Exception $e) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

// Incoming filters
$username = $_POST['username'] ?? "ALL";
$from = $_POST['from'] ?? "";
$to = $_POST['to'] ?? "";

$sql = "
    SELECT 
        id,
        username,
        email,
        role,
        is_active,
        created_at
    FROM users
";

$conditions = [];
$params = [];

// Username filter (ignore if ALL)
if (!empty($username) && strtoupper($username) !== "ALL") {
    $conditions[] = "id = :username";
    $params[":username"] = $username;
}

// Date range filter
if (!empty($from)) {
    $conditions[] = "created_at >= :fromDate";
    $params[":fromDate"] = $from . " 00:00:00";
}

if (!empty($to)) {
    $conditions[] = "created_at <= :toDate";
    $params[":toDate"] = $to . " 23:59:59";
}

// Combine WHERE
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY id";

// DEBUG OUTPUT
$debugInfo = [
    "sql" => $sql,
    "params" => $params,
    "username" => $username,
    "from" => $from,
    "to" => $to
];

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    echo json_encode([
        "debug" => $debugInfo,  // <--- view in console
        "rows" => $rows
    ]);
} catch (Exception $e) {
    echo json_encode([
        "error" => "Query failed",
        "debug" => $debugInfo,
        "exception" => $e->getMessage()
    ]);
}
