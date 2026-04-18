<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$greeting_prefix = "Welcome back,";
if (!isset($_SESSION['returning_user'])) {
    $greeting_prefix = "Great to have you,";
    $_SESSION['returning_user'] = true;
}

// Fetch books
$stmt = $pdo->query("SELECT * FROM books LIMIT 6");
$books = $stmt->fetchAll();

// Fetch wishlist/borrowed data to check status
$stmt = $pdo->query("SELECT book_id, user_id FROM borrowed_books WHERE returned_at IS NULL");
$wishlist_data = [];
foreach ($stmt as $row) {
    $wishlist_data[$row['book_id']] = $row['user_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Learning Hub | Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --brand-teal: #3cb1c5;
    --brand-dark: #2e8d9e;
    --bg-light: #fdfdfd;
    --sidebar-bg: #ffffff;
    --text-main: #0f172a;
    --text-muted: #64748b;
    --accent-soft: rgba(60, 177, 197, 0.08);
}

/* Reset */
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-light); color: var(--text-main); height: 100vh; overflow: hidden; }

.dashboard-wrapper { display: flex; height: 100vh; }

/* Sidebar */
.sidebar { width: 260px; background-color: var(--sidebar-bg); border-right: 1px solid #f1f5f9; display: flex; flex-direction: column; padding: 40px 0; z-index: 10; }
.sidebar-header { padding: 0 30px 50px; }
.logo-box { display: flex; align-items: center; gap: 12px; text-decoration: none; }
.logo-sq { width: 38px; height: 38px; background: var(--brand-teal); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; box-shadow: 0 8px 15px rgba(60, 177, 197, 0.2); }
.logo-text h1 { font-size: 18px; font-weight: 800; color: var(--brand-teal); line-height: 1; }
.logo-text span { font-size: 9px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }

.sidebar-nav { flex-grow: 1; }
.sidebar-nav ul { list-style: none; }
.sidebar-nav li a { display: flex; align-items: center; padding: 14px 30px; text-decoration: none; color: var(--text-muted); font-weight: 600; font-size: 14px; transition: 0.3s; position: relative; }
.sidebar-nav li a:hover, .sidebar-nav li a.active { color: var(--brand-teal); background: var(--accent-soft); }
.sidebar-nav li a.active::after { content: ''; position: absolute; right: 0; height: 20px; width: 4px; background: var(--brand-teal); border-radius: 4px 0 0 4px; }
.sidebar-nav li a .icon { margin-right: 15px; font-size: 18px; opacity: 0.8; }

.sidebar-footer { padding: 20px 30px; }
.logout-link { display: flex; align-items: center; color: #f43f5e; text-decoration: none; font-weight: 700; font-size: 14px; gap: 10px; }

.main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding: 0 60px; }

.top-nav { display: flex; justify-content: flex-end; align-items: center; padding: 40px 0 20px 0; }

.user-greeting { background: white; padding: 10px 25px; border-radius: 100px; border: 1px solid #f1f5f9; box-shadow: 0 4px 20px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 12px; }
.user-greeting .avatar { width: 32px; height: 32px; background: var(--brand-teal); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; }
.greet-txt { font-size: 13px; font-weight: 600; }
.greet-txt span { color: var(--brand-teal); font-weight: 800; }

/* Hero Section */
.hero-section { margin-bottom: 20px; }
.hero-section h2 { font-size: 32px; font-weight: 800; letter-spacing: -1px; margin-bottom: 8px; }
.hero-section p { color: var(--text-muted); font-size: 15px; max-width: 500px; line-height: 1.6; }

.book-shelf { 
    display: grid; 
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); 
    gap: 30px; 
    margin-bottom: 60px; 
}

.book-card { 
    background: white; 
    border-radius: 24px; 
    padding: 24px; 
    border: 1px solid #f1f5f9; 
    transition: all 0.3s ease; 
    display: flex; 
    flex-direction: column; 
    position: relative;
    overflow: hidden;
}

.book-card:hover { 
    transform: translateY(-8px); 
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08); 
    border-color: var(--brand-teal); 
}

.book-card::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 5px;
    background: linear-gradient(90deg, var(--brand-teal), #8ce1ef);
    opacity: 0.6;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    gap: 10px;
}

.badge-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.book-card h3 { 
    font-size: 17px; 
    font-weight: 800; 
    color: var(--text-main);
    margin-bottom: 4px;
    line-height: 1.4;
}

.book-card .author-tag { 
    font-size: 13px; 
    color: var(--text-muted); 
    margin-bottom: 20px;
    display: block;
}

.category-badge { 
    padding: 4px 10px; 
    border-radius: 6px; 
    font-size: 9px; 
    font-weight: 800; 
    text-transform: uppercase; 
    background: var(--accent-soft); 
    color: var(--brand-dark);
}

/* Status text WITHOUT boxes */
.status-text { 
    font-size: 10px; 
    font-weight: 800; 
    text-transform: uppercase; 
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.text-avail { color: #10b981; }
.text-brwd { color: #f43f5e; }

.status-text::before {
    content: "●";
    font-size: 8px;
}

.desc-toggle { 
    cursor: pointer; 
    font-size: 12px; 
    color: var(--brand-teal); 
    font-weight: 700; 
    background: var(--accent-soft); 
    border: none; 
    padding: 10px; 
    border-radius: 10px;
    text-align: center;
    margin-top: auto;
    transition: 0.2s;
}
.desc-toggle:hover { background: var(--brand-teal); color: white; }

.description { 
    display: none; 
    font-size: 13px; 
    color: var(--text-muted); 
    margin-top: 15px; 
    line-height: 1.5;
    padding-top: 15px;
    border-top: 1px dashed #e2e8f0;
}
.show-desc { display: block; animation: fadeIn 0.4s ease; }

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

.shelf-action { 
    display: flex; 
    align-items: center; 
    justify-content: space-between; 
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    padding: 30px 40px; 
    border-radius: 24px; 
    margin-bottom: 80px; 
    border: 1px solid #e2e8f0;
}
.shelf-action span { font-weight: 700; color: var(--text-main); }
.btn-main { background: var(--brand-teal); color: white; text-decoration: none; padding: 14px 30px; border-radius: 14px; font-weight: 700; font-size: 14px; transition: 0.3s; box-shadow: 0 10px 20px rgba(60, 177, 197, 0.2); }
.btn-main:hover { transform: translateY(-2px); box-shadow: 0 15px 25px rgba(60, 177, 197, 0.3); }

</style>
</head>
<body>

<div class="dashboard-wrapper">
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="#" class="logo-box">
                <div class="logo-sq">📖</div>
                <div class="logo-text">
                    <h1>Learning Hub</h1>
                    <span>Library Management</span>
                </div>
            </a>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li><a href="user_dashboard.php" class="active"><span class="icon">🏠</span> Dashboard</a></li>
                <li><a href="user_view_books.php"><span class="icon">📚</span> View All Books</a></li>
                <li><a href="view_borrowed_books.php"><span class="icon">📑</span> My Wishlist</a></li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <a href="user_logout.php" class="logout-link"><span>🚪</span> Log Out</a>
        </div>
    </aside>

    <main class="main-content">
        <nav class="top-nav">
            <div class="user-greeting">
                <div class="avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                <div class="greet-txt">
                    <?php echo $greeting_prefix; ?> <span><?php echo htmlspecialchars($user_name); ?></span>
                </div>
            </div>
        </nav>

        <header class="hero-section">
            <h2>Explore Featured Books</h2>
            <p>Dive into our curated collection of literature, science, and technology.</p>
        </header>

        <section class="book-shelf">
            <?php foreach($books as $book): 
                $book_id = $book['id'];
                $is_occupied = isset($wishlist_data[$book_id]);
                $on_my_wishlist = ($is_occupied && $wishlist_data[$book_id] == $user_id);
            ?>
            <div class="book-card">
                <div class="card-header">
                    <div class="badge-container">
                        <span class="category-badge"><?php echo htmlspecialchars($book['category']); ?></span>

                        <span class="status-text <?php echo $is_occupied ? 'text-brwd' : 'text-avail'; ?>">
                            <?php 
                                if ($on_my_wishlist) {
                                    echo 'In Wishlist';
                                } elseif ($is_occupied) {
                                    echo 'Unavailable';
                                } else {
                                    echo 'Available';
                                }
                            ?>
                        </span>
                    </div>
                    <span style="font-size: 16px; opacity: 0.3;">🔖</span>
                </div>

                <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                <span class="author-tag">by <?php echo htmlspecialchars($book['author']); ?></span>

                <button class="desc-toggle" onclick="toggleDescription(this)">Quick Details</button>
                <div class="description">
                    <?php echo nl2br(htmlspecialchars($book['description'])); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </section>

        <div class="shelf-action">
            <span>Ready for more? Explore the full library catalog.</span>
            <a href="user_view_books.php" class="btn-main">View Full Book List &rarr;</a>
        </div>
    </main>
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