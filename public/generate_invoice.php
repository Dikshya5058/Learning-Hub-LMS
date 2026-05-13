<?php
session_start();
require '../config/db.php';
require '../vendor/autoload.php';
require 'auth_guard.php';

use Dompdf\Dompdf;

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access");
}

$user_id = $_SESSION['user_id'];
$tx = $_GET['tx'] ?? null;

if (!$tx) {
    die("Missing transaction ID");
}

/* Get subscription */
$stmt = $pdo->prepare("
    SELECT us.*, sp.plan_name, sp.price
    FROM user_subscriptions us
    JOIN subscription_plans sp ON us.plan_id = sp.id
    WHERE us.user_id = ? AND us.transaction_uuid = ?
");

$stmt->execute([$user_id, $tx]);
$data = $stmt->fetch();

if (!$data) {
    die("Invoice not found");
}

$user_stmt = $pdo->prepare("SELECT name, email FROM users WHERE id=?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

$html = "
<h2 style='text-align:center;'>Learning Hub - Payment Invoice</h2>
<hr>

<p><b>Name:</b> {$user['name']}</p>
<p><b>Email:</b> {$user['email']}</p>

<hr>

<p><b>Plan:</b> {$data['plan_name']}</p>
<p><b>Amount Paid:</b> Rs {$data['price']}</p>

<p><b>Transaction ID:</b> {$data['transaction_uuid']}</p>
<p><b>Start Date:</b> {$data['start_date']}</p>
<p><b>End Date:</b> {$data['end_date']}</p>
<p><b>Status:</b> {$data['payment_status']}</p>

<hr>
<p style='text-align:center;'>Thank you for your payment</p>
";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();


$dompdf->stream("invoice_$tx.pdf", ["Attachment" => true]);
?>