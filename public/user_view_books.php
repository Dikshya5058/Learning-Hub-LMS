<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all books from the library
$stmt = $pdo->query("SELECT * FROM books ORDER BY created_at DESC");
$books = $stmt->fetchAll();

// Fetch ONLY the current user's active wishlist items
$stmt = $pdo->prepare("SELECT book_id FROM borrowed_books WHERE user_id = ? AND returned_at IS NULL");
$stmt->execute([$user_id]);
$my_wishlist = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Books - Learning Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-teal: #3cb1c5; 
            --brand-dark: #2e8d9e;
            --bg-light: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --white: #ffffff;
            --accent-soft: rgba(60, 177, 197, 0.08);
            /* NEW: Wishlist Red Theme */
            --wishlist-red: #ef4444;
            --wishlist-red-bg: #fef2f2;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg-light); 
            color: var(--text-main);
            line-height: 1.6;
        }

        header { 
            background: var(--white); 
            padding: 20px 60px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 1px solid #e2e8f0; 
            position: sticky; 
            top: 0; 
            z-index: 100; 
        }

        header h1 { font-size: 22px; font-weight: 800; color: var(--brand-teal); }

        .dash-btn { 
            text-decoration: none; 
            color: var(--text-main); 
            font-weight: 700; 
            font-size: 14px; 
            padding: 10px 20px; 
            border-radius: 10px; 
            background: #f1f5f9; 
            transition: 0.3s; 
        }

        .dash-btn:hover { background: var(--brand-teal); color: white; }

        .container { max-width: 1200px; margin: 40px auto; padding: 0 40px; }

        .section-header { margin-bottom: 40px; }
        .section-header h2 { font-size: 32px; font-weight: 800; letter-spacing: -1px; }
        .section-header p { color: var(--text-muted); font-size: 16px; }

        .confirm-msg { 
            background: #f0fdf4; 
            color: #16a34a; 
            padding: 15px 20px; 
            border-radius: 12px; 
            margin-bottom: 30px; 
            font-weight: 700; 
            border-left: 5px solid #16a34a; 
        }

        .books-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
            gap: 25px; 
            align-items: stretch; 
        }

        .book-card { 
            background: var(--white); 
            padding: 28px; 
            border-radius: 20px; 
            border: 1px solid #e2e8f0; 
            transition: all 0.3s ease; 
            display: flex; 
            flex-direction: column; 
            height: 100%; 
        }

        .book-card:hover { 
            transform: translateY(-5px); 
            border-color: var(--brand-teal); 
            box-shadow: 0 15px 30px rgba(0,0,0,0.05); 
        }

        .book-content { 
            flex-grow: 1; 
            display: flex; 
            flex-direction: column; 
        }

        .category-tag { 
            font-size: 10px; 
            text-transform: uppercase; 
            font-weight: 800; 
            color: var(--brand-teal); 
            background: rgba(60, 177, 197, 0.1); 
            padding: 4px 10px; 
            border-radius: 6px; 
            display: inline-block; 
            margin-bottom: 15px; 
            width: fit-content;
        }

        .book-card h4 { 
            font-size: 19px; 
            font-weight: 800; 
            margin-bottom: 8px; 
            color: var(--text-main); 
            line-height: 1.3; 
        }

        .book-card .author { 
            font-size: 14px; 
            color: var(--text-muted); 
            margin-bottom: 20px; 
        }

        .status-box { 
            margin-top: auto; 
            padding-top: 20px; 
            border-top: 1px solid #f1f5f9; 
            display: flex; 
            flex-direction: column; 
            gap: 12px; 
        }

        .status-text { font-size: 12px; font-weight: 700; }
        .status-available { color: #10b981; }
        
        /* UPDATED: Red status text */
        .status-wishlisted { color: var(--wishlist-red); }

        .action-btn { 
            border: none; 
            padding: 12px; 
            border-radius: 12px; 
            font-weight: 800; 
            font-size: 13px; 
            cursor: pointer; 
            transition: 0.3s; 
            width: 100%; 
            text-align: center; 
        }

        .btn-borrow { background: var(--brand-teal); color: white; }
        .btn-borrow:hover { background: var(--brand-dark); }

        /* UPDATED: Red button for wishlisted state */
        .btn-disabled { 
            background: var(--wishlist-red-bg); 
            color: var(--wishlist-red); 
            cursor: not-allowed; 
            border: 1.5px solid #fee2e2; 
        }

        .desc-toggle { 
            cursor: pointer; 
            font-size: 12px; 
            color: var(--brand-teal); 
            font-weight: 700; 
            background: var(--accent-soft); 
            border: none; 
            padding: 8px; 
            border-radius: 8px; 
            margin-bottom: 15px;
        }

        .description { 
            display: none; 
            font-size: 13px; 
            color: var(--text-muted); 
            margin-bottom: 15px;
            background: #f8fafc;
            padding: 10px;
            border-radius: 8px;
        }
        .show-desc { display: block; }

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
        <h2>All Library Books</h2>
        <p>Explore our collection and save your favorites to your personal wishlist.</p>
    </div>

    <div class="books-grid">
        <?php foreach($books as $book): 
            $book_id = $book['id'];
            $is_on_my_wishlist = in_array($book_id, $my_wishlist);
        ?>
        <div class="book-card">
            <div class="book-content">
                <span class="category-tag"><?php echo htmlspecialchars($book['category']); ?></span>
                <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                <p class="author">by <?php echo htmlspecialchars($book['author']); ?></p>
                
                <button class="desc-toggle" onclick="toggleDescription(this)">Quick Details</button>
                <div class="description">
                    <?php echo nl2br(htmlspecialchars($book['description'])); ?>
                </div>
            </div>

            <div class="status-box">
                <?php if($is_on_my_wishlist): ?>
                    <span class="status-text status-wishlisted">● Already in Wishlist</span>
                    <button class="action-btn btn-disabled" disabled>Wishlisted</button>
                <?php else: ?>
                    <span class="status-text status-available">● Available to add</span>
                    <form action="user_borrow.php" method="POST" onsubmit="return confirm('Add this book to your wishlist?');">
                        <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                        <button type="submit" class="action-btn btn-borrow">Add to wishlist</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <a href="user_dashboard.php" class="back-link">← Back to Dashboard</a>
</div>

<script>
function toggleDescription(button) {
    const desc = button.nextElementSibling;
    desc.classList.toggle('show-desc');
    button.textContent = desc.classList.contains('show-desc') ? 'Hide Details' : 'Quick Details';
}
</script>

</body>
</html>