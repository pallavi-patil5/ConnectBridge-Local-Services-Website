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
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'worker') {
                header("Location: worker_dashboard.php");
            } else {
                header("Location: index.php");
            }
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
    <style>
        body {
            display: flex;
            height: 100vh;
            background-color: #f0f4f8;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .navbar {
            background: linear-gradient(135deg, rgb(120, 40, 120), rgb(50, 10, 90));
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        .login-container {
            display: flex;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        .form-section {
            width: 50%;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .image-section {
            width: 50%;
            background: url('log.jpg') no-repeat center center/cover;
        }
        h2 {
            color:rgb(0, 0, 0);
            margin-bottom: 15px;
        }
        .welcome-text {
            font-size: 1.2rem;
            color:rgb(0, 0, 0);
            margin-bottom: 30px;
        }
        input, select, button {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        button {
            background-color:rgb(164, 87, 160);
            color: #fff;
            border: none;
            cursor: pointer;
            margin-top: 20px;
        }
        button:hover {
            background-color:rgb(231, 167, 232);
        }
        .register-link, .admin-login {
            text-align: center;
            margin-top: 15px;
        }
        .register-link a, .admin-login a {
            color:rgb(0, 0, 0);
            text-decoration: none;
        }
        .register-link a:hover, .admin-login a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="navbar">ConnectBridge</div>
    <div class="login-container">
        <div class="image-section" style="background-image:url('assets/images/log.jpg')"></div>
        <!-- Form Section -->
        <div class="form-section">
            <h2>Welcome Back!</h2>
            <p class="welcome-text">Login to continue your journey</p>

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

                <button type="submit">Login</button>
            </form>

            <div class="register-link">
                Don't have an account? <a href="register.php">Register</a>
            </div>
            <div class="admin-login">
                <a href="admin_login.php"><button>Admin Login</button></a>
            </div>
        </div>
    </div>

    <script>
        <?php if (!empty($error_message)): ?>
            alert("<?php echo $error_message; ?>");
        <?php endif; ?>
    </script>
</body>
</html>
