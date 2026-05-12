<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$plan_id = $_GET['plan_id'] ?? null;

if (!$plan_id) die("Invalid plan");

$_SESSION['selected_plan_id'] = $plan_id;

$stmt = $pdo->prepare("SELECT * FROM subscription_plans WHERE id = ?");
$stmt->execute([$plan_id]);
$plan = $stmt->fetch();

if (!$plan) die("Plan not found");

$amount = $plan['price'];
$total_amount = $amount;

$transaction_uuid = uniqid("TXN_");

$product_code = "EPAYTEST";
$secret_key = "8gBm/:&EnhH.1/q";

// IMPORTANT: must be reachable URL (NOT wrong folder)
$success_url = "http://localhost/Learning-Hub-LMS/public/payment_success.php";
$failure_url = "http://localhost/Learning-Hub-LMS/public/payment_failed.php";

$signed_field_names = "total_amount,transaction_uuid,product_code";

$message = "total_amount={$total_amount},transaction_uuid={$transaction_uuid},product_code={$product_code}";
$signature = base64_encode(hash_hmac('sha256', $message, $secret_key, true));
?>

<form id="esewaForm" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">

    <input type="hidden" name="amount" value="<?= $amount ?>">
    <input type="hidden" name="tax_amount" value="0">
    <input type="hidden" name="total_amount" value="<?= $total_amount ?>">

    <input type="hidden" name="transaction_uuid" value="<?= $transaction_uuid ?>">
    <input type="hidden" name="product_code" value="<?= $product_code ?>">

    <input type="hidden" name="product_service_charge" value="0">
    <input type="hidden" name="product_delivery_charge" value="0">

    <input type="hidden" name="success_url" value="<?= $success_url ?>">
    <input type="hidden" name="failure_url" value="<?= $failure_url ?>">

    <input type="hidden" name="signed_field_names" value="<?= $signed_field_names ?>">
    <input type="hidden" name="signature" value="<?= $signature ?>">

</form>

<script>
document.getElementById("esewaForm").submit();
</script>