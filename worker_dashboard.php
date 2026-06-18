<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/helpers.php';
requireWorker();

$user_id = $_SESSION['user_id'];

// Fetch worker
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$worker = $stmt->fetch();

if (!$worker) { redirect('login.php'); }

// Pending verification page
if ($worker['is_verified'] != 1) { ?>
<!DOCTYPE html><html><head><title>Under Review</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head><body class="d-flex justify-content-center align-items-center" style="height:100vh;background:#f0f0f0">
<div class="card p-5 text-center shadow">
    <h4>Application Under Review</h4>
    <p class="text-muted">Your profile is being reviewed by the admin. Please wait for verification.</p>
    <p class="text-danger"><strong>If not approved within 48hrs, please create a new profile.</strong></p>
    <a href="login.php" class="btn btn-secondary mt-2">Back to Login</a>
</div>
</body></html>
<?php exit(); }

// Handle booking accept/reject
if (isset($_GET['action'], $_GET['bid'])) {
    $action = in_array($_GET['action'], ['accepted','rejected']) ? $_GET['action'] : null;
    if ($action) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ? AND worker_id = ? AND status = 'pending'");
        $stmt->execute([$action, (int)$_GET['bid'], $user_id]);
    }
    redirect('worker_dashboard.php');
}

// Handle mark complete
if (isset($_GET['complete'])) {
    $stmt = $conn->prepare("UPDATE bookings SET status = 'completed' WHERE id = ? AND worker_id = ? AND status = 'accepted'");
    $stmt->execute([(int)$_GET['complete'], $user_id]);
    redirect('worker_dashboard.php');
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $stmt = $conn->prepare("UPDATE users SET description=?, address=?, mobile=? WHERE id=?");
    $stmt->execute([trim($_POST['description']), trim($_POST['address']), trim($_POST['mobile']), $user_id]);
    redirect('worker_dashboard.php');
}

// Handle availability toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['availability'])) {
    $avail = $_POST['availability'] === '1' ? 1 : 0;
    $stmt  = $conn->prepare("UPDATE users SET is_available=? WHERE id=?");
    $stmt->execute([$avail, $user_id]);
    redirect('worker_dashboard.php');
}

// Handle skill add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['skill'])) {
    $skill = trim($_POST['skill']);
    if ($skill) {
        $stmt = $conn->prepare("INSERT IGNORE INTO skills (worker_id, skill) VALUES (?,?)");
        $stmt->execute([$user_id, $skill]);
    }
    redirect('worker_dashboard.php');
}

// Handle skill delete
if (isset($_GET['del_skill'])) {
    $stmt = $conn->prepare("DELETE FROM skills WHERE id=? AND worker_id=?");
    $stmt->execute([(int)$_GET['del_skill'], $user_id]);
    redirect('worker_dashboard.php');
}

// Fetch pending bookings
$stmt = $conn->prepare("SELECT b.*, u.name AS user_name, u.mobile AS user_mobile FROM bookings b JOIN users u ON b.user_id=u.id WHERE b.worker_id=? AND b.status='pending' ORDER BY b.booking_date");
$stmt->execute([$user_id]);
$pending = $stmt->fetchAll();

// Fetch upcoming accepted bookings
$stmt = $conn->prepare("SELECT b.*, u.name AS user_name FROM bookings b JOIN users u ON b.user_id=u.id WHERE b.worker_id=? AND b.status='accepted' ORDER BY b.booking_date");
$stmt->execute([$user_id]);
$accepted = $stmt->fetchAll();

// Earnings — sum of completed bookings (counted as fixed ₹500 placeholder)
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM bookings WHERE worker_id=? AND status='completed'");
$stmt->execute([$user_id]);
$completed_count = $stmt->fetch()['total'];

// Avg rating
$stmt = $conn->prepare("SELECT ROUND(AVG(rating),1) AS avg_rating, COUNT(*) AS total FROM ratings WHERE worker_id=?");
$stmt->execute([$user_id]);
$rating_info = $stmt->fetch();

// Skills
$stmt = $conn->prepare("SELECT * FROM skills WHERE worker_id=?");
$stmt->execute([$user_id]);
$skills = $stmt->fetchAll();

// Re-fetch worker for is_available
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user_id]);
$worker = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Dashboard | ConnectBridge</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background:#f5f5f5; padding-top:80px; }
        .navbar { background: linear-gradient(135deg, rgb(120,40,120), rgb(50,10,90)); }
        .navbar a, .navbar-brand { color:white !important; }
        .stat-card { border-radius:12px; border-left:5px solid rgb(164,87,160); }
        .btn-purple { background:rgb(164,87,160); color:#fff; border:none; }
        .btn-purple:hover { background:rgb(120,40,120); color:#fff; }
        .section-card { border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,.07); margin-bottom:24px; }
        .badge-pending  { background:#ffc107; color:#000; }
        .badge-accepted { background:#198754; }
        .stars { color:#ffc107; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg fixed-top px-4">
    <a class="navbar-brand fw-bold" href="worker_dashboard.php">ConnectBridge</a>
    <div class="ms-auto d-flex gap-3">
        <a class="text-white text-decoration-none" href="notifications.php">Notifications</a>
        <a class="text-white text-decoration-none" href="login.php">Logout</a>
    </div>
</nav>

<div class="container">

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card p-3">
                <small class="text-muted">Pending Requests</small>
                <h3><?= count($pending) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card p-3">
                <small class="text-muted">Upcoming Jobs</small>
                <h3><?= count($accepted) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card p-3">
                <small class="text-muted">Jobs Completed</small>
                <h3><?= $completed_count ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card p-3">
                <small class="text-muted">Avg Rating</small>
                <h3 class="stars"><?= $rating_info['avg_rating'] ?? '—' ?> <small style="font-size:.9rem">★ (<?= $rating_info['total'] ?>)</small></h3>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEFT COLUMN -->
        <div class="col-lg-4">

            <!-- Profile -->
            <div class="card section-card p-4">
                <h5 class="mb-3">Profile</h5>
                <p class="mb-1"><strong><?= e($worker['name']) ?></strong></p>
                <p class="text-muted mb-1"><?= e($worker['Profession']) ?></p>
                <p class="mb-1"><small><?= e($worker['email']) ?></small></p>
                <hr>
                <form method="POST">
                    <input type="hidden" name="update_profile" value="1">
                    <div class="mb-2">
                        <label class="form-label small">Mobile</label>
                        <input type="text" name="mobile" class="form-control form-control-sm" value="<?= e($worker['mobile']) ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Address</label>
                        <input type="text" name="address" class="form-control form-control-sm" value="<?= e($worker['address']) ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Description</label>
                        <textarea name="description" class="form-control form-control-sm" rows="3"><?= e($worker['description']) ?></textarea>
                    </div>
                    <button class="btn btn-purple btn-sm w-100">Update Profile</button>
                </form>
            </div>

            <!-- Availability -->
            <div class="card section-card p-4">
                <h5 class="mb-3">Availability</h5>
                <p class="mb-2">Status: <strong><?= $worker['is_available'] ? '<span class="text-success">Available</span>' : '<span class="text-danger">Unavailable</span>' ?></strong></p>
                <form method="POST">
                    <input type="hidden" name="availability" value="<?= $worker['is_available'] ? '0' : '1' ?>">
                    <button class="btn btn-sm <?= $worker['is_available'] ? 'btn-outline-danger' : 'btn-outline-success' ?> w-100">
                        <?= $worker['is_available'] ? 'Set Unavailable' : 'Set Available' ?>
                    </button>
                </form>
            </div>

            <!-- Skills -->
            <div class="card section-card p-4">
                <h5 class="mb-3">Skills</h5>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php foreach ($skills as $s): ?>
                        <span class="badge bg-secondary">
                            <?= e($s['skill']) ?>
                            <a href="worker_dashboard.php?del_skill=<?= $s['id'] ?>" class="text-white ms-1 text-decoration-none" onclick="return confirm('Remove skill?')">&times;</a>
                        </span>
                    <?php endforeach; ?>
                    <?php if (!$skills): ?><small class="text-muted">No skills added yet.</small><?php endif; ?>
                </div>
                <form method="POST" class="d-flex gap-2">
                    <input type="text" name="skill" class="form-control form-control-sm" placeholder="Add skill..." required>
                    <button class="btn btn-purple btn-sm">Add</button>
                </form>
            </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="col-lg-8">

            <!-- Pending Requests -->
            <div class="card section-card p-4">
                <h5 class="mb-3">Booking Requests <span class="badge badge-pending"><?= count($pending) ?></span></h5>
                <?php if (!$pending): ?>
                    <p class="text-muted">No pending requests.</p>
                <?php endif; ?>
                <?php foreach ($pending as $b): ?>
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong><?= e($b['user_name']) ?></strong>
                            <small class="text-muted d-block"><?= e($b['user_mobile']) ?></small>
                            <small><?= date('d M Y', strtotime($b['booking_date'])) ?> at <?= date('h:i A', strtotime($b['booking_time'])) ?></small>
                            <?php if ($b['note']): ?><p class="mb-0 mt-1 small text-muted"><?= e($b['note']) ?></p><?php endif; ?>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <a href="worker_dashboard.php?action=accepted&bid=<?= $b['id'] ?>" class="btn btn-sm btn-success">Accept</a>
                            <a href="worker_dashboard.php?action=rejected&bid=<?= $b['id'] ?>" class="btn btn-sm btn-danger">Reject</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Upcoming Jobs -->
            <div class="card section-card p-4">
                <h5 class="mb-3">Upcoming Jobs <span class="badge badge-accepted"><?= count($accepted) ?></span></h5>
                <?php if (!$accepted): ?>
                    <p class="text-muted">No upcoming jobs.</p>
                <?php endif; ?>
                <?php foreach ($accepted as $b): ?>
                <div class="border rounded p-3 mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= e($b['user_name']) ?></strong>
                        <small class="text-muted d-block"><?= date('d M Y', strtotime($b['booking_date'])) ?> at <?= date('h:i A', strtotime($b['booking_time'])) ?></small>
                        <?php if ($b['note']): ?><small class="text-muted"><?= e($b['note']) ?></small><?php endif; ?>
                    </div>
                    <a href="worker_dashboard.php?complete=<?= $b['id'] ?>"
                       class="btn btn-sm btn-outline-primary"
                       onclick="return confirm('Mark as completed?')">Mark Complete</a>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
