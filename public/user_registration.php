<?php
require '../config/db.php';

$email_error = $password_error = $confirm_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/";

    // Check email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $email_error = "Email already exists.";
    }

    if (!preg_match($pattern, $password)) {
        $password_error = "Weak password.";
    }

    if ($password !== $confirm_password) {
        $confirm_error = "Passwords do not match.";
    }

    if (empty($email_error) && empty($password_error) && empty($confirm_error)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
        $stmt->execute([$name, $email, $hashed]);

        header("Location: user_login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- Navbar -->
<nav>
    <div class="nav-links">
        <a href="user_login.php" class="nav-login">Log In</a>
        <a href="user_registration.php" class="nav-signup active">Sign Up</a>
    </div>
</nav>

<!-- Main container -->
<main class="signup-container">
    <div class="hero-text">
        <h1>Sign up for <span>LMS</span></h1>
        <p>Your library catalog is available anywhere, anytime.</p>
    </div>

    <div class="registration-box">
        <h3>Account Information:</h3>

        <form action="user_registration.php" method="POST">

            <!-- Name -->
            <div class="input-group">
                <label>Name</label>
                <input type="text" name="name" placeholder="Full Name" required value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
            </div>
            
            <!-- Email -->
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="E-mail" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                <div class="field-error"><?php echo $email_error; ?></div>
            </div>

            <!-- Password -->
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
                <div class="field-error"><?php echo $password_error; ?></div>
            </div>

            <!-- Confirm Password -->
            <div class="input-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <div class="field-error"><?php echo $confirm_error; ?></div>
            </div>

            <input type="submit" value="Sign Up" class="btn-submit">
        </form>

        <div class="login-link">
            Already have an account? <a href="user_login.php">Login here</a>
        </div>
    </div>
</main>

</body>
</html>