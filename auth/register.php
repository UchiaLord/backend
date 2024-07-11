<?php
// PHP-Datei register.php

// Fehlerausgabe und CORS-Einstellungen
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start der Session
session_start();

// CORS-Einstellungen für den Zugriff von verschiedenen Domains
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // Cache für 1 Tag
}

// OPTIONS-Anfragen behandeln (für CORS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

// Content-Type auf JSON setzen
header("Content-Type: application/json");

// Einbindung der erforderlichen Dateien (z. B. für die Datenbankverbindung und Funktionen)
require_once "../db_connect.inc.php"; // Stellen Sie sicher, dass dies auf Ihre Datenbankverbindung verweist
require_once "../functions.php"; // Enthält Funktionen wie cleanInput() und dbq()

// Nehmen Sie Daten von Angular entgegen
$data = json_decode(file_get_contents("php://input"), true);

// Logging der empfangenen Daten
$logMessage = "Received data from Angular: " . json_encode($data) . "\n";
file_put_contents("log.txt", $logMessage, FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verarbeiten Sie die Daten hier
    $response = [];
    try {
        // Überprüfung der erforderlichen Felder
        $requiredFields = ['username', 'password', 'firstname', 'lastname'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Missing required parameter: $field");
            }
        }

        // Bereinigung und Validierung der Eingaben
        $username = cleanInput($data['username']);
        $password = cleanInput($data['password']);
        $first_name = cleanInput($data['firstname']);
        $last_name = cleanInput($data['lastname']);

        // Eingaben überprüfen (Beispielvalidierungen)
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            throw new Exception("Please enter a valid username (3-20 characters, letters, numbers, and underscores only)");
        }

        if (strlen($password) < 6) {
            throw new Exception("Password must be at least 6 characters long");
        }

        if (strlen($first_name) <= 2 || !preg_match("/^[a-zA-Z\s]+$/", $first_name)) {
            throw new Exception("Invalid first name");
        }

        if (strlen($last_name) <= 2 || !preg_match("/^[a-zA-Z\s]+$/", $last_name)) {
            throw new Exception("Invalid last name");
        }

        // Überprüfen, ob der Benutzername bereits existiert
        $sql = "SELECT * FROM users WHERE username = ?";
        $result = dbq($sql, [$username], 's');

        // Wenn der Benutzer bereits existiert, wirft dies eine Exception
        if ($result["success"] && !empty($result["data"])) {
            throw new Exception("Username already exists");
        }

        // Passwort hashen (angepasste Methode verwenden)
        $hashed_password = hash("sha256", $password);

        // Benutzer in die Datenbank einfügen
        $insertQuery = "INSERT INTO `users`(`username`, `password`, `first_name`, `last_name`) VALUES (?, ?, ?, ?)";
        $params = [$username, $hashed_password, $first_name, $last_name];
        $types = "ssss";

        $insertResult = dbq($insertQuery, $params, $types);

        // Erfolgsmeldung zurückgeben
        $response["success"] = true;
        $response["message"] = "Registration successful";

        // Logging der Antwort
        $logMessage = "Sending response to Angular: " . json_encode($response) . "\n";
        file_put_contents("log.txt", $logMessage, FILE_APPEND);
    } catch (Exception $e) {
        // Fehlermeldung zurückgeben
        http_response_code(400);
        $response["success"] = false;
        $response["error"] = $e->getMessage();
        error_log("Registration failed: " . $e->getMessage()); // Fehlermeldung in das Fehlerprotokoll schreiben

        // Logging der Fehlerantwort
        $logMessage = "Sending error response to Angular: " . json_encode($response) . "\n";
        file_put_contents("log.txt", $logMessage, FILE_APPEND);
    }

    // JSON-Antwort ausgeben
    echo json_encode($response);
} else {
    // Antwort für nicht unterstützte Methoden
    http_response_code(405); // Method Not Allowed
    echo json_encode(array("message" => "Method not allowed"));
}
