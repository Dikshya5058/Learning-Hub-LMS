<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT * FROM user_subscriptions
    WHERE user_id = ?
    AND payment_status = 'COMPLETE'
    AND end_date > NOW()
    ORDER BY id DESC
    LIMIT 1
");
$stmt->execute([$user_id]);
$sub = $stmt->fetch();

if ($sub) {
    header("Location: user_dashboard.php");
    exit();
} else {
    header("Location: subscription_plans.php");
    exit();
}