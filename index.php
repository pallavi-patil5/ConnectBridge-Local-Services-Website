<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/helpers.php';
// If user is not logged in, show login page first
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

requireLogin();


// Initialize filter variables
$profession_filter = isset($_GET['profession']) ? $_GET['profession'] : '';
$location_filter = isset($_GET['location']) ? $_GET['location'] : '';

// Build the SQL query with filters
$sql = "SELECT * FROM users WHERE role = 'worker' AND is_verified = 1";
$params = [];

if (!empty($profession_filter)) {
    $sql .= " AND Profession = :profession";
    $params[':profession'] = $profession_filter;
}

if (!empty($location_filter)) {
    $sql .= " AND address LIKE :location";
    $params[':location'] = "%$location_filter%";
}

$stmt = $conn->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$workers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verified Workers</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* Hero Section */
        .hero {
            position: relative;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            overflow: hidden;
        }

        .hero::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1507679799987-c73779587ccf?ixlib=rb-1.2.1&auto=format&fit=crop&w=1351&q=80') no-repeat center center/cover;
            filter: blur(3px) brightness(0.7);
            z-index: -1;
        }
        .hero-content {
            max-width: 800px;
            padding: 20px;
            animation: fadeInUp 1s ease-out;
        }
        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }
        .explore-btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: rgb(164, 87, 160);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .explore-btn:hover {
            background-color: rgb(182, 102, 184);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }
        
        /* Workers Section */
        .workers-section {
            padding: 100px 20px;
            background-color: #ffffff;
        }
        .section-title {
            text-align: center;
            margin-bottom: 50px;
            font-size: 2.5rem;
            color: #333;
            position: relative;
        }
        .section-title::after {
            content: "";
            display: block;
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, rgb(164, 87, 160), rgb(120, 40, 120));
            margin: 15px auto;
            border-radius: 2px;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: left;
            transition: transform 0.3s, box-shadow 0.3s;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.5s ease-out forwards;
        }
        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }
        .card:nth-child(4) { animation-delay: 0.4s; }
        .card:nth-child(5) { animation-delay: 0.5s; }
        .card:nth-child(6) { animation-delay: 0.6s; }
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0px 15px 30px rgba(0, 0, 0, 0.15);
        }
        .card h3 {
            margin-bottom: 15px;
            color: #333;
            font-size: 1.4rem;
        }
        .card p {
            margin: 8px 0;
            color: #555;
            line-height: 1.5;
        }
        .card a {
            display: block;
            color:rgb(255, 255, 255);
            text-decoration: none;
            transition: color 0.3s;
        }
        .card a:hover {
            color:rgb(255, 255, 255);
            text-decoration: underline;
        }
        .see-worker {
            display: block;
            margin-top: 15px;
            padding: 10px;
            background-color:rgb(164, 87, 160);
            color: white;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s, transform 0.2s;
        }
        .see-worker:hover {
            background-color:rgb(182, 102, 184);
            transform: scale(1.05);
        }
        
        /* Filter Section */
        .filter-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            align-items: flex-end;
        }
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        .filter-group select, 
        .filter-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .filter-buttons {
            display: flex;
            gap: 10px;
        }
        .filter-btn {
            padding: 10px 20px;
            background-color: rgb(164, 87, 160);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        .filter-btn:hover {
            background-color: rgb(182, 102, 184);
        }
        .reset-btn {
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        .reset-btn:hover {
            background-color: #5a6268;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Footer */
        .foot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: rgb(0, 0, 0) !important;
            height: 70px;
            color: rgb(255, 255, 255);
            width: 100%;
            padding: 0 20px;
        }
        .content-left, .content-right {
            display: flex;
            flex-direction: column;
        }
        .social-icon {
            margin-right: 40px;
            height: 30px;
            width: 30px;
            border-radius: 7px;
        }
        .vvp1 {
            background-color: rgb(187, 220, 225);
        }
        .vvp2 {
            background-color: rgb(124, 199, 208);
        }
        .vvpp {
            color: black !important;
        }
        .no-workers {
            text-align: center;
            font-size: 1.2rem;
            color: #666;
            padding: 50px;
            width: 100%;
        }
    </style>
</head>
<body>
    <?php
    $navLinks = ['Home' => 'index.php', 'About Us' => 'aboutus.html', 'My Bookings' => 'booking_history.php', 'Logout' => 'login.php'];
    include 'views/partials/navbar.php';
    ?>
    
    <!-- Hero Section -->
    <section class="hero" id="hero">
        <div class="hero-content">
            <h1>Find Verified Professionals</h1>
            <p>Connect with trusted and verified workers for all your needs. Quality service guaranteed.</p>
            <button class="explore-btn" id="exploreBtn">Explore Workers</button>
        </div>
    </section>
    
    <!-- Workers Section -->
    <section class="workers-section" id="workers">
        <h1 class="section-title">Verified Workers</h1>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="profession">Profession</label>
                    <select id="profession" name="profession">
                        <option value="">All Professions</option>
                        <option value="Plumber" <?php echo ($profession_filter == 'Plumber') ? 'selected' : ''; ?>>Plumber</option>
                        <option value="Electrician" <?php echo ($profession_filter == 'Electrician') ? 'selected' : ''; ?>>Electrician</option>
                        <option value="AC Repair" <?php echo ($profession_filter == 'AC Repair') ? 'selected' : ''; ?>>AC Repair</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="location">Location</label>
                    <select id="location" name="location">
                        <option value="">All Locations</option>
                        <option value="Bibwewadi" <?php echo ($location_filter == 'Bibwewadi') ? 'selected' : ''; ?>>Bibwewadi</option>
                        <option value="Pune" <?php echo ($location_filter == 'Pune') ? 'selected' : ''; ?>>Pune</option>
                        <option value="Mumbai" <?php echo ($location_filter == 'Mumbai') ? 'selected' : ''; ?>>Mumbai</option>
                    </select>
                </div>
                <div class="filter-buttons">
                    <button type="submit" class="filter-btn">Search</button>
                    <a href="index.php" class="reset-btn">Reset</a>
                </div>
            </form>
        </div>
        
        <div class="container">
            <?php if (count($workers) > 0): ?>
                <?php foreach ($workers as $worker): ?>
                <div class="card">
                    <h3><?php echo e($worker['name']); ?></h3>
                    <p><strong>Profession:</strong> <?php echo e($worker['Profession']); ?></p>
                    <p><strong>Mobile:</strong> <?php echo e($worker['mobile']); ?></p>
                    <p><strong>Address:</strong> <?php echo e($worker['address']); ?></p>
                    <p><strong>Aadhar:</strong> 
                        <?php if ($worker['aadhar_file']): ?>
                            <a class="vvpp" href="/DBMS/uploads/<?php echo rawurlencode(basename($worker['aadhar_file'])); ?>" target="_blank">View Aadhar</a>
                        <?php else: ?>
                            No Aadhar Uploaded
                        <?php endif; ?>
                    </p>
                    <p><strong>PAN:</strong> 
                        <?php if ($worker['pan_file']): ?>
                            <a class="vvpp" href="/DBMS/uploads/<?php echo rawurlencode(basename($worker['pan_file'])); ?>" target="_blank">View PAN</a>
                        <?php else: ?>
                            No PAN Uploaded
                        <?php endif; ?>
                    </p>
                    <a href="worker_details.php?id=<?php echo $worker['id']; ?>" class="see-worker">View Profile</a>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-workers">
                    <p>No workers found matching your criteria.</p>
                    <p><a href="index.php">Show all workers</a></p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'views/partials/footer.php'; ?>
    
    <script>
        // Smooth scrolling for Explore button
        document.getElementById('exploreBtn').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('workers').scrollIntoView({
                behavior: 'smooth'
            });
        });
        
        // Animation for cards when they come into view
        const cards = document.querySelectorAll('.card');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = `fadeInUp 0.5s ease-out forwards`;
                }
            });
        }, { threshold: 0.1 });
        
        cards.forEach(card => {
            observer.observe(card);
        });
    </script>
</body>
</html>