<?php
require '../config/db.php';

$stmt = $pdo->query("SELECT * FROM books ORDER BY id DESC LIMIT 8");
$books = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Learning Hub</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
:root{
    --primary:#3cb1c5;
    --bg:#f8fafc;
    --card:#ffffff;
    --text:#0f172a;
    --muted:#64748b;
    --border:#e5e7eb;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter', sans-serif;
}

body{
    background:var(--bg);
    color:var(--text);
}

/* HEADER */
header{
    background:white;
    padding:20px 70px;
    border-bottom:1px solid var(--border);
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.logo{
    font-weight:800;
    color:var(--primary);
    font-size:18px;
}

.subtitle{
    font-size:13px;
    color:var(--muted);
}

/* HERO */
.hero{
    padding:60px 70px 30px;
}

.hero h1{
    font-size:36px;
    font-weight:800;
}

.hero p{
    margin-top:8px;
    color:var(--muted);
    max-width:600px;
}

/* GRID */
.grid{
    padding:30px 70px 60px;
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(260px,1fr));
    gap:20px;
}

/* CARD */
.card{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:14px;
    padding:18px;
    transition:0.2s;
}

.card:hover{
    border-color:rgba(60,177,197,0.5);
    transform:translateY(-4px);
}

/* TAG */
.tag{
    font-size:10px;
    font-weight:700;
    color:var(--primary);
    background:rgba(60,177,197,0.08);
    display:inline-block;
    padding:4px 8px;
    border-radius:6px;
}

/* TITLE */
.title{
    margin-top:10px;
    font-size:16px;
    font-weight:700;
}

/* AUTHOR */
.meta{
    font-size:12px;
    color:var(--muted);
    margin-top:4px;
}

/* DETAILS */
.details{
    display:none;
    margin-top:12px;
    font-size:13px;
    color:#334155;
    line-height:1.6;
    border-left:2px solid var(--primary);
    padding-left:10px;
}

/* BUTTON */
.btn{
    margin-top:15px;
    width:100%;
    padding:9px;
    border:none;
    border-radius:10px;
    background:rgba(60,177,197,0.12);
    color:var(--primary);
    font-weight:700;
    cursor:pointer;
    transition:0.2s;
}

.btn:hover{
    background:var(--primary);
    color:white;
}

/* CTA */
.cta{
    margin:20px 70px 70px;
    padding:25px;
    background:white;
    border:1px solid var(--border);
    border-radius:14px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.cta a{
    background:var(--primary);
    color:white;
    text-decoration:none;
    padding:10px 18px;
    border-radius:10px;
    font-weight:700;
}

.cta a:hover{
    opacity:0.9;
}
</style>
</head>

<body>

<header>
    <div class="logo">📚 Learning Hub</div>
    <div class="subtitle">Modern Digital Library</div>
</header>

<div class="hero">
    <h1>Explore featured books</h1>
    <p>A clean curated collection of books before full access.</p>
</div>

<div class="grid">

<?php foreach($books as $book): ?>
    <div class="card">

        <span class="tag">
            <?php echo htmlspecialchars($book['category']); ?>
        </span>

        <div class="title">
            <?php echo htmlspecialchars($book['title']); ?>
        </div>

        <div class="meta">
            <?php echo htmlspecialchars($book['author']); ?>
        </div>

        <div class="details">
            <?php echo nl2br(htmlspecialchars($book['description'])); ?>
        </div>

        <button class="btn" onclick="toggle(this)">
            Quick Details
        </button>

    </div>
<?php endforeach; ?>

</div>

<div class="cta">
    <div>
        <strong>Unlock full library access</strong><br>
        <span style="color:var(--muted); font-size:13px;">
            Sign up to borrow and manage books
        </span>
    </div>

    <a href="landing_page.php">Get Started</a>
</div>

<script>
function toggle(btn){
    const card = btn.parentElement;
    const d = card.querySelector('.details');

    if(d.style.display === "block"){
        d.style.display = "none";
        btn.innerText = "Quick Details";
    } else {
        d.style.display = "block";
        btn.innerText = "Hide Details";
    }
}
</script>

</body>
</html>