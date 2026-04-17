<?php
session_start();
require '../config/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../public/user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$book_id = $_POST['book_id'] ?? null;

if(!$book_id){
    $_SESSION['message'] = "⚠️ No book selected to return.";
    header("Location: view_borrowed_books.php");
    exit();
}

// Check ownership
$stmt = $pdo->prepare("SELECT * FROM borrowed_books WHERE user_id=? AND book_id=? AND returned_at IS NULL");
$stmt->execute([$user_id, $book_id]);
$result = $stmt->fetch();

if(!$result){
    $_SESSION['message'] = "⚠️ You cannot return a book you haven't borrowed!";
    header("Location: view_borrowed_books.php");
    exit();
}

// Return book
$returned_at = date('Y-m-d H:i:s');

$stmt = $pdo->prepare("UPDATE borrowed_books SET returned_at=? WHERE user_id=? AND book_id=? AND returned_at IS NULL");
$stmt->execute([$returned_at, $user_id, $book_id]);

$_SESSION['message'] = "✅ Book returned successfully!";
header("Location: view_borrowed_books.php");
exit();
?>