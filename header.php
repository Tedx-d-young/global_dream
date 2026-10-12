<?php
$pageTitle = $pageTitle ?? 'Global Dream';
$adminArea = $adminArea ?? false;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | Global Dream</title>
<meta name="description" content="Discover scholarships and study opportunities from official sources.">
<link rel="stylesheet" href="<?= $adminArea ? '../assets/css/style.css' : 'assets/css/style.css' ?>">
<script src="<?= $adminArea ? '../assets/js/app.js' : 'assets/js/app.js' ?>" defer></script>
</head>
<body>
<header class="site-header">
<a class="brand" href="<?= $adminArea ? '../index.php' : 'index.php' ?>"><span class="brand-icon">✦</span><span><strong>Global Dream</strong><small>Your Scholarship, Our Mission</small></span></a>
<button class="menu-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false">☰</button>
<nav class="main-nav">
<a href="<?= $adminArea ? '../index.php' : 'index.php' ?>">Home</a>
<a href="<?= $adminArea ? '../index.php#scholarships' : 'index.php#scholarships' ?>">Scholarships</a>
<?php if ($adminArea && !empty($_SESSION['admin_id'])): ?>
<a href="dashboard.php">Dashboard</a><a href="sources.php">Sources</a><a href="discovery.php">Discovery queue</a>
<?php else: ?><a href="<?= $adminArea ? '../about.php' : 'about.php' ?>">About</a><?php endif; ?>
</nav>
<?php if ($adminArea && !empty($_SESSION['admin_id'])): ?><a class="nav-admin" href="logout.php">Log out</a><?php else: ?><a class="nav-admin" href="<?= $adminArea ? 'login.php' : 'admin/login.php' ?>">Admin</a><?php endif; ?>
</header>
<?php $flash = take_flash(); if ($flash): ?><div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
