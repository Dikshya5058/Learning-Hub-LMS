<?php
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


$stmt = $pdo->prepare("
    SELECT * FROM borrowed_books 
    WHERE book_id = ? 
    AND user_id = ? 
    AND returned_at IS NULL
");
$stmt->execute([$book_id, $user_id]);
$result = $stmt->fetch();

if($result){
    $_SESSION['message'] = "This book is already in your wishlist.";
    header("Location: user_view_books.php");
    exit();
}


$added_at = date('Y-m-d H:i:s');

$stmt = $pdo->prepare("
    INSERT INTO borrowed_books (user_id, book_id, borrowed_at, due_date) 
    VALUES (?, ?, ?, NULL)
");

$stmt->execute([$user_id, $book_id, $added_at]);

$_SESSION['message'] = "Book successfully added to wishlist!";
header("Location: view_borrowed_books.php");
exit();
?>