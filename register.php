<?php
require_once 'includes/db.php';
require_once 'includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    if ($role == 'worker') {
        $mobile = $_POST['mobile'];
        $description = $_POST['description'];
        $Profession = $_POST['Profession'];
        $address = $_POST['address'];
        $aadhar_ext = pathinfo($_FILES['aadhar']['name'], PATHINFO_EXTENSION);
        $pan_ext    = pathinfo($_FILES['pan']['name'], PATHINFO_EXTENSION);
        $aadhar_file = 'uploads/' . uniqid('aadhar_') . '.' . $aadhar_ext;
        $pan_file    = 'uploads/' . uniqid('pan_') . '.' . $pan_ext;

        move_uploaded_file($_FILES['aadhar']['tmp_name'], $aadhar_file);
        move_uploaded_file($_FILES['pan']['tmp_name'], $pan_file);

        $stmt = $conn->prepare("INSERT INTO users (role, name, email, password, mobile, description,Profession, address, aadhar_file, pan_file) VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$role, $name, $email, $password, $mobile, $description,$Profession, $address, $aadhar_file, $pan_file]);
    } else {
        $stmt = $conn->prepare("INSERT INTO users (role, name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$role, $name, $email, $password]);
    }

    echo "Registration successful!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ConnectBridge</title>
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

        .register-container {
            display: flex;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }

        .form-section {
            width: 100%;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .image-section {
            width: 100%;
            background: url('assets/images/log.jpg') no-repeat center center/cover;
            display: block;
        }

        h2 {
            color:rgb(3, 3, 3);
            margin-bottom: 15px;
        }

        .welcome-text {
            font-size: 1.2rem;
            color:rgb(0, 0, 0);
            margin-bottom: 30px;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        button {
            width: 100%;
            padding: 10px;
            margin-top: 15px;
            background-color:rgb(164, 87, 160);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background-color:rgb(231, 167, 232);
        }

        .login-link {
            text-align: center;
            margin-top: 15px;
        }

        .login-link a {
            color:rgb(5, 5, 5);
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        #worker-fields {
            display: none;
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

        /* Hide image for worker */
        .worker-form .image-section {
            display: none;
        }

        .worker-form .form-section {
            width: 100%; /* Full width for worker form */
        }
    </style>
</head>
<body>
<div class="navbar">ConnectBridge</div>

    <div class="register-container" id="register-container">
        <!-- Image Section -->
        <div class="image-section" style="background-image:url('assets/images/log.jpg')"></div>

        <!-- Form Section -->
        <div class="form-section">
            <h2>Welcome to ConnectBridge</h2>
            <p class="welcome-text">Create your account to get started</p>

            <form action="register.php" method="post" enctype="multipart/form-data">
                <label for="role">Role:</label>
                <select name="role" id="role" required>
                    <option value="user">User</option>
                    <option value="worker">Worker</option>
                </select>

                <label for="name">Name:</label>
                <input type="text" name="name" required>

                <label for="email">Email:</label>
                <input type="email" name="email" required>

                <label for="password">Password:</label>
                <input type="password" name="password" required>

                <div id="worker-fields">
                    <label for="mobile">Mobile:</label>
                    <input type="text" name="mobile">

                    <label for="description">Description:</label>
                    <textarea name="description"></textarea>

                    <label for="Profession">Profession:</label>
                    <textarea name="Profession"></textarea>

                    <label for="address">Address:</label>
                    <textarea name="address"></textarea>

                    <label for="aadhar">Aadhar Card:</label>
                    <input type="file" name="aadhar">

                    <label for="pan">PAN Card:</label>
                    <input type="file" name="pan">
                </div>

                <button type="submit">Register</button>
            </form>

            <div class="login-link">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </div>
    </div>

    <script>
        const roleSelect = document.getElementById('role');
        const workerFields = document.getElementById('worker-fields');
        const registerContainer = document.getElementById('register-container');

        // Handle role change
        roleSelect.addEventListener('change', function() {
            if (this.value === 'worker') {
                workerFields.style.display = 'block';
                registerContainer.classList.add('worker-form'); // Hides image for worker
            } else {
                workerFields.style.display = 'none';
                registerContainer.classList.remove('worker-form'); // Shows image for user
            }
        });

        // Initial load setup
        if (roleSelect.value === 'worker') {
            workerFields.style.display = 'block';
            registerContainer.classList.add('worker-form'); // Hides image for worker
        }
    </script>
</body>
</html>
