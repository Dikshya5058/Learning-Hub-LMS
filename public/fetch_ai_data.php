<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php'; // Uses your existing config

if (!isset($_GET['book_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No ID']);
    exit;
}

$book_id = (int)$_GET['book_id'];

// Fetch from our new AI table using PDO
$stmt = $pdo->prepare("SELECT * FROM smart_assistant_data WHERE book_id = ?");
$stmt->execute([$book_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($data) {
    echo json_encode([
        'status' => 'success',
        'summary' => $data['summary'],
        'key_points' => array_map('trim', explode(';', $data['key_points'])),
        'recommendations' => array_map('trim', explode(',', $data['recommended_books'])),
        'faqs' => json_decode($data['faq_data'], true)
    ]);
} else {
    echo json_encode([
        'status' => 'fallback',
        'summary' => 'Our AI is currently analyzing this book. Please check back later!',
        'key_points' => ['Analysis in progress', 'Contextual extraction pending'],
        'recommendations' => ['Similar Trending Books'],
        'faqs' => [['q' => 'When will this be ready?', 'a' => 'Usually within 24 hours.']]
    ]);
}