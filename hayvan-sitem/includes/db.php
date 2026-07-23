<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\includes\db.php

// Database connection settings
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'hayvan_sitem';

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>