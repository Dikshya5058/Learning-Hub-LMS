<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: index.html");
    exit();
}

$stmt = $pdo->query("SELECT COUNT(*) FROM books");
$totalBooks = $stmt->fetchColumn();

$booksStmt = $pdo->query("SELECT id, title, author, category, content FROM books ORDER BY id DESC");
$books = $booksStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS | Admin Dashboard</title>

    <style>
body {
    margin: 0;
    font-family: 'Segoe UI';
    background-color: #f8f9fa;
}

.dashboard-wrapper {
    max-width: 1100px;
    margin: 80px auto;
}

.admin-header {
    background: white;
    padding: 20px;
    border-radius: 12px;
    display: flex;
    justify-content: space-between;
}

table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { padding:15px; border-bottom:1px solid #eee; }
th { color:#4db6ac; }

.edit-btn { color:#3cb1c5; border:1px solid #3cb1c5; padding:6px; }
.delete-btn { color:#e24b4a; border:1px solid #e24b4a; padding:6px; }
    </style>
</head>
<body style="background:#f1f7f9;">
    <div class="dashboard-wrapper">
        <header class="admin-header">
            <h2 style="margin:0; font-size:1.5rem;">Admin Dashboard</h2>
            <div style="display:flex; gap:10px; align-items:center;">
                <form action="admin_logout.php" method="POST" style="margin:0;">
                    <button type="submit" class="logout-btn-header">Logout</button>
                </form>
            </div>
        </header>

        <section class="manage-section" style="max-width:100%; margin-top:30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom:20px;">
                <h3 style="margin:0;">Library Management</h3>
                <a href="add_book.php" style="text-decoration: none; background: #3cb1c5; color: white; padding: 10px 20px; border-radius: 8px; font-weight: bold; font-size:14px;">+ Add New Book</a>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($book['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><span style="background:#e0f2f1; color:#00796b; padding:4px 10px; border-radius:20px; font-size:12px;"><?php echo htmlspecialchars($book['category']); ?></span></td>
                        <td>
                            <div class="action-links">
                                <a href="edit_book.php?id=<?php echo $book['id']; ?>" class="edit-btn">Edit</a>
                                <a href="delete_book.php?id=<?php echo $book['id']; ?>" class="delete-btn">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>