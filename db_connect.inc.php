<?php
$hostname = "127.0.0.1";
$username = "root";
$password = "";
$database = "notizbuch";

$connect = mysqli_connect($hostname, $username, $password, $database);


function dbq($query, $params = [], $types = "")
{
    global $connect;

    // Connect to the database
    $hostname = "127.0.0.1";
    $username = "root";
    $password = "";
    $database = "notizbuch";

    $connect = mysqli_connect($hostname, $username, $password, $database);

    if (!$connect) {
        return ["success" => false, "message" => "Connection failed: " . mysqli_connect_error()];
    }

    // Prepare statement with parameterized query
    $stmt = mysqli_prepare($connect, $query);

    if ($stmt === false) {
        return ["success" => false, "message" => "Query preparation failed: " . mysqli_error($connect)];
    }

    // Bind parameters if any are provided
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    // Execute query
    $success = mysqli_stmt_execute($stmt);

    if ($success) {
        if (strpos($query, 'SELECT') === 0) {
            $result = mysqli_stmt_get_result($stmt);
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            mysqli_stmt_close($stmt);
            return ["success" => true, "data" => $data];
        } else {
            $affected_rows = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            return ["success" => true, "affected_rows" => $affected_rows];
        }
    } else {
        mysqli_stmt_close($stmt);
        return ["success" => false, "message" => "Query execution failed: " . mysqli_stmt_error($stmt)];
    }
}

/*

function createOrUpdateUserNotePrmission($permission_user_id, $note_id, $canEdit) {

    // Insert or update permissions for the user
    $permission_query = "REPLACE INTO permissions (fk_user_id, fk_note_id, can_view, can_edit) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($connect, $permission_query);
    mysqli_stmt_bind_param($stmt, "iiii", $permission_user_id, $note_id, $permissions['canView'], $canEdit);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$success) {
        throw new Exception('Failed to insert or update permissions');
    }
}


*/

/*
<?php

$hostname = "127.0.0.1";
$username = "root";
$password = "";
$database = "notizbuch";

// Verbindung zur Datenbank herstellen
$connect = mysqli_connect($hostname, $username, $password, $database);

function dbq($query, $params = [], $types = "") {
    global $connect;

    // Überprüfen, ob die Verbindung erfolgreich war
    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Prepare statement mit parameterized query
    $stmt = mysqli_prepare($connect, $query);

    // Überprüfen, ob das statement korrekt vorbereitet wurde
    if ($stmt === false) {
        die("Query preparation failed: " . mysqli_error($connect));
    }

    // Bind parameters, falls Parameter vorhanden sind
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    // Query ausführen
    $success = mysqli_stmt_execute($stmt);

    // Ergebnis des Execute-Vorgangs überprüfen
    if ($success) {
        $result = mysqli_stmt_get_result($stmt);
        // Get mysqli_result object
        mysqli_stmt_close($stmt);
        mysqli_close($connect);
        return $result; // Rückgabe des mysqli_result Objekts
    } else {
        // Fehler beim Ausführen des Statements
        die("Query execution failed: " . mysqli_stmt_error($stmt));
    }
}
*/
