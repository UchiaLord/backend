<?php
session_start();

// Allow requests from all domains (CORS headers)
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit; // Exit early to handle preflight requests
}

header('Content-Type: application/json');

$response = array();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        unset($_SESSION["user"]);
        session_unset();
        session_destroy();

        $response["success"] = true;
        $response["message"] = "Logout successful";
    } else {
        http_response_code(405); // Method Not Allowed
        $response["success"] = false;
        $response["message"] = "Invalid request method";
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    $response["success"] = false;
    $response["message"] = "An error occurred: " . $e->getMessage();
}

echo json_encode($response);
