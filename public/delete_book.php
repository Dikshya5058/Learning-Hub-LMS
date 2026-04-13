<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Check admin session
if (!isset($_SESSION['admin'])) {
    header("Location: index.html");
    exit();
}

$error = "";
$book  = null;

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = (int) $_GET['id'];

// Fetch book details to display the name in the confirmation
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$book = $stmt->fetch();

// If book doesn't exist, go back
if (!$book) {
    header("Location: dashboard.php");
    exit();
}

// Handle deletion logic
if (isset($_POST['confirm_delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
        if ($stmt->execute([$id])) {
            // Set success message in session for the dashboard
            $_SESSION['success_msg'] = "Book '" . $book['title'] . "' has been deleted successfully.";
            
            // REDIRECT TO DASHBOARD
            header("Location: dashboard.php");
            exit();
        }
    } catch (PDOException $e) {
        $error = "Error deleting book: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Book — LMS</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }

        .main-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        h2 {
            margin-top: 0;
            color: #333;
        }

        .book-title {
            font-weight: 700;
            color: #e24b4a;
            font-size: 1.2rem;
            margin: 15px 0;
        }

        .btn-row {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn-confirm {
            background: #e24b4a;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-confirm:hover {
            background: #c63d3c;
        }

        .btn-back {
            background: #f1f3f5;
            color: #495057;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
        }

        .btn-back:hover {
            background: #e9ecef;
        }

        .alert-error {
            background: #fdecea;
            color: #e24b4a;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="card">
            <h2>Delete Book?</h2>
            
            <?php if ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <p>Are you sure you want to remove this book from the library?</p>
            <p class="book-title"><?= htmlspecialchars($book['title']) ?></p>
            <p style="color: #888; font-size: 13px;">This action cannot be undone.</p>

            <form method="POST">
                <div class="btn-row">
                    <a href="dashboard.php" class="btn-back">Cancel</a>
                    <button type="submit" name="confirm_delete" class="btn-confirm">Yes, Delete</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>