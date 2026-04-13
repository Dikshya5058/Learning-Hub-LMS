<?php
session_start();
require __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: index.html");
    exit();
}

$error = "";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    header("Location: dashboard.php");
    exit();
}

if (isset($_POST['edit_book'])) {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title && $author && $category) {
        try {
            $sql = "UPDATE books 
                    SET title = ?, author = ?, category = ?, description = ? 
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$title, $author, $category, $description, $id])) {
                // Store success message and redirect to dashboard
                $_SESSION['success_msg'] = "Book updated successfully!";
                header("Location: dashboard.php");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    } else {
        $error = "Title, Author, and Category are required.";
    }
}

$book_title = $book['title'] ?? '';
$book_author = $book['author'] ?? '';
$book_category = $book['category'] ?? '';
$book_description = $book['description'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LMS | Edit Book</title>
<style>
    * { box-sizing: border-box; }
    body{ margin:0; padding:0; font-family:'Segoe UI', sans-serif; background:#f8f9fa; color:#333; }
    .container{ display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:100vh; padding:40px 16px; }
    .form-card{ background:#fff; padding:40px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.07); width:100%; max-width:500px; }
    h2{ margin:0 0 6px 0; font-size:1.5rem; }
    .subtitle{ color:#888; font-size:14px; margin-bottom:24px; }
    label{ display:block; font-size:13px; font-weight:600; color:#555; margin-bottom:6px; }
    input[type=text], textarea{ width:100%; padding:12px 14px; margin-bottom:18px; border-radius:8px; border:1px solid #e0e0e0; font-size:14px; transition:0.2s; }
    input:focus, textarea:focus{ border-color:#3cb1c5; outline:none; }
    .section-label{ font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#3cb1c5; margin-bottom:14px; }
    .section-divider{ border:none; border-top:1px solid #f0f0f0; margin:8px 0 22px 0; }
    button{ width:100%; padding:14px; background:#3cb1c5; color:white; border:none; border-radius:8px; font-size:15px; font-weight:600; cursor:pointer; transition:.2s; }
    button:hover{ background:#26a69a; }
    .cancel-link{ display:block; text-align:center; margin-top:16px; text-decoration:none; color:#888; font-size:14px; font-weight:600; }
    .alert{ padding:12px 16px; border-radius:8px; margin-bottom:18px; font-size:13px; font-weight:600; text-align:center; }
    .alert-error{ background:#fdecea; color:#e24b4a; }
</style>
</head>
<body>
<div class="container">
    <div class="form-card">
        <h2>Edit Book Details</h2>
        <p class="subtitle">Modify the information for "<?php echo htmlspecialchars($book_title); ?>"</p>

        <?php if($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="section-label">📖 Book Information</div>
            <label>Book Title</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($book_title); ?>" required>
            <label>Author</label>
            <input type="text" name="author" value="<?php echo htmlspecialchars($book_author); ?>" required>
            <label>Category</label>
            <input type="text" name="category" value="<?php echo htmlspecialchars($book_category); ?>" required>
            <hr class="section-divider">
            <div class="section-label">📝 Description</div>
            <label>Description</label>
            <textarea name="description" rows="6"><?php echo htmlspecialchars($book_description); ?></textarea>
            <button type="submit" name="edit_book">Update Book</button>
            <a href="dashboard.php" class="cancel-link">Return to Dashboard</a>
        </form>
    </div>
</div>
</body>
</html>