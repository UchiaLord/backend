<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once "../db_connect.inc.php";
require_once "../functions.php";

$response = array();

try {
    $user_id = $_SESSION['user'];
    if (!$user_id) {
        throw new Exception('User ID not found in session');
    }

    error_log('User ID: ' . $user_id);

    $query = "SELECT n.note_id, n.titel, n.content, n.created_at, n.fk_user_id, p.can_view, p.can_edit
              FROM notizen n
              LEFT JOIN permissions p ON n.note_id = p.fk_note_id AND p.fk_user_id = ?
              WHERE n.fk_user_id = ? OR p.fk_user_id = ?";

    $result = dbq($query, [$user_id, $user_id, $user_id], 'iii');

    if (!$result["success"]) {
        throw new Exception($result["message"]);
    }

    $notes = [];
    foreach ($result["data"] as $row) {
        $notes[] = [
            'id' => $row['note_id'],
            'titel' => $row['titel'],
            'content' => $row['content'],
            'created_at' => $row['created_at'],
            'can_edit' => $row['can_edit'] || $row['fk_user_id'] == $user_id
        ];
    }

    error_log('Notes retrieved: ' . json_encode($notes));

    echo json_encode($notes);
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    error_log('Error: ' . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
