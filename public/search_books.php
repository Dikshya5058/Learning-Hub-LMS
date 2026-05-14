<?php
session_start();
require '../config/db.php';

/* ================================
   SECURITY CHECK
================================ */
if (!isset($_SESSION['user_id'])) {
    exit(); // stop AJAX request if not logged in
}

$user_id = $_SESSION['user_id'];

/* ================================
   GET SEARCH QUERY
================================ */
$search_query = trim($_GET['search'] ?? "");

/* ================================
   FETCH BOOKS (SEARCH OR ALL)
================================ */
if ($search_query !== "") {
    $stmt = $pdo->prepare("
        SELECT * FROM books 
        WHERE title LIKE ?
        ORDER BY created_at DESC
    ");
    $stmt->execute(["%$search_query%"]);
} else {
    $stmt = $pdo->query("
        SELECT * FROM books 
        ORDER BY created_at DESC
    ");
}

$books = $stmt->fetchAll();

/* ================================
   USER WISHLIST
================================ */
$stmt = $pdo->prepare("
    SELECT book_id 
    FROM borrowed_books 
    WHERE user_id = ? AND returned_at IS NULL
");
$stmt->execute([$user_id]);

$my_wishlist = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
?>

<?php foreach($books as $book): 
    $book_id = $book['id'];
    $is_wishlisted = in_array($book_id, $my_wishlist);
?>

<div class="book-card">

    <span class="category-tag">
        <?php echo htmlspecialchars($book['category']); ?>
    </span>

    <h4><?php echo htmlspecialchars($book['title']); ?></h4>

    <p class="author">
        by <?php echo htmlspecialchars($book['author']); ?>
    </p>

    <button class="desc-toggle" onclick="toggleDescription(this)">
        Quick Details
    </button>

    <div class="description">
        <?php echo nl2br(htmlspecialchars($book['description'])); ?>
    </div>

    <div class="status-box">

        <?php if($is_wishlisted): ?>
            <span class="status-text status-wishlisted">
                ● Already in Wishlist
            </span>

            <button class="action-btn btn-disabled" disabled>
                Wishlisted
            </button>

        <?php else: ?>
            <span class="status-text status-available">
                ● Available to add
            </span>

            <form action="user_borrow.php" method="POST">
                <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                <button type="submit" class="action-btn btn-borrow">
                    Add to Wishlist
                </button>
            </form>
        <?php endif; ?>

    </div>
</div>

<?php endforeach; ?>

<?php if(count($books) === 0): ?>
<div style="padding:20px; text-align:center; color:#64748b;">
    <h3>No books found</h3>
    <p>Try searching with a different title.</p>
</div>
<?php endif; ?>