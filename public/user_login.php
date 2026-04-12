<?php
session_start();
require '../config/db.php';

$login_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: user_dashboard.php");
            exit();
        } else {
            $login_error = "Invalid email or password.";
        }
    } else {
        $login_error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<nav>
    <div class="nav-links">
        <a href="user_login.php" class="nav-login active">Log in</a>
        <a href="user_registration.php" class="nav-signup">Sign Up</a>
    </div>
</nav>

<main class="signup-container">

    <div class="hero-text">
        <h1>Welcome back to <span>LMS</span></h1>
        <p>Login to continue your learning journey.</p>
    </div>

    <div class="registration-box">
        <h3>Login</h3>

        <form action="user_login.php" method="POST">

            <!-- Email -->
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="E-mail" required
                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            </div>

            <!-- Password -->
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Password" required>
                <!-- Login error appears here -->
                <div class="field-error"><?php echo $login_error; ?></div>
            </div>

            <input type="submit" value="Login" class="btn-submit">
        </form>

        <div class="login-link">
            Don't have an account? <a href="user_registration.php">Sign up</a>
        </div>
    </div>

</main>

</body>
</html>