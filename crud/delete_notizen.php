<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "../db_connect.inc.php";
require "../functions.php";

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Access-Control-Allow-Credentials: true");

try {
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        exit(0);
    }

    if (!isset($_SESSION['user'])) {
        throw new Exception('Unauthorized access');
    }

    if (!isset($_GET['id'])) {
        throw new Exception('Note ID not provided');
    }

    $noteId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($noteId === false || $noteId <= 0) {
        throw new Exception('Invalid note ID');
    }

    $userId = $_SESSION['user'];

    // Check if the user has permission to delete the note
    $queryPermission = "SELECT * FROM permissions WHERE fk_note_id = ? AND fk_user_id = ? AND can_edit = 1";
    $paramsPermission = [$noteId, $userId];
    $typesPermission = "ii";

    $resultPermission = dbq($queryPermission, $paramsPermission, $typesPermission);

    if ($resultPermission["success"] && count($resultPermission["data"]) > 0) {
        // User has permission to delete the note

        // Start a transaction
        mysqli_begin_transaction($connect);

        try {
            // Delete related permissions
            $queryDeletePermissions = "DELETE FROM permissions WHERE fk_note_id = ?";
            $paramsDeletePermissions = [$noteId];
            $typesDeletePermissions = "i";

            $resultDeletePermissions = dbq($queryDeletePermissions, $paramsDeletePermissions, $typesDeletePermissions);

            if ($resultDeletePermissions["success"]) {
                // Delete the note
                $queryDeleteNote = "DELETE FROM notizen WHERE note_id = ?";
                $paramsDeleteNote = [$noteId];
                $typesDeleteNote = "i";

                $resultDeleteNote = dbq($queryDeleteNote, $paramsDeleteNote, $typesDeleteNote);

                if ($resultDeleteNote["success"]) {
                    // Commit the transaction
                    mysqli_commit($connect);
                    echo json_encode(['message' => 'Note and related permissions deleted successfully', 'deleted_id' => $noteId]);
                } else {
                    throw new Exception('Failed to delete note: ' . $resultDeleteNote["message"]);
                }
            } else {
                throw new Exception('Failed to delete related permissions: ' . $resultDeletePermissions["message"]);
            }
        } catch (Exception $e) {
            // Rollback the transaction
            mysqli_rollback($connect);
            throw $e;
        }
    } else {
        throw new Exception('No permission to delete this note');
    }
} catch (Exception $e) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => $e->getMessage()]);
    error_log($e->getMessage()); // Log the error message
}

// Close the database connection
mysqli_close($connect);
