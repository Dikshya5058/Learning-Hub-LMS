
<?php
require_once __DIR__ . '/auth_guard.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$greeting_prefix = "Welcome back,";
if (!isset($_SESSION['returning_user'])) {
    $greeting_prefix = "Great to have you,";
    $_SESSION['returning_user'] = true;
}

$stmt = $pdo->query("SELECT * FROM books LIMIT 6");
$books = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT book_id FROM borrowed_books WHERE user_id = ? AND returned_at IS NULL");
$stmt->execute([$user_id]);
$my_wishlist = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
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

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-light); color: var(--text-main); height: 100vh; overflow: hidden; }
        .dashboard-wrapper { display: flex; height: 100vh; }

        /* Sidebar */
        .sidebar { width: 260px; background-color: var(--sidebar-bg); border-right: 1px solid #f1f5f9; display: flex; flex-direction: column; padding: 40px 0; z-index: 10; }
        .sidebar-header { padding: 0 30px 40px; }
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

        .sidebar-support {
            margin: 0 20px 20px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 16px;
            text-align: center;
        }
        .sidebar-support p { font-size: 12px; color: var(--text-muted); margin-bottom: 12px; line-height: 1.4; }
        .btn-support-sm { 
            display: block; 
            text-decoration: none; 
            background: white; 
            color: var(--text-main); 
            font-size: 12px; 
            font-weight: 700; 
            padding: 8px; 
            border-radius: 8px; 
            border: 1px solid #e2e8f0;
            transition: 0.2s;
            cursor: pointer;
        }
        .btn-support-sm:hover { border-color: var(--brand-teal); color: var(--brand-teal); }

        .sidebar-footer { padding: 10px 30px 0px; border-top: 1px solid #f1f5f9; }
        .logout-link { display: flex; align-items: center; color: #f43f5e; text-decoration: none; font-weight: 700; font-size: 14px; gap: 10px; padding: 15px 0; }

        /* Main Content */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding: 0 60px; }
        .top-nav { display: flex; justify-content: flex-end; align-items: center; padding: 40px 0 20px 0; }

        .user-greeting { background: white; padding: 10px 25px; border-radius: 100px; border: 1px solid #f1f5f9; box-shadow: 0 4px 20px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 12px; }
        .user-greeting .avatar { width: 32px; height: 32px; background: var(--brand-teal); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; }
        .greet-txt { font-size: 13px; font-weight: 600; }
        .greet-txt span { color: var(--brand-teal); font-weight: 800; }

        .hero-section { margin-bottom: 20px; }
        .hero-section h2 { font-size: 32px; font-weight: 800; letter-spacing: -1px; margin-bottom: 8px; }
        .hero-section p { color: var(--text-muted); font-size: 15px; }

        /* Book Cards */
        .book-shelf { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 30px; margin-bottom: 40px; }
        .book-card { background: white; border-radius: 24px; padding: 24px; border: 1px solid #f1f5f9; transition: all 0.3s ease; display: flex; flex-direction: column; position: relative; overflow: hidden; }
        .book-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08); border-color: var(--brand-teal); }
        .book-card::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 5px; background: linear-gradient(90deg, var(--brand-teal), #8ce1ef); opacity: 0.6; }

        /* THE FIXED BADGE CSS */
        .card-header {
    display: flex;
    align-items: center;
    justify-content: space-between; /* Pushes status to the far right */
    gap: 8px;
    margin-bottom: 8px;
    width: 100%;
    overflow: hidden; /* Keeps everything inside the card */
}

.category-badge { 
    display: inline-block;
    padding: 4px 10px; 
    border-radius: 6px; 
    font-size: 9px; 
    font-weight: 800; 
    text-transform: uppercase; 
    background: var(--accent-soft); 
    color: var(--brand-dark); 
    
    /* These three lines handle long text */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis; 
    max-width: 65%; /* Limits category so status always has room */
}

.status-text { 
    display: inline-flex;
    align-items: center;
    font-size: 10px; 
    font-weight: 800; 
    text-transform: uppercase; 
    color: #10b981;
    white-space: nowrap; /* Prevents "Available" from wrapping or disappearing */
    flex-shrink: 0; /* Forces this element to NEVER shrink */
}
        .text-wishlist { color: #ef4444; }   

        .book-card h3 { font-size: 17px; font-weight: 800; color: var(--text-main); margin: 15px 0 4px; line-height: 1.4; }
        .author-tag { font-size: 13px; color: var(--text-muted); margin-bottom: 20px; display: block; }

        .desc-toggle { cursor: pointer; font-size: 12px; color: var(--brand-teal); font-weight: 700; background: var(--accent-soft); border: none; padding: 10px; border-radius: 10px; margin-top: auto; }
        .description { display: none; font-size: 13px; color: var(--text-muted); margin-top: 15px; line-height: 1.5; padding-top: 15px; border-top: 1px dashed #e2e8f0; }
        .show-desc { display: block; }

        .shelf-action { display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, #f8fafc, #f1f5f9); padding: 30px 40px; border-radius: 24px; margin-bottom: 60px; border: 1px solid #e2e8f0; }
        .btn-main { background: var(--brand-teal); color: white; text-decoration: none; padding: 14px 30px; border-radius: 14px; font-weight: 700; font-size: 14px; transition: 0.3s; }

        /* CONTACT MODAL CSS */
        #contactOverlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .contact-box {
            background: white;
            padding: 40px;
            border-radius: 24px;
            width: 450px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .contact-box h3 { margin-bottom: 10px; font-weight: 800; }
        .contact-box input, .contact-box textarea {
            width: 100%; padding: 12px; margin: 10px 0;
            border: 1px solid #e2e8f0; border-radius: 10px; font-family: inherit;
        }
        .contact-box button {
            background: var(--brand-teal); color: white; border: none; 
            padding: 12px 20px; border-radius: 10px; font-weight: 700; cursor: pointer;
        }
        .close-modal { background: #f1f5f9 !important; color: var(--text-main) !important; margin-left: 10px; }

    </style>
</head>
<body>

<div class="dashboard-wrapper">
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="user_dashboard.php" class="logo-box">
                <div class="logo-sq">📖</div>
                <div class="logo-text">
                    <h1>Learning Hub</h1>
                    <span>Library Management</span>
                </div>
            </a>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li><a href="user_dashboard.php" class="active">Dashboard</a></li>
                <li><a href="user_view_books.php">View All Books</a></li>
                <li><a href="view_borrowed_books.php">My Wishlist</a></li>
            </ul>
        </nav>

        <div class="sidebar-support">
            <p>Need help with your books?</p>
            <a href="javascript:void(0)" onclick="openContactModal()" class="btn-support-sm">Contact Support</a>
        </div>

        <div class="sidebar-footer">
            <a href="user_logout.php" class="logout-link"
   onclick="return confirm('Are you sure you want to logout?')">
    Log Out
</a>
        </div>
    </aside>

    <main class="main-content">
        <nav class="top-nav">
            <div class="user-greeting">
                <div class="avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                <div class="greet-txt"><?php echo $greeting_prefix; ?> <span><?php echo htmlspecialchars($user_name); ?></span></div>
            </div>
        </nav>

        <header class="hero-section">
            <h2>Explore Featured Books</h2>
            <p>Dive into our curated collection of literature and technology.</p>
        </header>

        <section class="book-shelf">
            <?php foreach($books as $book): 
                $on_my_wishlist = in_array($book['id'], $my_wishlist);
            ?>
            <div class="book-card">
                <div class="card-header">
                    <span class="category-badge"><?php echo htmlspecialchars($book['category']); ?></span>
                    <span class="status-text <?php echo $on_my_wishlist ? 'text-wishlist' : ''; ?>">● <?php echo $on_my_wishlist ? 'In Wishlist' : 'Available'; ?></span>
                </div>
                <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                <span class="author-tag">by <?php echo htmlspecialchars($book['author']); ?></span>
                <button class="desc-toggle" onclick="toggleDescription(this)">Quick Details</button>
                <div class="description"><?php echo nl2br(htmlspecialchars($book['description'])); ?></div>
            </div>
            <?php endforeach; ?>
        </section>

        <div class="shelf-action">
            <span style="font-weight: 700;">Ready for more? Explore the full library catalog.</span>
            <a href="user_view_books.php" class="btn-main">View Full Book List &rarr;</a>
        </div>
    </main>
</div>

<div id="contactOverlay">
    <div class="contact-box">
        <h3>Contact Us</h3>
        <div id="successMsg" style="display:none; background: #ecfdf5; color: #065f46; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; font-weight: 700; text-align: center; border: 1px solid #a7f3d0;">
            Thank you for contacting us. We'll soon reach out to you.
        </div>
        <div id="formContainer">
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 15px;">Send us a message and we'll get back to you.</p>
            <form id="contactForm">
                <input type="text" id="c_name" placeholder="Full Name" required>
                <input type="email" id="c_email" placeholder="Email Address" required>
                <textarea id="c_message" rows="4" placeholder="How can we help?" required></textarea>
                <div style="margin-top: 10px;">
                    <button type="submit" id="sendBtn">Send Message</button>
                    <button type="button" class="close-modal" onclick="closeContactModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleDescription(button) {
    const desc = button.nextElementSibling;
    desc.classList.toggle('show-desc');
    button.textContent = desc.classList.contains('show-desc') ? 'Hide Details' : 'Quick Details';
}

function openContactModal() {
    document.getElementById('contactOverlay').style.display = 'flex';
}

function closeContactModal() {
    document.getElementById('contactOverlay').style.display = 'none';
    setTimeout(() => {
        document.getElementById('successMsg').style.display = 'none';
        document.getElementById('formContainer').style.display = 'block';
        document.getElementById('contactForm').reset();
    }, 300);
}

document.getElementById('contactForm').onsubmit = function(e) {
    e.preventDefault();
    const btn = document.getElementById('sendBtn');
    btn.innerText = 'Sending...';

    const data = new FormData();
    data.append('name', document.getElementById('c_name').value);
    data.append('email', document.getElementById('c_email').value);
    data.append('message', document.getElementById('c_message').value);

    fetch('contact_us.php', {
        method: 'POST',
        body: data
    })
    .then(res => res.text())
    .then(response => {
        document.getElementById('formContainer').style.display = 'none';
        document.getElementById('successMsg').style.display = 'block';
        btn.innerText = 'Send Message';
        setTimeout(closeContactModal, 2500);
    });
};
</script>
</body>
</html>