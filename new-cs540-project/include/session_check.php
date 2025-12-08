<?php
// Set app-wide timezone
date_default_timezone_set('America/Chicago');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Seiten, die keine Weiterleitung bekommen dürfen (Login/Register/Startseite)
$whitelist = [
    'login.php',
    'register.php',
    'logout.php',
    'index.php',       // deine Startseite-Datei
    ''                 // falls root "/" ohne Dateiname geladen wird
];

$currentFile = basename($_SERVER['SCRIPT_NAME']);      // z. B. "login.php"
$requestUri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // z. B. "/cs540project/"

// Wenn bereits eingeloggt → nichts tun
if (isset($_SESSION['user_id'])) {
    return;
}

// Wenn die aktuelle Seite in der Whitelist ist → nichts tun
if (in_array($currentFile, $whitelist, true)) {
    return;
}

// Wenn die aktuelle Anfrage die Projekt-Root ist (/cs540project/), erlauben (verhindert Schleife)
$projectRoot = '/cs540project'; // passe an, falls dein Projekt anders heisst
if ($requestUri === $projectRoot || $requestUri === $projectRoot . '/' ) {
    return;
}

// Sonst weiterleiten zur Login-Seite
header('Location: /cs540project/login.php');
exit();
