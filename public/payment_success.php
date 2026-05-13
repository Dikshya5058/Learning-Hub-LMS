<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$plan_id = $_SESSION['selected_plan_id'] ?? null;
$tx = $_SESSION['transaction_uuid'] ?? null;

if (!$plan_id || !$tx) {
    header("Location: subscription_plans.php");
    exit();
}

if (!$plan_id || !$tx) {
    die("Invalid session data");
}

/* Prevent duplicate subscription (IMPORTANT) */
$stmt = $pdo->prepare("
    SELECT id FROM user_subscriptions 
WHERE transaction_uuid = ? AND user_id = ?
");
$stmt->execute([$tx, $user_id]);

if ($stmt->rowCount() == 0) {

    /* Get plan details */
    $stmt = $pdo->prepare("SELECT * FROM subscription_plans WHERE id=?");
    $stmt->execute([$plan_id]);
    $plan = $stmt->fetch();

    if (!$plan) {
        die("Plan not found");
    }

    /* Dates */
    $start = date('Y-m-d H:i:s');
    $end = date('Y-m-d H:i:s', strtotime("+{$plan['duration_days']} days"));

    /* INSERT subscription */
    $stmt = $pdo->prepare("
        INSERT INTO user_subscriptions
        (user_id, plan_id, transaction_uuid, payment_status, start_date, end_date)
        VALUES (?, ?, ?, 'COMPLETE', ?, ?)
    ");

    $stmt->execute([$user_id, $plan_id, $tx, $start, $end]);
}

/* OPTIONAL: clear session temp data */
unset($_SESSION['selected_plan_id']);
unset($_SESSION['transaction_uuid']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Success</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-teal: #3cb1c5;
            --brand-dark: #2e8d9e;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --bg: #f8fbfc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top left, rgba(60,177,197,0.12), transparent 40%),
                        radial-gradient(circle at bottom right, rgba(60,177,197,0.08), transparent 45%),
                        var(--bg);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .box {
            background: white;
            padding: 50px;
            border-radius: 26px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0,0,0,0.08);
            max-width: 450px;
            width: 100%;
        }

        h1 {
            color: var(--brand-teal);
            font-size: 28px;
            margin-bottom: 10px;
        }

        p {
            color: var(--text-muted);
            margin-bottom: 25px;
        }

        .btn {
            display: block;
            padding: 12px;
            margin: 10px 0;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 700;
        }

        .primary {
            background: var(--brand-teal);
            color: white;
        }

        .secondary {
            background: #f1f5f9;
            color: var(--text-main);
        }
    </style>
</head>

<body>

<div class="box">

    <h1>Payment Successful!</h1>
    <p>Your subscription is now active.</p>

    <a class="btn primary" href="user_dashboard.php">Go to Dashboard</a>

    <a class="btn secondary" href="generate_invoice.php?tx=<?php echo $tx; ?>">
        Download Payment Bill
    </a>

</div>

</body>
</html>