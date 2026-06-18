<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/helpers.php';
requireLogin();

if ($_SESSION['role'] !== 'user') redirect('worker_dashboard.php');

$worker_id = $_GET['id'] ?? null;
if (!$worker_id) redirect('index.php');

// Fetch worker
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'worker' AND is_verified = 1");
$stmt->execute([$worker_id]);
$worker = $stmt->fetch();
if (!$worker) redirect('index.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date    = $_POST['booking_date'] ?? '';
    $time    = $_POST['booking_time'] ?? '';
    $note    = trim($_POST['note'] ?? '');
    $user_id = $_SESSION['user_id'];

    if (!$date || !$time) {
        $error = 'Please select a date and time.';
    } elseif (strtotime($date) < strtotime(date('Y-m-d'))) {
        $error = 'Booking date cannot be in the past.';
    } else {
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, worker_id, booking_date, booking_time, note) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $worker_id, $date, $time, $note]);
        $success = 'Booking confirmed!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Service | ConnectBridge</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #f5f5f5; padding-top: 80px; }
        .navbar { background: linear-gradient(135deg, rgb(120,40,120), rgb(50,10,90)); }
        .navbar a, .navbar-brand { color: white !important; }
        .card { border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,.08); }
        .btn-primary { background: rgb(164,87,160); border-color: rgb(164,87,160); }
        .btn-primary:hover { background: rgb(120,40,120); border-color: rgb(120,40,120); }
        .worker-info { border-left: 4px solid rgb(164,87,160); padding-left: 15px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg fixed-top px-4">
    <a class="navbar-brand fw-bold" href="index.php">ConnectBridge</a>
    <div class="ms-auto">
        <a class="nav-link d-inline text-white" href="booking_history.php">My Bookings</a>
        <a class="nav-link d-inline text-white" href="login.php">Logout</a>
    </div>
</nav>

<div class="container" style="max-width:600px">
    <div class="card p-4">
        <div class="worker-info mb-4">
            <h5 class="mb-1"><?= e($worker['name']) ?></h5>
            <small class="text-muted"><?= e($worker['Profession']) ?> &mdash; <?= e($worker['address']) ?></small>
        </div>

        <h4 class="mb-3">Book a Service</h4>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?>
                <a href="booking_history.php" class="alert-link">View your bookings</a>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Date</label>
                <input type="date" name="booking_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Time</label>
                <input type="time" name="booking_time" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Note <small class="text-muted">(optional)</small></label>
                <textarea name="note" class="form-control" rows="3" placeholder="Describe the work needed..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Confirm Booking</button>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
