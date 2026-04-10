<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all books
$books_result = $conn->query("SELECT * FROM books ORDER BY created_at DESC");

// Fetch ALL borrowed books to check who borrowed what
$borrowed_query = $conn->query("SELECT book_id, user_id FROM borrowed_books WHERE returned_at IS NULL");
$borrowed_data = [];
if($borrowed_query){
    while($row = $borrowed_query->fetch_assoc()){
        $borrowed_data[$row['book_id']] = $row['user_id'];
    }
}
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
    --bg-light: #fdfdfd;
    --text-main: #0f172a;
    --text-muted: #64748b;
    --white: #ffffff;
    --accent-soft: rgba(60, 177, 197, 0.08);
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
    padding: 25px 60px; 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    border-bottom: 1px solid #e2e8f0; 
    position: sticky; 
    top: 0; 
    z-index: 100; 
}
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

.books-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 25px; }

.book-card { 
    background: var(--white); 
    padding: 25px; 
    border-radius: 16px; 
    box-shadow: 0 4px 20px rgba(0,0,0,0.02); 
    border: 1px solid #f1f5f9; 
    transition: 0.3s; 
    display: flex; 
    flex-direction: column; 
    height: 100%;
}

.book-card:hover { transform: translateY(-5px); border-color: var(--brand-teal); box-shadow: 0 10px 25px rgba(60, 177, 197, 0.1); }

.book-content { flex-grow: 1; }

.category-tag { font-size: 10px; text-transform: uppercase; font-weight: 800; color: var(--brand-teal); letter-spacing: 1px; margin-bottom: 8px; display: block; }
.book-card h4 { font-size: 18px; margin-bottom: 5px; color: var(--text-main); }
.book-card .author { font-size: 14px; color: var(--text-muted); margin-bottom: 10px; }

.desc-toggle { 
    cursor: pointer; font-size: 12px; color: var(--brand-teal); font-weight: 700; 
    background: var(--accent-soft); border: none; padding: 10px; border-radius: 10px;
    text-align: center; margin-top: 10px; transition: 0.2s; width: 100%;
}
.desc-toggle:hover { background: var(--brand-teal); color: white; }

.description { 
    display: none; font-size: 13px; color: var(--text-muted); margin-top: 15px; 
    padding-top: 15px; border-top: 1px dashed #e2e8f0; 
}
.show-desc { display: block; }

.status-box { display: flex; align-items: center; justify-content: space-between; padding-top: 15px; border-top: 1px solid #f1f5f9; margin-top: 15px; }
.status-text { font-size: 12px; font-weight: 700; }
.status-available { color: #10b981; }
.status-borrowed { color: #f43f5e; }

.action-btn { border: none; padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 13px; cursor: pointer; transition: 0.3s; width: 100px; text-align: center; }

.btn-borrow { background: var(--brand-teal); color: white; }
.btn-borrow:hover { background: var(--brand-dark); }

.btn-return { background: #fee2e2; color: #b91c1c; text-decoration: none; display: inline-block; line-height: 1.2; }
.btn-return:hover { background: #fecaca; }

.btn-disabled { background: #f1f5f9; color: #64748b; cursor: not-allowed; }

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

    <!-- Show confirmation message -->
    <?php if(isset($_SESSION['message'])): ?>
        <div class="confirm-msg"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <div class="section-header">
        <h2>All Library Books</h2>
        <p>Browse our complete catalog and manage your readings.</p>
    </div>

    <div class="books-grid">
        <?php while($book = $books_result->fetch_assoc()): 
            $book_id = $book['id'];
            $is_borrowed = isset($borrowed_data[$book_id]);
            $borrowed_by_me = ($is_borrowed && $borrowed_data[$book_id] == $user_id);
        ?>
        <div class="book-card">
            <div class="book-content">
                <span class="category-tag"><?php echo htmlspecialchars($book['category']); ?></span>
                <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                <p class="author">by <?php echo htmlspecialchars($book['author']); ?></p>
            </div>
            
            <button class="desc-toggle" onclick="toggleDescription(this)">Quick Details</button>
            <div class="description">
                <?php echo nl2br(htmlspecialchars($book['description'])); ?>
            </div>

            <div class="status-box">
                <span class="status-text <?php echo $is_borrowed ? 'status-borrowed' : 'status-available'; ?>">
                    <?php echo $is_borrowed ? '● Borrowed' : '● Available'; ?>
                </span>

                <?php if(!$is_borrowed): ?>
                    <form action="user_borrow.php" method="POST" 
onsubmit="return confirm('Are you sure you want to borrow this book?\n\nYou must return it within 14 days.');">
                        <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                        <input type="hidden" name="redirect" value="view_borrowed_books.php">
                        <button type="submit" class="action-btn btn-borrow">Borrow</button>
                    </form>
                
                <?php else: ?>
                    <button class="action-btn btn-disabled" disabled>Unavailable</button>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
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