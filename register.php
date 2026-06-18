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
        $aadhar_file = 'uploads/' . basename($_FILES['aadhar']['name']);
        $pan_file = 'uploads/' . basename($_FILES['pan']['name']);

        move_uploaded_file($_FILES['aadhar']['tmp_name'], $aadhar_file);
        move_uploaded_file($_FILES['pan']['tmp_name'], $pan_file);

        $stmt = $conn->prepare("INSERT INTO users (role, name, email, password, mobile, description,Profession, address, aadhar_file, pan_file) VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$role, $name, $email, $password, $mobile, $description, $Profession, $address, $aadhar_file, $pan_file]);
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
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

</head>
<body>


<div class="auth-page">
    <div class="auth-shell" id="register-container">
        <div class="auth-image"></div>

        <div class="auth-form">
            <h2 class="auth-title">Welcome to ConnectBridge</h2>
            <p class="auth-sub">Create your account to get started</p>

            <form action="register.php" method="post" enctype="multipart/form-data" id="register-form">
                <label for="role">Role</label>
                <select name="role" id="role" required>
                    <option value="user">User</option>
                    <option value="worker">Worker</option>
                </select>

                <label for="name">Name</label>
                <input type="text" name="name" required>

                <label for="email">Email</label>
                <input type="email" name="email" required>

                <label for="password">Password</label>
                <input type="password" name="password" required>

                <div id="worker-fields">
                    <label for="mobile">Mobile</label>
                    <input type="text" name="mobile">

                    <label for="description">Description</label>
                    <textarea name="description"></textarea>

                    <label for="Profession">Profession</label>
                    <textarea name="Profession"></textarea>

                    <label for="address">Address</label>
                    <textarea name="address"></textarea>

                    <label for="aadhar">Aadhar Card</label>
                    <input type="file" name="aadhar">

                    <label for="pan">PAN Card</label>
                    <input type="file" name="pan">
                </div>

                <div class="auth-actions">
                    <button class="auth-btn" type="submit">Register</button>
                </div>
            </form>

            <div class="auth-links">
                <a href="login.php" class="auth-pill">Already have an account? Login</a>
            </div>
        </div>
    </div>
</div>

<script>
    const roleSelect = document.getElementById('role');
    const workerFields = document.getElementById('worker-fields');

    function syncWorkerFields() {
        if (roleSelect.value === 'worker') {
            workerFields.style.display = 'block';
        } else {
            workerFields.style.display = 'none';
        }
    }

    roleSelect.addEventListener('change', syncWorkerFields);
    // Initial load setup
    syncWorkerFields();
</script>
</body>
</html>

