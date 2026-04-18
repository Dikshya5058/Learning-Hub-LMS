<?php
session_start();
require '../config/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$book_id = $_POST['book_id'] ?? null;

if(!$book_id){
    $_SESSION['message'] = "⚠️ No book selected to remove.";
    header("Location: view_borrowed_books.php");
    exit();
}

// Verify this book is actually in THIS user's active wishlist
$stmt = $pdo->prepare("SELECT * FROM borrowed_books WHERE user_id=? AND book_id=? AND returned_at IS NULL");
$stmt->execute([$user_id, $book_id]);
$result = $stmt->fetch();

if(!$result){
    $_SESSION['message'] = "⚠️ This book was not found in your wishlist.";
    header("Location: view_borrowed_books.php");
    exit();
}

// Logic: Setting returned_at removes it from active wishlist queries
$removed_at = date('Y-m-d H:i:s');

$stmt = $pdo->prepare("UPDATE borrowed_books SET returned_at=? WHERE user_id=? AND book_id=? AND returned_at IS NULL");

if($stmt->execute([$removed_at, $user_id, $book_id])) {
    $_SESSION['message'] = "✅ Book removed from wishlist.";
} else {
    $_SESSION['message'] = "❌ Failed to remove book.";
}

header("Location: view_borrowed_books.php");
exit();
?>