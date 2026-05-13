<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Failed</title>
    <style>
        body{
            font-family: Arial;
            background:#fff1f2;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
        }

        .box{
            background:white;
            padding:40px;
            border-radius:15px;
            text-align:center;
            box-shadow:0 5px 20px rgba(0,0,0,0.1);
        }

        h1{
            color:#dc2626;
        }

        a{
            display:inline-block;
            margin-top:20px;
            padding:10px 20px;
            background:#0ea5e9;
            color:white;
            text-decoration:none;
            border-radius:10px;
        }
    </style>
</head>
<body>

<div class="box">
    <h1>Payment Failed</h1>
    <p>Your transaction was not completed.</p>

    <a href="subscription_plans.php">Try Again</a>
</div>

</body>
</html>