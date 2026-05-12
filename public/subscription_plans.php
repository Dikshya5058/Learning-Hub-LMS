<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$stmt = $pdo->query("SELECT * FROM subscription_plans");
$plans = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Subscription Plans</title>

    <style>
        body{
            font-family: Arial;
            background:#f1f5f9;
            margin:0;
            padding:40px;
        }

        .container{
            max-width:1000px;
            margin:auto;
        }

        h1{
            text-align:center;
            margin-bottom:30px;
        }

        .grid{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
            gap:20px;
        }

        .card{
            background:white;
            padding:25px;
            border-radius:15px;
            text-align:center;
            box-shadow:0 5px 15px rgba(0,0,0,0.1);
        }

        .price{
            font-size:28px;
            font-weight:bold;
            color:#0ea5e9;
            margin:10px 0;
        }

        .btn{
            display:inline-block;
            margin-top:15px;
            padding:10px 20px;
            background:#0ea5e9;
            color:white;
            text-decoration:none;
            border-radius:10px;
        }
    </style>
</head>
<body>

<div class="container">

    <h1>Choose Subscription Plan</h1>

    <div class="grid">

        <?php foreach($plans as $plan): ?>

        <div class="card">

            <h2><?php echo htmlspecialchars($plan['plan_name']); ?></h2>

            <div class="price">
                Rs <?php echo $plan['price']; ?>
            </div>

            <p><?php echo $plan['duration_days']; ?> Days Access</p>

            <a class="btn" href="payment.php?plan_id=<?php echo $plan['id']; ?>">
    Subscribe
</a>

        </div>

        <?php endforeach; ?>

    </div>

</div>

</body>
</html>