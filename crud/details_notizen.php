<?php
session_start();

require "../db_connect.inc.php";
require "../functions.php";

// Update CORS headers
header("Access-Control-Allow-Origin: http://localhost:4200"); // Set to your frontend origin
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

$response = [];

try {
    // Check if user is authenticated
    if (!isset($_SESSION['user'])) {
        throw new Exception('Unauthorized access');
    }

    // Get user ID from session
    $user_id = $_SESSION['user'];

    // Get note ID from query parameter
    $note_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // Validate note ID
    if ($note_id === 0) {
        throw new Exception('Invalid note ID');
    }

    // Fetch the note the user has permission to view
    $query = "
        SELECT n.note_id, n.titel, n.content, n.created_at, n.fk_user_id, 
               p.can_view, p.can_edit
        FROM notizen n
        LEFT JOIN permissions p ON n.note_id = p.fk_note_id
        WHERE n.note_id = ? AND p.fk_user_id = ? AND p.can_view = 1";

    // Prepare and execute the query
    $stmt = $connect->prepare($query);
    $stmt->bind_param('ii', $note_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if note exists and user has permission to view
    if ($result->num_rows > 0) {
        $note = $result->fetch_assoc();
        echo json_encode($note);
    } else {
        throw new Exception('Note not found or no permission to view');
    }
} catch (Exception $e) {
    // Return error response
    http_response_code(403); // Forbidden
    echo json_encode(['error' => $e->getMessage()]);
}
