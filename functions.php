<?php
// Function verhindert XXS attacks durch Inputs
function cleanInput($var)
{
    $data = trim($var);                 // Entfernt Leerzeichen am Anfang und Ende
    $data = strip_tags($data);          // Entfernt HTML and PHP tags
    $data = htmlspecialchars($data);    // Convertiert special characters to HTML entities

    return $data;
}
