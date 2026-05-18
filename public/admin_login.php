<?php
header('Content-Type: application/json');
session_start();

// Ensure the path to db.php is correct
require_once '../config/db.php';

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

$email    = trim($data['email'] ?? '');
$password = trim($data['password'] ?? ''); // Added trim to password as well

// 1. Basic validation
if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Please enter both email and password."]);
    exit;
}

// 2. SERVER-SIDE PASSWORD STRENGTH CHECK
// Criteria: Min 8 chars, at least one letter and one number
if (strlen($password) < 8 || !preg_match("/[A-Za-z]/", $password) || !preg_match("/[0-9]/", $password)) {
    echo json_encode(["status" => "error", "message" => "Weak Password: Must be 8+ characters with letters and numbers."]);
    exit;
}

try {
    // 3. Fetch user by email
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4. Detailed Authentication Logic for Debugging
    if (!$user) {
        // This tells you if the email 'admin@lms.com' actually exists in the DB
        echo json_encode(["status" => "error", "message" => "Account not found."]);
    } else {
        // Check the hashed password
        if (password_verify($password, $user['password'])) {
            // SUCCESS
            $_SESSION['admin'] = $user['email'];
            echo json_encode(["status" => "success"]);
        } else {
            // This tells you the email is correct, but the hash mismatch is happening
            echo json_encode(["status" => "error", "message" => "Incorrect password. Check your database hash."]);
        }
    }

} catch (PDOException $e) {
    // Standard database error
    echo json_encode(["status" => "error", "message" => "Database connection error."]);
}
?>