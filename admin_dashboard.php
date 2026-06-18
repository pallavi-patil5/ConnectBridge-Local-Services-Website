<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/helpers.php';
requireAdmin();

// Verify worker
if (isset($_GET['verify'])) {
    $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ? AND role = 'worker'");
    $stmt->execute([(int)$_GET['verify']]);
    redirect('admin_dashboard.php?tab=workers');
}

// Delete user/worker
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    redirect('admin_dashboard.php?tab=' . ($_GET['tab'] ?? 'workers'));
}

// Resolve complaint
if (isset($_GET['resolve'])) {
    $stmt = $conn->prepare("UPDATE complaints SET status = 'resolved' WHERE id = ?");
    $stmt->execute([(int)$_GET['resolve']]);
    redirect('admin_dashboard.php?tab=complaints');
}

// Delete complaint
if (isset($_GET['del_complaint'])) {
    $conn->prepare("DELETE FROM complaints WHERE id = ?")->execute([(int)$_GET['del_complaint']]);
    redirect('admin_dashboard.php?tab=complaints');
}

$tab = $_GET['tab'] ?? 'stats';

// ── Stats ──────────────────────────────────────────────
$stats = [];
foreach ([
    'total_users'     => "SELECT COUNT(*) FROM users WHERE role='user'",
    'total_workers'   => "SELECT COUNT(*) FROM users WHERE role='worker'",
    'verified'        => "SELECT COUNT(*) FROM users WHERE role='worker' AND is_verified=1",
    'pending'         => "SELECT COUNT(*) FROM users WHERE role='worker' AND is_verified=0",
    'total_bookings'  => "SELECT COUNT(*) FROM bookings",
    'completed_jobs'  => "SELECT COUNT(*) FROM bookings WHERE status='completed'",
    'total_ratings'   => "SELECT COUNT(*) FROM ratings",
    'avg_rating'      => "SELECT ROUND(AVG(rating),1) FROM ratings",
    'total_complaints'=> "SELECT COUNT(*) FROM complaints",
    'open_complaints' => "SELECT COUNT(*) FROM complaints WHERE status='open'",
] as $key => $sql) {
    $stats[$key] = $conn->query($sql)->fetchColumn();
}

// ── Workers ────────────────────────────────────────────
$workers = $conn->query("SELECT * FROM users WHERE role='worker' ORDER BY is_verified ASC, id DESC")->fetchAll();

// ── Users ──────────────────────────────────────────────
$users = $conn->query("SELECT * FROM users WHERE role='user' ORDER BY id DESC")->fetchAll();

// ── Complaints ─────────────────────────────────────────
$complaints = $conn->query("
    SELECT c.*, u.name AS user_name, w.name AS worker_name
    FROM complaints c
    JOIN users u ON c.user_id = u.id
    JOIN users w ON c.worker_id = w.id
    ORDER BY c.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | ConnectBridge</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background:#f5f5f5; padding-top:80px; }
        .navbar { background: linear-gradient(135deg, rgb(60,2,70), rgb(30,0,50)); }
        .navbar a, .navbar-brand { color:white !important; }
        .stat-card { border-radius:12px; border-left:5px solid rgb(164,87,160); }
        .nav-tabs .nav-link.active { color:rgb(120,40,120); font-weight:600; border-bottom:3px solid rgb(120,40,120); }
        .nav-tabs .nav-link { color:#555; }
        .table th { background:rgb(60,2,70); color:white; }
        .badge-pending  { background:#ffc107; color:#000; }
        .badge-verified { background:#198754; }
        .badge-open     { background:#dc3545; }
        .badge-resolved { background:#6c757d; }
        .section-card { border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,.07); }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg fixed-top px-4">
    <a class="navbar-brand fw-bold" href="admin_dashboard.php">ConnectBridge Admin</a>
    <div class="ms-auto">
        <a class="text-white text-decoration-none" href="admin_login.php">Logout</a>
    </div>
</nav>

<div class="container-fluid px-4">

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item"><a class="nav-link <?= $tab==='stats'      ? 'active':'' ?>" href="?tab=stats">📊 Statistics</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab==='workers'    ? 'active':'' ?>" href="?tab=workers">👷 Workers</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab==='users'      ? 'active':'' ?>" href="?tab=users">👤 Users</a></li>
        <li class="nav-item">
            <a class="nav-link <?= $tab==='complaints' ? 'active':'' ?>" href="?tab=complaints">
                🚩 Complaints
                <?php if ($stats['open_complaints'] > 0): ?>
                    <span class="badge badge-open ms-1"><?= $stats['open_complaints'] ?></span>
                <?php endif; ?>
            </a>
        </li>
    </ul>

    <!-- ── STATISTICS ───────────────────────────────── -->
    <?php if ($tab === 'stats'): ?>
    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['Total Users',       $stats['total_users'],      'primary'],
            ['Total Workers',     $stats['total_workers'],    'purple'],
            ['Verified Workers',  $stats['verified'],         'success'],
            ['Pending Approval',  $stats['pending'],          'warning'],
            ['Total Bookings',    $stats['total_bookings'],   'info'],
            ['Completed Jobs',    $stats['completed_jobs'],   'success'],
            ['Avg Rating',        ($stats['avg_rating'] ?: '—') . ' ★', 'warning'],
            ['Total Ratings',     $stats['total_ratings'],    'secondary'],
            ['Open Complaints',   $stats['open_complaints'],  'danger'],
            ['Total Complaints',  $stats['total_complaints'], 'secondary'],
        ];
        foreach ($cards as [$label, $val, $color]):
        $borderColor = $color === 'purple' ? 'rgb(164,87,160)' : '';
        ?>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card stat-card p-3 h-100" <?= $borderColor ? "style='border-left-color:$borderColor'" : '' ?>>
                <small class="text-muted"><?= $label ?></small>
                <h3 class="mt-1 text-<?= $color === 'purple' ? 'dark' : $color ?>"><?= $val ?></h3>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── WORKERS ──────────────────────────────────── -->
    <?php if ($tab === 'workers'): ?>
    <div class="card section-card p-4">
        <h5 class="mb-3">Worker Registrations</h5>
        <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead>
                <tr>
                    <th>Name</th><th>Email</th><th>Mobile</th><th>Profession</th>
                    <th>Aadhar</th><th>PAN</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($workers as $w): ?>
            <tr>
                <td><?= e($w['name']) ?></td>
                <td><?= e($w['email']) ?></td>
                <td><?= e($w['mobile']) ?></td>
                <td><?= e($w['Profession']) ?></td>
                <td>
                    <?= $w['aadhar_file']
                        ? '<a href="'.e($w['aadhar_file']).'" target="_blank">View</a>'
                        : '<span class="text-muted">—</span>' ?>
                </td>
                <td>
                    <?= $w['pan_file']
                        ? '<a href="'.e($w['pan_file']).'" target="_blank">View</a>'
                        : '<span class="text-muted">—</span>' ?>
                </td>
                <td>
                    <?php if ($w['is_verified']): ?>
                        <span class="badge badge-verified">Verified</span>
                    <?php else: ?>
                        <span class="badge badge-pending">Pending</span>
                    <?php endif; ?>
                </td>
                <td class="d-flex gap-2">
                    <?php if (!$w['is_verified']): ?>
                        <a href="?verify=<?= $w['id'] ?>&tab=workers" class="btn btn-sm btn-success">Verify</a>
                    <?php endif; ?>
                    <a href="?delete=<?= $w['id'] ?>&tab=workers" class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this worker?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── USERS ────────────────────────────────────── -->
    <?php if ($tab === 'users'): ?>
    <div class="card section-card p-4">
        <h5 class="mb-3">Registered Users</h5>
        <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Registered</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= e($u['name']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><?= isset($u['created_at']) ? date('d M Y', strtotime($u['created_at'])) : '—' ?></td>
                <td>
                    <a href="?delete=<?= $u['id'] ?>&tab=users" class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this user?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── COMPLAINTS ───────────────────────────────── -->
    <?php if ($tab === 'complaints'): ?>
    <div class="card section-card p-4">
        <h5 class="mb-3">Complaints</h5>
        <?php if (!$complaints): ?>
            <p class="text-muted">No complaints filed.</p>
        <?php else: ?>
        <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead>
                <tr><th>User</th><th>Against Worker</th><th>Message</th><th>Date</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($complaints as $c): ?>
            <tr>
                <td><?= e($c['user_name']) ?></td>
                <td><?= e($c['worker_name']) ?></td>
                <td><?= e($c['message']) ?></td>
                <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                <td>
                    <span class="badge <?= $c['status']==='open' ? 'badge-open' : 'badge-resolved' ?>">
                        <?= e($c['status']) ?>
                    </span>
                </td>
                <td class="d-flex gap-2">
                    <?php if ($c['status'] === 'open'): ?>
                        <a href="?resolve=<?= $c['id'] ?>" class="btn btn-sm btn-success">Resolve</a>
                    <?php endif; ?>
                    <a href="?del_complaint=<?= $c['id'] ?>" class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete complaint?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
