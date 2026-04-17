<?php
session_start();
require '../config/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->query("
    SELECT b.id, b.title, b.author, b.category, 
           bb.user_id, bb.borrowed_at, bb.due_date,
           u.name AS borrower_name
    FROM borrowed_books bb
    JOIN books b ON bb.book_id = b.id
    JOIN users u ON bb.user_id = u.id
    WHERE bb.returned_at IS NULL
    ORDER BY bb.borrowed_at DESC
");

$borrowed_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Borrowed Books - Learning Hub</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --brand-teal: #3cb1c5; 
    --brand-dark: #2e8d9e;
    --bg-light: #fdfdfd;
    --text-main: #0f172a;
    --text-muted: #64748b;
    --white: #ffffff;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg-light); color: var(--text-main); line-height: 1.6; }

header { background: var(--white); padding: 25px 60px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 100; }

header h1 { font-size: 22px; font-weight: 800; color: var(--brand-teal); }

.dash-btn { text-decoration: none; color: var(--text-main); font-weight: 700; font-size: 14px; padding: 8px 20px; border-radius: 8px; background: #f1f5f9; transition: 0.3s; }

.dash-btn:hover { background: var(--brand-teal); color: white; }

.container { max-width: 1200px; margin: 40px auto; padding: 0 40px; }

.section-header { margin-bottom: 30px; }

.section-header h2 { font-size: 28px; font-weight: 800; letter-spacing: -0.5px; }

.section-header p { color: var(--text-muted); font-size: 15px; }

.confirm-msg { 
    background: #ecfdf5; 
    color: #10b981; 
    padding: 12px 18px; 
    border-radius: 8px; 
    margin-bottom: 25px; 
    font-weight: 700;
}

.books-grid { 
    display: grid; 
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); 
    gap: 25px; 
}

.book-card { 
    background: var(--white); 
    padding: 25px; 
    border-radius: 16px; 
    box-shadow: 0 4px 20px rgba(0,0,0,0.02); 
    border: 1px solid #f1f5f9; 
    transition: 0.3s; 
    display: flex; 
    flex-direction: column; 
    justify-content: space-between;   /* ✅ FIX ADDED */
}

.book-card:hover { 
    transform: translateY(-5px); 
    border-color: var(--brand-teal); 
    box-shadow: 0 10px 25px rgba(60, 177, 197, 0.1); 
}

.category-tag { 
    font-size: 10px; 
    text-transform: uppercase; 
    font-weight: 800; 
    color: var(--brand-teal); 
    letter-spacing: 1px; 
    margin-bottom: 8px; 
    display: block; 
}

.book-card h4 { 
    font-size: 18px; 
    margin-bottom: 5px; 
    color: var(--text-main); 
}

.book-card .author { 
    font-size: 14px; 
    color: var(--text-muted); 
    margin-bottom: 10px; 
}

.status-box { 
    display: flex; 
    flex-direction: column; 
    padding-top: 15px; 
    border-top: 1px solid #f1f5f9; 
    margin-top: auto;  /* ✅ FIX ADDED */
}

.status-text { 
    font-size: 12px; 
    font-weight: 700; 
    margin-bottom: 8px; 
    color: #f43f5e; 
}

.action-btn { 
    border: none; 
    padding: 10px 18px; 
    border-radius: 10px; 
    font-weight: 800; 
    font-size: 13px; 
    cursor: pointer; 
    transition: 0.3s; 
}

.btn-return { 
    background: #fee2e2; 
    color: #b91c1c; 
}

.btn-return:hover { 
    background: #fecaca; 
    transform: scale(1.05); 
}

.back-link { 
    margin-top: 40px; 
    display: inline-flex; 
    align-items: center; 
    gap: 8px; 
    font-weight: 700; 
    color: var(--text-muted); 
    text-decoration: none; 
    font-size: 14px; 
}

.back-link:hover { 
    color: var(--brand-teal); 
}
</style>
</head>

<body>

<header>
    <h1>Learning Hub</h1>
    <a href="user_dashboard.php" class="dash-btn">Dashboard</a>
</header>

<div class="container">

    <?php if(isset($_SESSION['message'])): ?>
        <div class="confirm-msg">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <div class="section-header">
        <h2>All Borrowed Books</h2>
        <p>See all borrowed books and track their due dates.</p>
    </div>

    <div class="books-grid">

        <?php if(count($borrowed_books) > 0): ?>
            <?php foreach($borrowed_books as $book): ?>

            <div class="book-card">
                <span class="category-tag">
                    <?php echo htmlspecialchars($book['category']); ?>
                </span>

                <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                <p class="author">by <?php echo htmlspecialchars($book['author']); ?></p>

                <div class="status-box">
                    <span class="status-text">
                        ● Borrowed by: <?php echo htmlspecialchars($book['borrower_name']); ?><br>
                        ● Borrowed on: <?php echo date('d M Y', strtotime($book['borrowed_at'])); ?><br>
                        ● Due date: <?php echo date('d M Y', strtotime($book['due_date'])); ?>
                    </span>

                    <?php if($book['user_id'] == $user_id): ?>
                        <form action="user_return.php" method="POST"
                              onsubmit="return confirm('Are you sure you want to return this book?');">
                            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                            <button type="submit" class="action-btn btn-return">Return</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <?php endforeach; ?>
        <?php else: ?>
            <p>No books are currently borrowed.</p>
        <?php endif; ?>

    </div>

    <a href="user_dashboard.php" class="back-link">← Back to Dashboard</a>
</div>

</body>
</html>