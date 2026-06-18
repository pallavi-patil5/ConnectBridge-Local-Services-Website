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
</head>
<body>
    <?php
$navLinks = ['Home' => 'index.php', 'About Us' => 'aboutus.html', 'Logout' => 'login.php'];
    $active = 'Home';
    include 'views/partials/navbar.php';
    ?>

    
    <!-- Hero Section -->
    <section class="hero" id="hero">
        <div class="hero-content">
            <h1>Find Verified Professionals</h1>
            <p>Connect with trusted and verified workers for all your needs. Quality service guaranteed.</p>
            <a class="explore-btn" id="exploreBtn" href="#workers">Explore Workers</a>

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
                            <a class="vvpp" href="<?php echo $worker['aadhar_file']; ?>" target="_blank">View Aadhar</a>
                        <?php else: ?>
                            No Aadhar Uploaded
                        <?php endif; ?>
                    </p>
                    <p><strong>PAN:</strong> 
                        <?php if ($worker['pan_file']): ?>
                            <a class="vvpp" href="<?php echo $worker['pan_file']; ?>" target="_blank">View PAN</a>
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
