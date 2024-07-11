<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "../db_connect.inc.php";
require "../functions.php";

// CORS headers
header("Access-Control-Allow-Origin: http://localhost:4200"); // Adjust as per your Angular app URL
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

// Handle OPTIONS request method
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$response = [];

try {
    // Check if user is authenticated
    if (!isset($_SESSION['user'])) {
        throw new Exception('Unauthorized access');
    }

    // Read raw POST data
    $rawPostData = file_get_contents('php://input');
    file_put_contents("logcrud.txt", "Received raw POST data: " . $rawPostData . "\n", FILE_APPEND);

    // Decode JSON input
    $input = json_decode($rawPostData, true);
    file_put_contents("logcrud.txt", "Decoded input: " . json_encode($input) . "\n", FILE_APPEND);

    // Initialize an array for missing fields
    $missingFields = [];

    // Check for required fields
    if (!isset($input['id'])) {
        $missingFields[] = 'id';
    }
    if (!isset($input['title'])) {
        $missingFields[] = 'title';
    }
    if (!isset($input['content'])) {
        $missingFields[] = 'content';
    }

    // If there are missing fields, log them and throw an exception
    if (!empty($missingFields)) {
        $logMessage = "Missing required fields: " . implode(', ', $missingFields) . "\n";
        file_put_contents("logcrud.txt", $logMessage, FILE_APPEND);
        throw new Exception('Missing required fields: ' . implode(', ', $missingFields));
    }

    // Extract input fields
    $note_id = intval($input['id']);
    file_put_contents("logcrud.txt", "Received note ID: " . $note_id . "\n", FILE_APPEND);
    $title = $input['title'];
    $content = $input['content'];

    file_put_contents("logcrud.txt", "Note ID: " . $note_id . ", Title: " . $title . ", Content: " . $content . "\n", FILE_APPEND);

    // Check if the user has permission to edit the note
    $queryPermission = "SELECT * FROM permissions WHERE fk_note_id = ? AND fk_user_id = ? AND can_edit = 1";
    $resultPermission = dbq($queryPermission, [$note_id, $_SESSION['user']], 'ii');

    if ($resultPermission["success"] && count($resultPermission["data"]) > 0) {
        // Update the note
        $queryUpdateNote = "UPDATE notizen SET titel = ?, content = ? WHERE note_id = ?";
        $resultUpdateNote = dbq($queryUpdateNote, [$title, $content, $note_id], 'ssi');

        if ($resultUpdateNote["success"]) {
            $response['success'] = true;
            $response['message'] = 'Note updated successfully';

            // Handle permissions update
            if (isset($input['permissions'])) {
                $canView = isset($input['permissions']['canView']) ? 1 : 0;
                $canEdit = isset($input['permissions']['canEdit']) ? 1 : 0;
                $username = isset($input['permissions']['username']) ? trim($input['permissions']['username']) : '';

                if (!empty($username)) {
                    // Retrieve user ID based on username (assuming a users table)
                    $queryUserId = "SELECT user_id FROM users WHERE username = ?";
                    $resultUserId = dbq($queryUserId, [$username], 's');

                    if ($resultUserId["success"] && count($resultUserId["data"]) > 0) {
                        $targetUserId = $resultUserId["data"][0]['user_id'];

                        // Check if permission already exists
                        $queryCheckPermission = "SELECT * FROM permissions WHERE fk_note_id = ? AND fk_user_id = ?";
                        $resultCheckPermission = dbq($queryCheckPermission, [$note_id, $targetUserId], 'ii');

                        if ($resultCheckPermission["success"] && count($resultCheckPermission["data"]) > 0) {
                            // Update existing permission
                            $queryUpdatePermission = "UPDATE permissions SET can_view = ?, can_edit = ? WHERE fk_note_id = ? AND fk_user_id = ?";
                            $resultUpdatePermission = dbq($queryUpdatePermission, [$canView, $canEdit, $note_id, $targetUserId], 'iiii');
                        } else {
                            // Insert new permission
                            $queryInsertPermission = "INSERT INTO permissions (fk_note_id, fk_user_id, can_view, can_edit) VALUES (?, ?, ?, ?)";
                            $resultInsertPermission = dbq($queryInsertPermission, [$note_id, $targetUserId, $canView, $canEdit], 'iiii');
                        }
                    } else {
                        throw new Exception('User with the provided username does not exist');
                    }
                }
            }
        } else {
            throw new Exception('No permission to view or edit this note');
        }
    } else {
        throw new Exception('No permission to edit this note');
    }
} catch (Exception $e) {
    http_response_code(403); // Forbidden
    $response['success'] = false;
    $response['message'] = 'An error occurred: ' . $e->getMessage();
    file_put_contents("logcrud.txt", "Error: " . $e->getMessage() . "\n", FILE_APPEND);
}

echo json_encode($response);
