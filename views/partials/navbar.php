<?php
// Usage: include_navbar('Home', 'index.php');
// $brand: brand name, $active: active link label
$navLinks = $navLinks ?? [];
$brand    = $brand ?? 'ConnectBridge';
?>
<nav class="navbar">
    <div class="brand"><?= e($brand) ?></div>
    <div>
        <?php foreach ($navLinks as $label => $href): ?>
            <a href="<?= e($href) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </div>
</nav>

