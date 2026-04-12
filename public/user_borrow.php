<<?php
session_start();
require '../config/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: ../public/user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$book_id = $_POST['book_id'] ?? null;

if(!$book_id){
    $_SESSION['message'] = "Please select a valid book.";
    header("Location: user_view_books.php");
    exit();
}

// Check if already borrowed
$stmt = $pdo->prepare("SELECT * FROM borrowed_books WHERE book_id=? AND returned_at IS NULL");
$stmt->execute([$book_id]);
$result = $stmt->fetch();

if($result){
    $_SESSION['message'] = "This book is already borrowed by another user.";
    header("Location: user_view_books.php");
    exit();
}

$borrowed_at = date('Y-m-d H:i:s');
$due_date = date('Y-m-d H:i:s', strtotime('+14 days'));

$stmt = $pdo->prepare("INSERT INTO borrowed_books (user_id, book_id, borrowed_at, due_date) VALUES (?, ?, ?, ?)");
$stmt->execute([$user_id, $book_id, $borrowed_at, $due_date]);

$_SESSION['message'] = "Book borrowed successfully! Return by " . date('d M Y', strtotime($due_date));
header("Location: view_borrowed_books.php");
exit();
?>