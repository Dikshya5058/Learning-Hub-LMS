<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: index.html");
    exit();
}

$success = "";
$error = "";

if (isset($_POST['add_book'])) {
    $title    = trim($_POST['title']);
    $author   = trim($_POST['author']);
    $category = trim($_POST['category']);
    $content  = trim($_POST['content']);
    $pdf_file = null;

    // Handle PDF upload
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
        $file     = $_FILES['pdf_file'];
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $maxSize  = 20 * 1024 * 1024; // 20 MB

        if ($ext !== 'pdf') {
            $error = "Only PDF files are allowed.";
        } elseif ($file['size'] > $maxSize) {
            $error = "PDF file must be under 20MB.";
        } else {
            $uploadDir = __DIR__ . '/uploads/books/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            // Unique filename to avoid collisions
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
            $dest     = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $pdf_file = 'uploads/books/' . $filename;
            } else {
                $error = "Failed to upload PDF. Check folder permissions.";
            }
        }
    }

    if (!$error) {
        if ($title && $author && $category) {
            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO books (title, author, category, description, pdf_file)
                     VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->execute([$title, $author, $category, $content, $pdf_file]);
$_SESSION['success_msg'] = "Book added successfully!";
header("Location: dashboard.php");
exit();
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        } else {
            $error = "Title, Author and Category are required.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS | Add New Book</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 40px 16px;
        }

        .form-card {
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.07);
            width: 100%;
            max-width: 500px;
        }

        h2 { margin: 0 0 6px 0; font-size: 1.5rem; }

        .subtitle {
            color: #888;
            font-size: 14px;
            margin-top: 0;
            margin-bottom: 24px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #555;
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 18px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            font-family: inherit;
            font-size: 14px;
            transition: border 0.2s;
            outline: none;
        }

        input[type="text"]:focus,
        textarea:focus {
            border-color: #3cb1c5;
        }

        /* PDF Upload Area */
        .pdf-upload-area {
            border: 2px dashed #c8e6e9;
            border-radius: 10px;
            padding: 24px;
            text-align: center;
            background: #f0fafc;
            margin-bottom: 18px;
            cursor: pointer;
            transition: border-color 0.2s, background 0.2s;
            position: relative;
        }

        .pdf-upload-area:hover {
            border-color: #3cb1c5;
            background: #e0f7fa;
        }

        .pdf-upload-area .upload-icon {
            font-size: 2.2rem;
            margin-bottom: 8px;
        }

        .pdf-upload-area p {
            margin: 4px 0;
            color: #555;
            font-size: 13px;
        }

        .pdf-upload-area .hint {
            font-size: 11px;
            color: #aaa;
            margin-top: 6px;
        }

        .pdf-upload-area input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
        }

        #pdf-file-name {
            font-size: 12px;
            color: #3cb1c5;
            font-weight: 600;
            margin-top: 8px;
            display: none;
        }

        .section-divider {
            border: none;
            border-top: 1px solid #f0f0f0;
            margin: 8px 0 22px 0;
        }

        .section-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #3cb1c5;
            margin-bottom: 14px;
        }

        button[type="submit"] {
            width: 100%;
            padding: 14px;
            background-color: #3cb1c5;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 4px;
            transition: background 0.2s;
        }

        button[type="submit"]:hover { background: #26a69a; }

        .cancel-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            text-decoration: none;
            color: #888;
            font-size: 14px;
            font-weight: 600;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
        }
        .alert-success { background: #e0f7f4; color: #26a69a; }
        .alert-error   { background: #fdecea; color: #e24b4a; }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-card">
            <h2>Add New Book</h2>
            <p class="subtitle">Enter the book details to update the library catalog.</p>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <!-- BOOK INFO -->
                <div class="section-label">📖 Book Information</div>

                <label>Book Title</label>
                <input type="text" name="title" placeholder="e.g. The Great Gatsby" required>

                <label>Author</label>
                <input type="text" name="author" placeholder="e.g. F. Scott Fitzgerald" required>

                <label>Category</label>
                <input type="text" name="category" placeholder="e.g. Classic Literature" required>

                <hr class="section-divider">

                <!-- PDF UPLOAD -->
                <div class="section-label">📄 PDF Book File</div>

                <div class="pdf-upload-area" id="uploadArea">
                    <div class="upload-icon">📂</div>
                    <p><strong>Click to upload PDF</strong> or drag & drop</p>
                    <p class="hint">PDF only · Max 20MB</p>
                    <p id="pdf-file-name"></p>
                    <input
                        type="file"
                        name="pdf_file"
                        id="pdfInput"
                        accept=".pdf"
                        onchange="showFileName(this)"
                    >
                </div>

                <hr class="section-divider">

                <!-- DESCRIPTION -->
                <div class="section-label">📝 Description</div>

                <label>Description / Content</label>
                <textarea name="content" rows="4" placeholder="Briefly describe the book..."></textarea>

                <button type="submit" name="add_book">Save to Library</button>

                <a href="dashboard.php" class="cancel-link">Cancel and Go Back</a>
            </form>
        </div>
    </div>

    <script>
        function showFileName(input) {
            const label = document.getElementById('pdf-file-name');
            const area  = document.getElementById('uploadArea');
            if (input.files && input.files[0]) {
                label.textContent = '✅ ' + input.files[0].name;
                label.style.display = 'block';
                area.style.borderColor = '#26a69a';
                area.style.background  = '#e0f7f4';
            }
        }

        // Drag-and-drop highlight
        const area = document.getElementById('uploadArea');
        area.addEventListener('dragover',  e => { e.preventDefault(); area.style.borderColor = '#3cb1c5'; });
        area.addEventListener('dragleave', ()  => { area.style.borderColor = '#c8e6e9'; });
        area.addEventListener('drop',      e  => {
            e.preventDefault();
            const input = document.getElementById('pdfInput');
            input.files = e.dataTransfer.files;
            showFileName(input);
        });
    </script>
</body>
</html>