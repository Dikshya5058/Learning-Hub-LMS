
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
$active = $stmt->fetch();

if ($active) {
    header("Location: user_dashboard.php");
    exit();
}


$stmt = $pdo->query("SELECT * FROM subscription_plans");
$plans = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Subscription Plans</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-teal: #3cb1c5;
            --brand-dark: #2e8d9e;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --accent-soft: rgba(60, 177, 197, 0.08);
            --bg: #f8fbfc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background: radial-gradient(circle at top left, rgba(60,177,197,0.12), transparent 40%),
                        radial-gradient(circle at bottom right, rgba(60,177,197,0.08), transparent 45%),
                        var(--bg);
            min-height: 100vh;
            padding: 70px 20px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        .header {
            text-align: center;
            margin-bottom: 120px;
        }

        .header h1 {
            font-size: 36px;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -1px;
        }

        .header p {
            color: var(--text-muted);
            font-size: 15px;
            margin-top: 10px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 28px;
        }

        .card {
            background: white;
            border-radius: 26px;
            padding: 30px;
            border: 1px solid #eef2f7;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-10px);
            border-color: var(--brand-teal);
            box-shadow: 0 25px 50px rgba(60, 177, 197, 0.18);
        }

        .card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--brand-teal), #8ce1ef);
        }

        .badge {
            display: inline-block;
            background: var(--accent-soft);
            color: var(--brand-dark);
            padding: 6px 14px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 999px;
            margin-bottom: 18px;
        }

        .card h2 {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 10px;
        }

        .price {
            font-size: 34px;
            font-weight: 900;
            color: var(--brand-teal);
            margin: 15px 0 8px;
        }

        .duration {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 22px;
        }

        .features p {
            font-size: 13px;
            color: var(--text-muted);
            margin: 6px 0;
        }

        .features p::before {
            content: "✔";
            color: var(--brand-teal);
            font-weight: 800;
            margin-right: 6px;
        }

        .btn {
            display: inline-block;
            width: 100%;
            text-align: center;
            padding: 13px;
            border-radius: 14px;
            background: var(--brand-teal);
            color: white;
            text-decoration: none;
            font-weight: 800;
            transition: 0.25s ease;
        }

        .btn:hover {
            background: var(--brand-dark);
            transform: scale(1.02);
        }

        .glow {
            position: fixed;
            width: 350px;
            height: 350px;
            background: rgba(60, 177, 197, 0.15);
            filter: blur(120px);
            top: -120px;
            left: -120px;
            z-index: -1;
        }

        .glow2 {
            position: fixed;
            width: 300px;
            height: 300px;
            background: rgba(60, 177, 197, 0.1);
            filter: blur(120px);
            bottom: -120px;
            right: -120px;
            z-index: -1;
        }
    </style>
</head>

<body>

<div class="glow"></div>
<div class="glow2"></div>

<div class="container">

    <div class="header">
        <h1>Choose Your Subscription</h1>
        <p>Unlock full access to books, learning materials, and premium content</p>
    </div>

    <div class="grid">

        <?php foreach($plans as $plan): ?>
        <div class="card">

            <div class="badge">
                <?php echo $plan['duration_days'] >= 30 ? 'Best Value' : 'Popular'; ?>
            </div>

            <h2><?php echo htmlspecialchars($plan['plan_name']); ?></h2>

            <div class="price">Rs <?php echo $plan['price']; ?></div>

            <div class="duration">
                <?php echo $plan['duration_days']; ?> Days Full Access
            </div>

            <div class="features">
                <p>Full Book Library Access</p>
                <p>Downloadable Resources</p>
                <p>Premium Learning Content</p>
            </div>

            <a class="btn" href="payment.php?plan_id=<?php echo $plan['id']; ?>">
                Subscribe Now
            </a>

        </div>
        <?php endforeach; ?>

    </div>

</div>

</body>
</html>