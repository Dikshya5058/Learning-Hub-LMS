<?php
session_start();
require '../config/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$book_id = $_POST['book_id'] ?? null;

if(!$book_id){
    $_SESSION['message'] = "Please select a valid book.";
    header("Location: user_view_books.php");
    exit();
}

try {
    /* Check if already in wishlist (active if returned_at is NULL) */
    $stmt = $pdo->prepare("SELECT * FROM borrowed_books WHERE book_id = ? AND user_id = ? AND returned_at IS NULL");
    $stmt->execute([$book_id, $user_id]);
    $result = $stmt->fetch();

    if($result){
        $_SESSION['message'] = "This book is already in your wishlist.";
        header("Location: user_view_books.php");
        exit();
    }

    /* Add to wishlist logic */
    $added_at = date('Y-m-d H:i:s');
    
    // FIX: Your database requires a 'due_date'. 
    // We set it to 100 years from now so it never "expires".
    $dummy_due_date = date('Y-m-d H:i:s', strtotime('+100 years'));

    $stmt = $pdo->prepare("
        INSERT INTO borrowed_books (user_id, book_id, borrowed_at, due_date, returned_at)
        VALUES (?, ?, ?, ?, NULL)
    ");

    $stmt->execute([$user_id, $book_id, $added_at, $dummy_due_date]);

    $_SESSION['message'] = "Book added to wishlist successfully!";
    
    // Redirect back to the books view
    header("Location: user_view_books.php"); 
    exit();

} catch (PDOException $e) {
    // This will catch any remaining integrity constraint violations
    die("Database Error: " . $e->getMessage());
}
?>