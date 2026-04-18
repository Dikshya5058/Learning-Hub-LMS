<?php
session_start();
require '../config/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// SQL: Fetch only the current user's active wishlist items
$stmt = $pdo->prepare("
    SELECT b.id, b.title, b.author, b.category, 
           bb.borrowed_at AS added_date
    FROM borrowed_books bb
    JOIN books b ON bb.book_id = b.id
    WHERE bb.user_id = ? AND bb.returned_at IS NULL
    ORDER BY bb.borrowed_at DESC
");
$stmt->execute([$user_id]);
$wishlist_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Wishlist | Learning Hub</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --brand-teal: #3cb1c5; 
    --bg-light: #f8fafc;
    --text-main: #0f172a;
    --text-muted: #64748b;
    --white: #ffffff;
    --danger: #ef4444;
}

* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg-light); color: var(--text-main); line-height: 1.6; }

header { background: var(--white); padding: 20px 60px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 100; }
header h1 { font-size: 20px; font-weight: 800; color: var(--brand-teal); }

.dash-btn { text-decoration: none; color: var(--text-main); font-weight: 700; font-size: 13px; padding: 10px 20px; border-radius: 12px; background: #f1f5f9; transition: 0.3s; }
.dash-btn:hover { background: var(--brand-teal); color: white; }

.container { max-width: 1100px; margin: 50px auto; padding: 0 40px; }

.section-header { margin-bottom: 40px; }
.section-header h2 { font-size: 32px; font-weight: 800; letter-spacing: -1px; }
.section-header p { color: var(--text-muted); font-size: 16px; }

.confirm-msg { background: #f0fdf4; color: #16a34a; padding: 15px 20px; border-radius: 12px; margin-bottom: 30px; font-weight: 700; border-left: 5px solid #16a34a; font-size: 14px; }

.wishlist-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; }

.wish-card { 
    background: var(--white); 
    padding: 30px; 
    border-radius: 20px; 
    border: 1px solid #e2e8f0; 
    transition: all 0.3s ease; 
    display: flex; 
    flex-direction: column; 
}

.wish-card:hover { transform: translateY(-5px); border-color: var(--brand-teal); box-shadow: 0 15px 30px rgba(0,0,0,0.04); }

.category-tag { font-size: 10px; text-transform: uppercase; font-weight: 800; color: var(--brand-teal); background: rgba(60, 177, 197, 0.1); padding: 4px 10px; border-radius: 6px; display: inline-block; margin-bottom: 15px; width: fit-content; }

.wish-card h4 { font-size: 19px; font-weight: 800; margin-bottom: 5px; color: var(--text-main); line-height: 1.3; }
.wish-card .author { font-size: 14px; color: var(--text-muted); margin-bottom: 25px; }

.wish-footer { margin-top: auto; padding-top: 20px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }

.date-added { font-size: 11px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; }
.date-added span { display: block; color: var(--text-main); font-size: 13px; text-transform: none; }

.btn-remove { 
    background: transparent; 
    color: var(--danger); 
    border: 1.5px solid #fee2e2;
    padding: 8px 16px; 
    border-radius: 10px; 
    font-weight: 700; 
    font-size: 12px; 
    cursor: pointer; 
    transition: 0.2s; 
}
.btn-remove:hover { background: var(--danger); color: white; border-color: var(--danger); }

.empty-state { text-align: center; padding: 80px 40px; background: white; border-radius: 24px; border: 2px dashed #e2e8f0; grid-column: 1 / -1; }
.empty-state h3 { font-size: 20px; color: var(--text-main); margin-bottom: 10px; }
.empty-state p { color: var(--text-muted); margin-bottom: 20px; }

.back-link { margin-top: 40px; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: var(--text-muted); text-decoration: none; font-size: 14px; }
.back-link:hover { color: var(--brand-teal); }
</style>
</head>
<body>

<header>
    <h1>Learning Hub</h1>
    <a href="user_dashboard.php" class="dash-btn">Dashboard</a>
</header>

<div class="container">
    <?php if(isset($_SESSION['message'])): ?>
        <div class="confirm-msg"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <div class="section-header">
        <h2>My Wishlist</h2>
        <p>Manage the books you've saved to read later.</p>
    </div>

    <div class="wishlist-grid">
        <?php if(count($wishlist_books) > 0): ?>
            <?php foreach($wishlist_books as $book): ?>
            <div class="wish-card">
                <span class="category-tag"><?php echo htmlspecialchars($book['category']); ?></span>
                <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                <p class="author">by <?php echo htmlspecialchars($book['author']); ?></p>

                <div class="wish-footer">
                    <div class="date-added">
                        Added on
                        <span><?php echo date('d M Y', strtotime($book['added_date'])); ?></span>
                    </div>

                    <form action="user_return.php" method="POST" 
                          onsubmit="return confirm('Remove this book from your wishlist?');">
                        <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                        <button type="submit" class="btn-remove">Remove</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>Your wishlist is empty</h3>
                <p>Browse the catalog and add books you're interested in.</p>
                <a href="user_view_books.php" class="dash-btn" style="background: var(--brand-teal); color: white;">Explore Catalog</a>
            </div>
        <?php endif; ?>
    </div>

    <a href="user_dashboard.php" class="back-link">← Back to Dashboard</a>
</div>
</body>
</html>