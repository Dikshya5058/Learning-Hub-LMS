<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db.php';

$data = json_decode(file_get_contents("php://input"));

if ($data && isset($data->email) && isset($data->password)) {
    $email = $data->email;
    $pass = $data->password;

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? AND password = ?");
    $stmt->execute([$email, $pass]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['admin'] = $user['email'];
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Request"]);
}
?>