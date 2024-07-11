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
    // Retrieve JSON input
    $json_data = file_get_contents("php://input");
    $data = json_decode($json_data, true);

    // Check if username and password are provided in the JSON data
    if (isset($data['username']) && isset($data['password'])) {
        $username = cleanInput($data['username']);
        $password = cleanInput($data['password']);

        // Log the credentials to a log file (for debugging purposes)
        $logMessage = "Login attempt - Username: " . $username . ", Password: " . $password . "\n";
        file_put_contents("log.txt", $logMessage, FILE_APPEND);

        // Hash the password securely (use password_hash() and password_verify() in production)
        $hashed_password = hash("sha256", $password);

        // Query to check user credentials
        $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
        $params = array($username, $hashed_password);
        $types = "ss";

        // Execute the query using dbq function
        $result = dbq($sql, $params, $types);

        // Check if exactly one user was found with the provided username and password
        if ($result["success"] && count($result["data"]) == 1) {
            $row = $result["data"][0]; // Fetch the first row
            // Login successful
            $_SESSION["user"] = $row["id"];
            file_put_contents("log.txt", $_SESSION["user"], FILE_APPEND);

            $response["success"] = true;
            $response["message"] = "Login successful";
            $response["userId"] = $_SESSION["user"];
            echo json_encode($response);
        } else {
            // Invalid credentials
            $response["success"] = false;
            $response["message"] = "Invalid credentials";
            echo json_encode($response);
        }
    } else {
        // Username or password is missing
        $response["success"] = false;
        $response["message"] = "Username or password is missing";
        echo json_encode($response);
    }
} catch (Exception $e) {
    // Error while processing request
    http_response_code(500); // Internal Server Error
    $response["success"] = false;
    $response["message"] = $e->getMessage();
    echo json_encode($response);
}
