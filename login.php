<?php
require_once 'includes/db.php';
require_once 'includes/helpers.php';

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $selected_role = $_POST['role'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['role'] === $selected_role) {
            session_start();
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'worker') {
                $_SESSION['user_id'] = $user['id'];
                header("Location: worker_dashboard.php");
                exit();
            }

            // Treat everything else (except worker/admin) as a user
            if ($user['role'] === 'admin') {
                // not expected on this login page, but keep safe
                $_SESSION['admin_id'] = $user['id'];
                header("Location: admin_dashboard.php");
                exit();
            }

            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php");
            exit();
        } else {
            $error_message = "You are not authorized to login as a $selected_role.";
        }
    } else {
        $error_message = "Invalid credentials! or your profile is deleted by admin";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ConnectBridge</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

</head>

<body>

    <div class="auth-page">
        <div class="auth-shell">
            <div class="auth-image"></div>
            <div class="auth-form">
            <h2 class="auth-title">Welcome Back!</h2>
            <p class="auth-sub">Login to continue your journey</p>

            <div class="auth-actions">
                <form action="login.php" method="post">

                    <label for="email">Email:</label>

                <input type="email" name="email" required>

                <label for="password">Password:</label>
                <input type="password" name="password" required>

                <label for="role">Role:</label>
                <select name="role" required>
                    <option value="user">User</option>
                    <option value="worker">Worker</option>
                </select>

                <button class="auth-btn" type="submit">Login</button>
            </form>


            <div class="auth-links">
                <a href="register.php" class="auth-pill">Don’t have an account? Register</a>
                <a href="admin_login.php" class="auth-pill">Admin Login</a>
            </div>

            <?php if (!empty($error_message)): ?>
                <script>alert("<?php echo $error_message; ?>");</script>
            <?php endif; ?>
        </div>
        </div>
    </div>
</body>
</html>
