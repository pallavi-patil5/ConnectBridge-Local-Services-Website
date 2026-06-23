<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/helpers.php';
requireLogin();

if ($_SESSION['role'] !== 'user') redirect('worker_dashboard.php');

$user_id = $_SESSION['user_id'];

// Cancel booking
if (isset($_GET['cancel'])) {
    $bid = (int)$_GET['cancel'];
    $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->execute([$bid, $user_id]);
    redirect('booking_history.php');
}

// Submit rating
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    $bid       = (int)$_POST['booking_id'];
    $worker_id = (int)$_POST['worker_id'];
    $rating    = (int)$_POST['rating'];
    $review    = trim($_POST['review'] ?? '');

    // Verify booking belongs to user and is completed
    $stmt = $conn->prepare("SELECT id FROM bookings WHERE id = ? AND user_id = ? AND status = 'completed'");
    $stmt->execute([$bid, $user_id]);
    if ($stmt->fetch()) {
        // Upsert rating
        $stmt = $conn->prepare("INSERT INTO ratings (booking_id, user_id, worker_id, rating, review)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE rating = VALUES(rating), review = VALUES(review)");
        $stmt->execute([$bid, $user_id, $worker_id, $rating, $review]);
    }
    redirect('booking_history.php');
}

// Fetch bookings with worker name
$stmt = $conn->prepare("
    SELECT b.*, u.name AS worker_name, u.Profession,
           r.rating AS my_rating, r.review AS my_review
    FROM bookings b
    JOIN users u ON b.worker_id = u.id
    LEFT JOIN ratings r ON r.booking_id = b.id AND r.user_id = ?
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$user_id, $user_id]);
$bookings = $stmt->fetchAll();

$statusColors = ['pending' => 'warning', 'accepted' => 'success', 'rejected' => 'danger', 'completed' => 'primary', 'cancelled' => 'secondary'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | ConnectBridge</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #f5f5f5; padding-top: 80px; }
        .navbar { background: linear-gradient(135deg, rgb(120,40,120), rgb(50,10,90)); }
        .navbar a, .navbar-brand { color: white !important; }
        .stars { color: #ffc107; font-size: 1.2rem; cursor: pointer; }
        .card { border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,.08); margin-bottom: 20px; }
        .badge-status { text-transform: capitalize; font-size: .85rem; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg fixed-top px-4">
    <a class="navbar-brand fw-bold" href="index.php">ConnectBridge</a>
    <div class="ms-auto">
        <a class="nav-link d-inline text-white" href="index.php">Home</a>
        <a class="nav-link d-inline fw-bold text-warning" href="booking_history.php">My Bookings</a>
        <a class="nav-link d-inline text-white" href="login.php">Logout</a>
    </div>
</nav>

<div class="container" style="max-width:800px">
    <h3 class="mb-4">My Bookings</h3>

    <?php if (!$bookings): ?>
        <div class="alert alert-info">No bookings yet. <a href="index.php">Find a worker</a>.</div>
    <?php endif; ?>

    <?php foreach ($bookings as $b): ?>
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h5 class="mb-1"><?= e($b['worker_name']) ?></h5>
                <small class="text-muted"><?= e($b['Profession']) ?></small>
            </div>
            <span class="badge bg-<?= $statusColors[$b['status']] ?> badge-status"><?= e($b['status']) ?></span>
        </div>
        <hr>
        <p class="mb-1"><strong>Date:</strong> <?= date('d M Y', strtotime($b['booking_date'])) ?> at <?= date('h:i A', strtotime($b['booking_time'])) ?></p>
        <?php if ($b['note']): ?>
            <p class="mb-1"><strong>Note:</strong> <?= e($b['note']) ?></p>
        <?php endif; ?>

        <?php if ($b['status'] === 'pending'): ?>
            <a href="booking_history.php?cancel=<?= $b['id'] ?>"
               class="btn btn-sm btn-outline-danger mt-2"
               onclick="return confirm('Cancel this booking?')">Cancel</a>

        <?php elseif ($b['status'] === 'completed' && !$b['my_rating']): ?>
            <button class="btn btn-sm btn-outline-primary mt-2"
                    data-bs-toggle="modal" data-bs-target="#rateModal"
                    data-bid="<?= $b['id'] ?>" data-wid="<?= $b['worker_id'] ?>">
                Rate Worker
            </button>

        <?php elseif ($b['my_rating']): ?>
            <p class="mt-2 mb-0">
                <strong>Your rating:</strong>
                <span class="stars"><?= str_repeat('★', $b['my_rating']) . str_repeat('☆', 5 - $b['my_rating']) ?></span>
                <?php if ($b['my_review']): ?>
                    &mdash; <em><?= e($b['my_review']) ?></em>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<!-- Rating Modal -->
<div class="modal fade" id="rateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Rate Worker</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="booking_id" id="modal_bid">
                    <input type="hidden" name="worker_id"  id="modal_wid">
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <div class="stars fs-3" id="starPicker">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span data-val="<?= $i ?>" style="cursor:pointer">☆</span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="ratingVal" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Review <small class="text-muted">(optional)</small></label>
                        <textarea name="review" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('rateModal').addEventListener('show.bs.modal', e => {
    document.getElementById('modal_bid').value = e.relatedTarget.dataset.bid;
    document.getElementById('modal_wid').value = e.relatedTarget.dataset.wid;
    // reset stars
    document.querySelectorAll('#starPicker span').forEach(s => s.textContent = '☆');
    document.getElementById('ratingVal').value = '';
});

document.querySelectorAll('#starPicker span').forEach(star => {
    star.addEventListener('click', function() {
        const val = +this.dataset.val;
        document.getElementById('ratingVal').value = val;
        document.querySelectorAll('#starPicker span').forEach((s, i) => {
            s.textContent = i < val ? '★' : '☆';
        });
    });
});
</script>
</body>
</html>
