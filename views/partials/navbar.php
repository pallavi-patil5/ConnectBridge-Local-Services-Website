<?php
// Usage: include_navbar('Home', 'index.php');
// $brand: brand name, $active: active link label
$navLinks = $navLinks ?? [];
$brand    = $brand ?? 'ConnectBridge';
$active   = $active ?? null;

// Role-based navigation (session-driven)
$isAuthenticated = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? null;

$publicLinks = [
  'Home' => 'index.php',
  'Services' => 'services.php',
  'About Us' => 'aboutus.html',
  'Contact' => 'contact.php',
  'Login' => 'login.php',
  'Register' => 'register.php'
];

$roleLinks = [];
if ($isAuthenticated) {
  if ($role === 'admin') {
    $roleLinks = [
      'Admin Dashboard' => 'admin_dashboard.php',
      'Manage Users' => 'admin_dashboard.php',
      'Analytics' => 'admin_dashboard.php'
    ];
  } elseif ($role === 'worker') {
    $roleLinks = [
      'Worker Dashboard' => 'worker_dashboard.php',
      'Job Requests' => 'worker_dashboard.php',
      'Availability' => 'worker_dashboard.php',
      'Profile' => 'worker_details.php'
    ];
  } else {
    // user
    $roleLinks = [
      'Dashboard' => 'user_dashboard.php',
      'My Bookings' => 'booking_history.php',
      'Profile' => 'worker_details.php'
    ];
  }
}

$logoutHref = 'logout.php';
?>

<nav class="navbar" id="cb-navbar" aria-label="Primary navigation">
  <div class="brand"><?= e($brand) ?></div>

  <div class="nav-links" role="navigation" aria-label="Main">
    <?php
      $linksToRender = $isAuthenticated ? $roleLinks : $publicLinks;
      foreach ($linksToRender as $label => $href) {
        // keep navbar item highlighting based on $active label if provided
        $isActive = ($active !== null && $label === $active);
    ?>
      <a href="<?= e($href) ?>" class="nav-link<?= $isActive ? ' is-active' : '' ?>" aria-current="<?= $isActive ? 'page' : 'false' ?>"><?= e($label) ?></a>
    <?php } ?>

    <?php if ($isAuthenticated): ?>
      <a href="<?= e($logoutHref) ?>" class="nav-link" aria-current="false">Logout</a>
    <?php endif; ?>
  </div>
</nav>


<script>
  (function () {
    var nav = document.getElementById('cb-navbar');
    if (!nav) return;

    var onScroll = function () {
      // Add scrolled class after a small threshold
      if (window.scrollY > 10) nav.classList.add('is-scrolled');
      else nav.classList.remove('is-scrolled');
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  })();
</script>





