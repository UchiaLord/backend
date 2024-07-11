<?php
session_start();

require "../db_connect.inc.php";
require "../functions.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS-Einstellungen für den Zugriff von verschiedenen Domains
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // Cache for 1 Day
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

$response = [];

try {
    // Check if user is authenticated
    if (!isset($_SESSION['user'])) {
        throw new Exception('Unauthorized access');
    }

    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get raw POST data and decode JSON
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate input parameters
    if (!$data || !isset($data['title']) || !isset($data['content']) || !isset($data['permissions'])) {
        throw new Exception('Invalid or empty input data');
    }

    $title = cleanInput($data['title']);
    $content = cleanInput($data['content']);
    $user_id = $_SESSION['user'];
    $permissions = $data['permissions'];

    // Validate title and content
    if (strlen($title) < 3 || strlen($title) > 255) {
        throw new Exception('Title must be between 3 and 255 characters');
    }

    if (strlen($content) < 10) {
        throw new Exception('Content must be at least 10 characters long');
    }

    // Insert note into database
    $query = "INSERT INTO notizen (titel, content, created_at, fk_user_id) VALUES (?, ?, NOW(), ?)";
    $params = [$title, $content, $user_id];
    $types = "ssi";

    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    $success = mysqli_stmt_execute($stmt);

    if (!$success) {
        throw new Exception('Failed to insert note');
    }

    $note_id = mysqli_insert_id($connect); // Get the last inserted ID

    mysqli_stmt_close($stmt);

    // Assign permissions to specified users
    if ($permissions['canView'] && $permissions['username']) {
        $username = $permissions['username'];
        $canEdit = isset($permissions['canEdit']) && $permissions['canEdit'] ? 1 : 0;

        // Query to find user ID by username
        $find_user_query = "SELECT id FROM users WHERE username = ?";
        $stmt = mysqli_prepare($connect, $find_user_query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $user_result = mysqli_stmt_get_result($stmt);

        if (!$user_result) {
            throw new Exception('Failed to find user with username ' . $username);
        }

        $user_row = mysqli_fetch_assoc($user_result);

        if (!$user_row) {
            throw new Exception('User with username ' . $username . ' not found');
        }

        $permission_user_id = $user_row['id'];

        // Insert or update permissions for the user
        $permission_query = "REPLACE INTO permissions (fk_user_id, fk_note_id, can_view, can_edit) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($connect, $permission_query);
        mysqli_stmt_bind_param($stmt, "iiii", $permission_user_id, $note_id, $permissions['canView'], $canEdit);
        $success = mysqli_stmt_execute($stmt);

        if (!$success) {
            throw new Exception('Failed to insert or update permissions');
        }

        mysqli_stmt_close($stmt);
    }

    


    // Respond with success
    http_response_code(201); // Created
    $response["success"] = true;
    $response["message"] = "Note created successfully";
    $response["inserted_id"] = $note_id;
    echo json_encode($response);
} catch (Exception $e) {
    // Handle exceptions
    http_response_code(400); // Bad Request
    echo json_encode(["error" => $e->getMessage()]);
}
