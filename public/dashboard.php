<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: index.html");
    exit();
}

$stmt = $pdo->query("SELECT COUNT(*) FROM books");
$totalBooks = $stmt->fetchColumn();

$booksStmt = $pdo->query("SELECT id, title, author, category, description, pdf_file FROM books ORDER BY id DESC");
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
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
        }

        .dashboard-wrapper {
            max-width: 1100px;
            margin: 60px auto;
            padding: 0 20px;
        }

        .admin-header {
            background: white;
            padding: 20px 28px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 14px 18px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
            text-align: left;
        }

        th {
            color: #4db6ac;
            font-weight: 700;
            background: #fafafa;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f9fefe; }

        .action-links {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-links a {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid;
            transition: background 0.2s, color 0.2s;
        }

        .edit-btn   { color: #3cb1c5; border-color: #3cb1c5; }
        .edit-btn:hover   { background: #3cb1c5; color: white; }

        .delete-btn { color: #e24b4a; border-color: #e24b4a; }
        .delete-btn:hover { background: #e24b4a; color: white; }

        .no-pdf {
            font-size: 11px;
            color: #bbb;
        }

        .logout-btn-header {
            background: transparent;
            border: 1px solid #e24b4a;
            color: #e24b4a;
            padding: 8px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }
        .logout-btn-header:hover { background: #e24b4a; color: white; }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <header class="admin-header">
            <h2 style="margin:0; font-size:1.5rem;">Admin Dashboard</h2>
            <div style="display:flex; gap:10px; align-items:center;">
                <form action="admin_logout.php" method="POST" style="margin:0;">
                    <button type="submit" class="logout-btn-header">Logout</button>
                </form>
            </div>
        </header>

        <section>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="margin:0;">Library Management
                    <span style="font-size:13px; color:#aaa; font-weight:400; margin-left:8px;">
                        <?php echo $totalBooks; ?> books
                    </span>
                </h3>
                <a href="add_book.php"
                   style="text-decoration:none; background:#3cb1c5; color:white;
                          padding:10px 20px; border-radius:8px; font-weight:bold; font-size:14px;">
                    + Add New Book
                </a>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>PDF</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($book['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td>
                            <span style="background:#e0f2f1; color:#00796b;
                                         padding:4px 10px; border-radius:20px; font-size:12px;">
                                <?php echo htmlspecialchars($book['category']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($book['pdf_file']): ?>
                                <span style="color:#26a69a; font-size:13px; font-weight:600;">✅ Uploaded</span>
                            <?php else: ?>
                                <span class="no-pdf">No PDF</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-links">
                                <a href="edit_book.php?id=<?php echo $book['id']; ?>" class="edit-btn">Edit</a>
                                <a href="delete_book.php?id=<?php echo $book['id']; ?>" 
                                   class="delete-btn" 
                                   onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
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