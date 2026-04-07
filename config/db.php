<?php
$host = "localhost";
$user = "root";       // your DB username
$password = "";       // your DB password
$dbname = "Learning_Management_System";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>