<?php
session_start();
require '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';
    $created_at = date('Y-m-d H:i:s');

    if (!empty($name) && !empty($email) && !empty($message)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message, created_at) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $message, $created_at]);
            echo "success";
        } catch (PDOException $e) {
            http_response_code(500);
            echo "Database error";
        }
    }
    exit();
}
?>