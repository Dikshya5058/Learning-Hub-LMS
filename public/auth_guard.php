<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

if (!isset($pdo)) {
    die("Database connection failed in auth_guard.php");
}


if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


try {
    $stmt = $pdo->prepare("
        SELECT *
        FROM user_subscriptions
        WHERE user_id = ?
        AND payment_status = 'COMPLETE'
        AND end_date > NOW()
        ORDER BY id DESC
        LIMIT 1
    ");

    $stmt->execute([$user_id]);
    $sub = $stmt->fetch();

} catch (Exception $e) {
    die("Auth query failed: " . $e->getMessage());
}

if (!$sub) {
    header("Location: subscription_plans.php");
    exit();
}