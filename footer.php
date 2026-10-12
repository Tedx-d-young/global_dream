<footer class="site-footer"><div class="footer-inner">
<a class="brand brand-footer" href="<?= $adminArea ? '../index.php' : 'index.php' ?>"><span class="brand-icon">✦</span><span><strong>Global Dream</strong><small>Your Scholarship, Our Mission</small></span></a>
<p>Scholarship information to help students discover their next opportunity.</p>
<div class="footer-links"><a href="<?= $adminArea ? '../index.php' : 'index.php' ?>">Home</a><a href="<?= $adminArea ? '../about.php' : 'about.php' ?>">About</a><?php if($adminArea): ?><a href="dashboard.php">Dashboard</a><?php endif; ?></div>
<small>© <?= date('Y') ?> Global Dream. Always verify details with official providers.</small>
</div></footer></body></html>
