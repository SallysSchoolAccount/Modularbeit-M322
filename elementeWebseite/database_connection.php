<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verbindung mit der Database
$conn =  new mysqli("localhost", "root", "", "books");

// Verbindung checken
if ($conn->connect_error) {
    die("Verbindung Fehlgeschlagen: " . $conn->connect_error);
}