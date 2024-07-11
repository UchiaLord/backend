<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// CORS headers
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

// Include necessary files (e.g., database connection and functions)
require_once "../db_connect.inc.php"; // Ensure this path is correct
require_once "../functions.php"; // Ensure this path is correct

$response = array();

try {
    // Retrieve user ID from session
    $user_id = $_SESSION['user'];

    // Check if search term is provided
    if (!isset($_GET['searchTerm'])) {
        throw new Exception('Search term is missing');
    }

    $searchTerm = cleanInput($_GET['searchTerm']); // Clean input to prevent SQL injection

    // Query to search notes for the authenticated user
    $query = "SELECT n.note_id, n.titel, n.content, n.created_at, n.fk_user_id, p.can_view, p.can_edit
              FROM notizen n
              LEFT JOIN permissions p ON n.note_id = p.fk_note_id AND p.fk_user_id = ?
              WHERE (n.fk_user_id = ? OR p.fk_user_id = ?) AND (n.titel LIKE ? OR n.content LIKE ?)";

    // Add '%' to search term for wildcard search
    $searchParam = '%' . $searchTerm . '%';

    // Execute database query using dbq function
    $result = dbq($query, [$user_id, $user_id, $user_id, $searchParam, $searchParam], 'iiiss');

    // Check if query was successful
    if (!$result["success"]) {
        throw new Exception($result["message"]); // Throw exception with error message
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
    echo json_encode($notes); // Output notes as JSON
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => $e->getMessage()]); // Output error message as JSON
}
