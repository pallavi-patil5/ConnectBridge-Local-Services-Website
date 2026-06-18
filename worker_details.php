<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/helpers.php';
requireLogin();

// Fetch worker details
if (isset($_GET['id'])) {
    $worker_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$worker_id]);
    $worker = $stmt->fetch();

    if (!$worker) {
        die("Worker not found.");
    }
} else {
    die("Invalid request.");
}

// Handle like/dislike
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'];

    // Check if the user has already liked/disliked
    $stmt = $conn->prepare("SELECT * FROM likes WHERE user_id = ? AND worker_id = ?");
    $stmt->execute([$user_id, $worker_id]);
    $existing_action = $stmt->fetch();

    if ($existing_action) {
        // Update existing like/dislike
        $stmt = $conn->prepare("UPDATE likes SET type = ? WHERE user_id = ? AND worker_id = ?");
        $stmt->execute([$action, $user_id, $worker_id]);
    } else {
        // Insert new like/dislike
        $stmt = $conn->prepare("INSERT INTO likes (user_id, worker_id, type) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $worker_id, $action]);
    }
}

// Handle complaint submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complaint'])) {
    $user_id = $_SESSION['user_id'];
    $message = trim($_POST['complaint']);
    if ($message) {
        $stmt = $conn->prepare("INSERT INTO complaints (user_id, worker_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $worker_id, $message]);
    }
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    $user_id = $_SESSION['user_id'];
    $comment = $_POST['comment'];

    $stmt = $conn->prepare("INSERT INTO comments (user_id, worker_id, comment) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $worker_id, $comment]);
}

// Fetch likes/dislikes count
$stmt = $conn->prepare("SELECT type, COUNT(*) as count FROM likes WHERE worker_id = ? GROUP BY type");
$stmt->execute([$worker_id]);
$likes_dislikes = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Fetch comments
$stmt = $conn->prepare("SELECT comments.comment, comments.created_at, users.name FROM comments JOIN users ON comments.user_id = users.id WHERE comments.worker_id = ? ORDER BY comments.created_at DESC");
$stmt->execute([$worker_id]);
$comments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Details | ConnectBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: rgb(164, 87, 160);
            --secondary-color: rgb(120, 40, 120);
            --dark-color: #333;
            --light-color: #f5f5f5;
            --white: #ffffff;
            --gray: #e6e6e6;
            --dark-gray: #666;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-color);
            color: var(--dark-color);
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--secondary-color), rgb(50, 10, 90));
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--white);
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        
        .navbar a {
            color: var(--white);
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
            margin-left: 10px;
            font-weight: 500;
        }
        
        .navbar a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .main-container {
            display: flex;
            flex: 1;
            margin-top: 60px;
            margin-bottom: 70px;
        }
        
        .sidebar {
            width: 300px;
            background-color: var(--white);
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            position: fixed;
            height: calc(100vh - 130px);
            overflow-y: auto;
        }
        
        .profile-card {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid var(--gray);
            margin-bottom: 20px;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--gray);
            margin: 0 auto 15px;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: var(--dark-gray);
        }
        
        .profile-card h2 {
            font-size: 22px;
            margin-bottom: 5px;
            color: var(--dark-color);
        }
        
        .profile-card p {
            color: var(--dark-gray);
            font-size: 14px;
        }
        
        .contact-info {
            padding: 15px 0;
            border-bottom: 1px solid var(--gray);
            margin-bottom: 20px;
        }
        
        .contact-info h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: var(--dark-color);
        }
        
        .contact-info p {
            margin-bottom: 10px;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        
        .contact-info i {
            margin-right: 10px;
            color: var(--primary-color);
            width: 20px;
            text-align: center;
        }
        
        .content-area {
            flex: 1;
            margin-left: 300px;
            padding: 30px;
            background-color: var(--white);
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.05);
            animation: slideIn 0.5s ease-in-out;
        }
        
        .section {
            margin-bottom: 30px;
            animation: fadeInUp 0.5s ease-in-out;
        }
        
        .section h2 {
            font-size: 22px;
            margin-bottom: 20px;
            color: var(--dark-color);
            position: relative;
            padding-bottom: 10px;
        }
        
        .section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }
        
        .about p {
            margin-bottom: 15px;
            line-height: 1.7;
        }
        
        .document-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--gray);
        }
        
        .document-item:last-child {
            border-bottom: none;
        }
        
        .document-item a {
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .document-item a:hover {
            text-decoration: underline;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            margin: 20px 0;
        }
        
        .actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .like-btn {
            background-color: var(--primary-color);
            color: var(--white);
        }
        
        .dislike-btn {
            background-color: #f1f1f1;
            color: var(--dark-color);
        }
        
        .actions button:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .comment-form textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid var(--gray);
            border-radius: 5px;
            resize: none;
            margin-bottom: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
        }
        
        .comment-form textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(164, 87, 160, 0.2);
        }
        
        .comment-form button {
            padding: 10px 25px;
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .comment-form button:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .comment-list {
            margin-top: 30px;
        }
        
        .comment-item {
            background-color: var(--light-color);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            animation: fadeIn 0.5s ease-in-out;
            transition: all 0.3s ease;
        }
        
        .comment-item:hover {
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .comment-author {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .comment-date {
            color: var(--dark-gray);
        }
        
        .comment-text {
            line-height: 1.6;
        }
        
        .foot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #000;
            height: 70px;
            color: #fff;
            width: 100%;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 0 30px;
            z-index: 1000;
        }
        
        .content-left, .content-right {
            display: flex;
            flex-direction: column;
        }
        
        .social-icon {
            margin-right: 20px;
            height: 30px;
            width: 30px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .social-icon:hover {
            transform: scale(1.1);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { 
                opacity: 0;
                transform: translateX(20px);
            }
            to { 
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                position: static;
                height: auto;
            }
            
            .content-area {
                margin-left: 0;
            }
            
            .profile-pic {
                width: 80px;
                height: 80px;
                font-size: 30px;
            }
        }
    </style>
</head>
<body>
    <?php
    $navLinks = ['Home' => 'index.php', 'Logout' => 'login.php'];
    include 'views/partials/navbar.php';
    ?>

    <div class="main-container">
        <!-- Left Sidebar -->
        <div class="sidebar">
            <div class="profile-card">
                <div class="profile-pic">
                    <?php echo strtoupper(substr($worker['name'], 0, 1)); ?>
                </div>
                <h2><?= e($worker['name']) ?></h2>
                <p>Verified Worker</p>
            </div>
            
            <div class="contact-info">
                <h3>Contact Information</h3>
                <p><i class="fas fa-envelope"></i> <?= e($worker['email']) ?></p>
                <p><i class="fas fa-phone"></i> <?= e($worker['mobile']) ?></p>
                <p><i class="fas fa-map-marker-alt"></i> <?= e($worker['address']) ?></p>
                <p><i class="fas fa-briefcase"></i> <?= e($worker['Profession']) ?></p>
            </div>
        </div>
        
        <!-- Main Content Area -->
        <div class="content-area">
            <!-- About Section -->
            <div class="section about">
                <h2>About</h2>
                <p><?= e($worker['description']) ?></p>
            </div>
            
            <!-- Documents Section -->
            <div class="section documents">
                <h2>Documents</h2>
                <div class="document-item">
                    <span>Aadhar Card</span>
                    <?php if ($worker['aadhar_file']): ?>
                        <a href="<?php echo $worker['aadhar_file']; ?>" target="_blank"><i class="fas fa-eye"></i> View</a>
                    <?php else: ?>
                        <span style="color: var(--dark-gray);">Not provided</span>
                    <?php endif; ?>
                </div>
                <div class="document-item">
                    <span>PAN Card</span>
                    <?php if ($worker['pan_file']): ?>
                        <a href="<?php echo $worker['pan_file']; ?>" target="_blank"><i class="fas fa-eye"></i> View</a>
                    <?php else: ?>
                        <span style="color: var(--dark-gray);">Not provided</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Book Service -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
            <div class="section">
                <a href="booking.php?id=<?= $worker['id'] ?>" class="like-btn" style="display:inline-block;padding:10px 25px;border-radius:5px;text-decoration:none;color:#fff;background:rgb(164,87,160)">Book This Worker</a>
            </div>
            <?php endif; ?>

            <!-- Like/Dislike Section -->
            <div class="section">
                <h2>Feedback</h2>
                <div class="actions">
                    <form action="worker_details.php?id=<?php echo $worker_id; ?>" method="post">
                        <button type="submit" name="action" value="like" class="like-btn">
                            <i class="fas fa-thumbs-up"></i> Like (<?php echo $likes_dislikes['like'] ?? 0; ?>)
                        </button>
                    </form>
                    <form action="worker_details.php?id=<?php echo $worker_id; ?>" method="post">
                        <button type="submit" name="action" value="dislike" class="dislike-btn">
                            <i class="fas fa-thumbs-down"></i> Dislike (<?php echo $likes_dislikes['dislike'] ?? 0; ?>)
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Comments Section -->
            <div class="section">
                <h2>Comments</h2>
                <form class="comment-form" action="worker_details.php?id=<?php echo $worker_id; ?>" method="post">
                    <textarea name="comment" placeholder="Add a comment..." rows="3" required></textarea>
                    <button type="submit"><i class="fas fa-paper-plane"></i> Post Comment</button>
                </form>
                
                <div class="comment-list">
                    <?php foreach ($comments as $comment): ?>
                    <div class="comment-item">
                        <div class="comment-header">
                            <span class="comment-author"><?= e($comment['name']) ?></span>
                            <span class="comment-date"><?php echo date('M j, Y \a\t g:i a', strtotime($comment['created_at'])); ?></span>
                        </div>
                        <p class="comment-text"><?= e($comment['comment']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Complaint Section -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
            <div class="section">
                <h2>Report a Problem</h2>
                <form class="comment-form" action="worker_details.php?id=<?= $worker_id ?>" method="post">
                    <textarea name="complaint" placeholder="Describe your complaint..." rows="3" required></textarea>
                    <button type="submit" style="background:#dc3545">Submit Complaint</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'views/partials/footer.php'; ?>
</body>
</html>