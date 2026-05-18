<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin'])) {
    require_once 'auth_guard.php';
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: view_borrowed_books.php");
    exit();
}

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book || empty($book['pdf_file'])) {
    header("Location: view_borrowed_books.php");
    exit();
}

$pdfUrl = "/Learning-Hub-LMS/public/" . $book['pdf_file'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($book['title']); ?> | Smart Reader</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/smart_assistant.css">

    <style>
        body { margin: 0; font-family: 'Plus Jakarta Sans', sans-serif; background: #0f1113; overflow: hidden; }
        .topbar {
            background: #ffffff;
            padding: 10px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 65px;
            box-sizing: border-box;
            border-bottom: 1px solid #e1e4e8;
        }
        .back-btn { text-decoration: none; color: #555; font-weight: 700; font-size: 14px; }
        .back-btn:hover { color: #3cb1c5; }
        .reader-layout { display: flex; height: calc(100vh - 65px); }
        .sidebar { width: 280px; background: #fff; padding: 25px; border-right: 1px solid #eee; overflow-y: auto; }
        .viewer-area { flex: 1; background: #2c2e31; }
        iframe { width: 100%; height: 100%; border: none; }
    </style>
</head>

<body>

<div class="topbar">
    <a href="view_borrowed_books.php" class="back-btn">← Back to Library</a>

    <div style="text-align: center;">
        <div style="font-size: 15px; font-weight: 800; color: #111;"><?php echo htmlspecialchars($book['title']); ?></div>
        <div style="font-size: 11px; color: #3cb1c5; font-weight: 700; text-transform: uppercase;"><?php echo htmlspecialchars($book['category'] ?? 'General'); ?></div>
    </div>

    <button class="ai-btn-trigger" onclick="toggleAI(<?php echo $id; ?>)">
        <span>✨</span> Smart Assistant
    </button>
</div>

<div class="reader-layout">
    <div class="sidebar">
        <h4 style="margin-top:0; color:#3cb1c5;">Book Overview</h4>
        <p style="font-size: 13px; color: #444;"><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
        <p style="font-size: 13px; color: #666; line-height: 1.6;">
            <?php echo nl2br(htmlspecialchars($book['description'] ?? 'No description available.')); ?>
        </p>
    </div>

    <div class="viewer-area">
        <iframe src="<?php echo $pdfUrl; ?>#toolbar=0&navpanes=0&scrollbar=1"></iframe>
    </div>
</div>

<div class="ai-panel" id="aiPanel">
    <div class="ai-header">
        <strong style="font-size: 14px; color: #fff;">Smart Assistant</strong>
        <button onclick="toggleAI()" style="background:none; border:none; color:white; font-size:24px; cursor:pointer;">&times;</button>
    </div>
    
    <div class="ai-tabs">
        <button class="tab-btn active" onclick="switchTab('t1', this)">Summary</button>
        <button class="tab-btn" onclick="switchTab('t2', this)">Key Points</button>
        <button class="tab-btn" onclick="switchTab('t3', this)">Related</button>
        <button class="tab-btn" onclick="switchTab('t4', this)">Help</button>
    </div>

    <div class="ai-content">
        <div id="ai-loader" style="display:none; text-align: center; padding: 20px;">
            <div style="color: #3cb1c5; font-size: 12px; font-weight: 700;">Processing book data...</div>
        </div>

        <div id="t1" class="tab-pane active"><p id="sum-text" style="font-size: 14px; line-height: 1.8; color: #ccc;"></p></div>
        <div id="t2" class="tab-pane"><ul id="point-list" class="point-list"></ul></div>
        <div id="t3" class="tab-pane"><div id="rec-list"></div></div>
        <div id="t4" class="tab-pane"><div id="faq-list"></div></div>
    </div>
</div>

<script src="../assets/js/smart_assistant.js"></script>

</body>
</html>