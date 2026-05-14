<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    exit("error");
}

$user_id = $_SESSION['user_id'];
$book_id = $_POST['book_id'] ?? null;

if (!$book_id) {
    exit("error");
}

$stmt = $pdo->prepare("INSERT INTO borrowed_books (user_id, book_id, borrowed_at, returned_at)
VALUES (?, ?, NOW(), NULL)");

if ($stmt->execute([$user_id, $book_id])) {
    echo "success";
} else {
    echo "error";
}