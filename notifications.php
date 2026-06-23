<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/helpers.php';
requireWorker();

// Get current worker's ID from session
$worker_id = $_SESSION['user_id'];

// Fetch likes and dislikes count
$stmt = $conn->prepare("SELECT type, COUNT(*) as count FROM likes WHERE worker_id = ? GROUP BY type");
$stmt->execute([$worker_id]);
$likes_dislikes = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Fetch avg rating
$stmt = $conn->prepare("SELECT ROUND(AVG(rating),1) AS avg_rating, COUNT(*) AS total FROM ratings WHERE worker_id=?");
$stmt->execute([$worker_id]);
$rating_info = $stmt->fetch();

// Fetch ratings with reviews
$stmt = $conn->prepare("
    SELECT r.rating, r.review, r.created_at, u.name
    FROM ratings r
    JOIN users u ON r.user_id = u.id
    WHERE r.worker_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$worker_id]);
$ratings = $stmt->fetchAll();

// Fetch comments
$stmt = $conn->prepare("
    SELECT comments.comment, comments.created_at, users.name 
    FROM comments 
    JOIN users ON comments.user_id = users.id 
    WHERE comments.worker_id = ? 
    ORDER BY comments.created_at DESC
");
$stmt->execute([$worker_id]);
$comments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Feedback | ConnectBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            background-color: #f5f7fa;
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
        }
        
        .navbar-brand i {
            margin-right: 10px;
        }
        
        .nav-links {
            display: flex;
            gap: 1.5rem;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 5px;
        }
        
        .nav-links a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        
        .feedback-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .feedback-header h1 {
            font-size: 2.5rem;
            color: var(--dark-color);
            margin-bottom: 1rem;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .feedback-header p {
            font-size: 1.1rem;
            color: var(--text-color);
            max-width: 700px;
            margin: 0 auto;
        }
        
        .feedback-stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem 2.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.likes {
            border-top: 4px solid #4caf50;
        }
        
        .stat-card.dislikes {
            border-top: 4px solid var(--accent-color);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .stat-card.likes .stat-icon {
            color: #4caf50;
        }
        
        .stat-card.dislikes .stat-icon {
            color: var(--accent-color);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 1rem;
            color: var(--text-color);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .comments-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 2rem;
        }
        
        .section-title {
            font-size: 1.5rem;
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border-color);
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        .comment-list {
            margin-top: 1.5rem;
        }
        
        .comment {
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            background-color: var(--light-color);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .comment:hover {
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .comment-author {
            font-weight: 600;
            color: var(--dark-color);
            font-size: 1.1rem;
        }
        
        .comment-date {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .comment-text {
            line-height: 1.7;
            color: var(--text-color);
        }
        
        .no-comments {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .feedback-stats {
                flex-direction: column;
                gap: 1.5rem;
                align-items: center;
            }
            
            .stat-card {
                width: 100%;
                max-width: 300px;
            }
            
            .navbar {
                flex-direction: column;
                padding: 1rem;
            }
            
            .nav-links {
                margin-top: 1rem;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">
            <i class="fas "></i> ConnectBridge
        </a>
        <div class="nav-links">
            <a href="worker_dashboard.php">Home</a>
            <a href="login.php"> Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="feedback-header">
            <h1>Notifications.....!</h1>
            <p>See what clients think about your services and performance</p>
        </div>
        
        <div class="feedback-stats">
            <div class="stat-card likes">
                <div class="stat-icon"></div>
                <div class="stat-number"><?= $likes_dislikes['like'] ?? 0 ?></div>
                <div class="stat-label">Likes</div>
            </div>
            <div class="stat-card dislikes">
                <div class="stat-icon"></div>
                <div class="stat-number"><?= $likes_dislikes['dislike'] ?? 0 ?></div>
                <div class="stat-label">Dislikes</div>
            </div>
            <div class="stat-card" style="border-top:4px solid #ffc107">
                <div class="stat-icon" style="color:#ffc107">★</div>
                <div class="stat-number"><?= $rating_info['avg_rating'] ?? '—' ?></div>
                <div class="stat-label">Avg Rating (<?= $rating_info['total'] ?>)</div>
            </div>
        </div>
        
        <div class="comments-section">
            <h2 class="section-title">
             Client's Feedback
            </h2>
            
            <div class="comment-list">
                <?php if (count($comments) > 0): ?>
                    <?php foreach ($comments as $c): ?>
                        <div class="comment">
                            <div class="comment-header">
                                <span class="comment-author">
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($c['name']) ?>
                                </span>
                                <span class="comment-date">
                                    <i class="far fa-clock"></i> <?= date("F j, Y, g:i a", strtotime($c['created_at'])) ?>
                                </span>
                            </div>
                            <p class="comment-text"><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-comments">
                        <i class="far fa-comment-dots" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                        <p>No comments yet. Your feedback will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ratings Section -->
        <div class="comments-section" style="margin-top:2rem">
            <h2 class="section-title">Client Ratings</h2>
            <div class="comment-list">
                <?php if ($ratings): ?>
                    <?php foreach ($ratings as $r): ?>
                    <div class="comment">
                        <div class="comment-header">
                            <span class="comment-author"><?= htmlspecialchars($r['name']) ?></span>
                            <span class="comment-date"><?= date('F j, Y', strtotime($r['created_at'])) ?></span>
                        </div>
                        <p style="color:#ffc107;font-size:1.2rem"><?= str_repeat('★', $r['rating']) . str_repeat('☆', 5 - $r['rating']) ?></p>
                        <?php if ($r['review']): ?><p class="comment-text"><?= htmlspecialchars($r['review']) ?></p><?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-comments"><p>No ratings yet.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Add simple animation to stat cards when they come into view
        document.addEventListener('DOMContentLoaded', function() {
            const statCards = document.querySelectorAll('.stat-card');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = 1;
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            statCards.forEach(card => {
                card.style.opacity = 0;
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                observer.observe(card);
            });
        });
    </script>
</body>
</html>