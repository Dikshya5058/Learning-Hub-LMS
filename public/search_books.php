<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    exit();
}

$user_id = $_SESSION['user_id'];
$search_query = trim($_GET['search'] ?? "");

/* SEARCH */
if ($search_query !== "") {
    $stmt = $pdo->prepare("
        SELECT * FROM books 
        WHERE title LIKE ?
        ORDER BY created_at DESC
    ");
    $stmt->execute(["%$search_query%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM books ORDER BY created_at DESC");
}

$books = $stmt->fetchAll();

/* WISHLIST */
$stmt = $pdo->prepare("
    SELECT book_id 
    FROM borrowed_books 
    WHERE user_id = ? AND returned_at IS NULL
");
$stmt->execute([$user_id]);

$my_wishlist = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($books as $book):
    $book_id = $book['id'];
    $is_on_my_wishlist = in_array($book_id, $my_wishlist);
?>

<div class="book-card">
    <div class="book-content">

        <span class="category-tag">
            <?= htmlspecialchars($book['category']) ?>
        </span>

        <h4><?= htmlspecialchars($book['title']) ?></h4>

        <p class="author">
            by <?= htmlspecialchars($book['author']) ?>
        </p>

        <button class="desc-toggle" onclick="toggleDescription(this)">
            Quick Details
        </button>

        <div class="description">
            <?= nl2br(htmlspecialchars($book['description'])) ?>
        </div>

    </div>

    <div class="status-box">

        <?php if($is_on_my_wishlist): ?>
            <span class="status-text status-wishlisted">● Already in Wishlist</span>
            <button class="action-btn btn-disabled" disabled>Wishlisted</button>
        <?php else: ?>
            <span class="status-text status-available">● Available to add</span>
            <form action="user_borrow.php" method="POST">
                <input type="hidden" name="book_id" value="<?= $book_id ?>">
                <button type="submit" class="action-btn btn-borrow">
                    Add to wishlist
                </button>
            </form>
        <?php endif; ?>

    </div>
</div>

<?php endforeach; ?>

<?php if (count($books) === 0): ?>
<div class="no-results">
    <h3>No books found</h3>
</div>
<?php endif; ?>