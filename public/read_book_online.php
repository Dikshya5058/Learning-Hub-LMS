<?php
session_start();
require_once __DIR__ . '/../config/db.php';

/* ================================
   AUTH CHECK (USER + SUBSCRIPTION)
================================ */
if (!isset($_SESSION['admin'])) {
    require_once 'auth_guard.php';
}

/* ================================
   GET BOOK ID
================================ */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: view_borrowed_books.php");
    exit();
}

$id = (int) $_GET['id'];

/* ================================
   FETCH BOOK
================================ */
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

/* VALIDATION */
if (!$book || empty($book['pdf_file'])) {
    header("Location: view_borrowed_books.php");
    exit();
}

/* ================================
   FIXED PDF PATH (IMPORTANT FIX)
================================ */
$pdfUrl = "/Learning-Hub-LMS/" . $book['pdf_file'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($book['title']); ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        body{
            margin:0;
            font-family:'Plus Jakarta Sans', sans-serif;
            background:#f8f9fa;
        }

        .topbar{
            background:white;
            padding:14px 24px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            box-shadow:0 3px 10px rgba(0,0,0,0.05);
        }

        .back-btn{
            text-decoration:none;
            color:#3cb1c5;
            font-weight:700;
        }

        .reader-layout{
            display:flex;
            height:calc(100vh - 60px);
        }

        .sidebar{
            width:260px;
            background:white;
            padding:24px;
            border-right:1px solid #eee;
            overflow-y:auto;
        }

        .sidebar h3{
            margin-bottom:6px;
        }

        .sidebar p{
            font-size:13px;
            color:#555;
            line-height:1.6;
        }

        .viewer-area{
            flex:1;
            background:#525659;
            display:flex;
            flex-direction:column;
        }

        .viewer-bar{
            background:#3a3d40;
            color:#ccc;
            padding:10px 18px;
            font-size:12px;
        }

        iframe{
            flex:1;
            width:100%;
            border:none;
        }
    </style>
</head>

<body>

<div class="topbar">
    <a href="view_borrowed_books.php" class="back-btn">← Back</a>

    <strong>
        <?php echo htmlspecialchars($book['title']); ?>
    </strong>

    <span>
        <?php echo htmlspecialchars($book['category'] ?? 'General'); ?>
    </span>
</div>

<div class="reader-layout">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h3><?php echo htmlspecialchars($book['title']); ?></h3>

        <p>
            <strong>Author:</strong>
            <?php echo htmlspecialchars($book['author']); ?>
        </p>

        <hr style="border:0; border-top:1px solid #eee; margin:15px 0;">

        <p>
            <strong>Description:</strong><br>
            <?php echo nl2br(htmlspecialchars($book['description'] ?? 'No description available.')); ?>
        </p>
    </div>

    <!-- PDF VIEWER -->
    <div class="viewer-area">

        <div class="viewer-bar">
            Reading: <?php echo htmlspecialchars($book['title']); ?>
        </div>

        <iframe src="<?php echo $pdfUrl; ?>#toolbar=0&navpanes=0&scrollbar=1"></iframe>

    </div>

</div>

</body>
</html>