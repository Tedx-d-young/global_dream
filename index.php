<?php
require __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Scholarships';
$q = trim($_GET['q'] ?? ''); $country = trim($_GET['country'] ?? ''); $level = trim($_GET['level'] ?? '');
$sql = "SELECT id,title,provider,country,study_level,funding_type,deadline,summary FROM scholarships WHERE status='published'";
$params=[];
if ($q!=='') { $sql.=" AND (title LIKE :q OR provider LIKE :q OR country LIKE :q OR summary LIKE :q)"; $params['q']="%$q%"; }
if ($country!=='') { $sql.=" AND country=:country"; $params['country']=$country; }
if ($level!=='') { $sql.=" AND study_level=:level"; $params['level']=$level; }
$sql.=" ORDER BY deadline IS NULL, deadline ASC, created_at DESC";
$stmt=$pdo->prepare($sql); $stmt->execute($params); $items=$stmt->fetchAll();
$countries=$pdo->query("SELECT DISTINCT country FROM scholarships WHERE status='published' AND country<>'' ORDER BY country")->fetchAll(PDO::FETCH_COLUMN);
$levels=$pdo->query("SELECT DISTINCT study_level FROM scholarships WHERE status='published' AND study_level<>'' ORDER BY study_level")->fetchAll(PDO::FETCH_COLUMN);
require __DIR__.'/includes/header.php';
?>
<main>
<section class="hero"><div class="hero-copy"><span class="eyebrow">YOUR SCHOLARSHIP, YOUR MISSION</span><h1>Dream bigger.<br><em>Study anywhere.</em></h1><p>Discover scholarship opportunities, compare funding options, and follow official application links from around the world.</p><a class="button button-light" href="#scholarships">Explore scholarships →</a><div class="hero-trust"><span>✓ Worldwide opportunities</span><span>✓ Official source links</span><span>✓ Deadline awareness</span></div></div><div class="hero-art" aria-hidden="true"><div class="orbit orbit-one"></div><div class="orbit orbit-two"></div><div class="globe">🌍</div><div class="float-card">🎓 <span><b>Keep learning</b><small>Your next chapter starts here</small></span></div><div class="art-label">DREAM • LEARN • ACHIEVE</div></div></section>
<section class="search-wrap"><form class="search-form" method="get" action="index.php#scholarships"><input type="search" name="q" value="<?= e($q) ?>" placeholder="Search scholarships, providers, countries"><select name="country"><option value="">All countries</option><?php foreach($countries as $c): ?><option value="<?= e($c) ?>" <?= $country===$c?'selected':'' ?>><?= e($c) ?></option><?php endforeach; ?></select><select name="level"><option value="">All study levels</option><?php foreach($levels as $l): ?><option value="<?= e($l) ?>" <?= $level===$l?'selected':'' ?>><?= e($l) ?></option><?php endforeach; ?></select><button class="button" type="submit">Search</button></form></section>
<section class="section" id="scholarships"><div class="section-heading"><div><span class="eyebrow">OPPORTUNITIES</span><h2>Latest scholarships</h2><p>Check each official source for current requirements and deadlines.</p></div><span class="result-count"><?= count($items) ?> listed</span></div>
<?php if (!$items): ?><div class="empty-state"><h3>No matching scholarships found</h3><p>Try different filters or check back later.</p><a class="text-link" href="index.php#scholarships">Clear filters</a></div><?php else: ?><div class="scholarship-grid"><?php foreach($items as $s): ?><article class="scholarship-card"><div class="card-top"><span class="provider-icon"><?= e(mb_strtoupper(mb_substr($s['provider'] ?: $s['title'],0,1))) ?></span><span class="funding-tag"><?= e($s['funding_type']) ?></span></div><p class="provider"><?= e($s['provider']) ?></p><h3><?= e($s['title']) ?></h3><p class="summary"><?= e($s['summary']) ?></p><div class="card-meta"><span>⌖ <?= e($s['country']) ?></span><span>▤ <?= e($s['study_level']) ?></span></div><div class="card-bottom"><span class="deadline">Deadline: <?= $s['deadline']?e(date('d M Y',strtotime($s['deadline']))):'Check official site' ?></span><a class="text-link" href="scholarship.php?id=<?= (int)$s['id'] ?>">View details →</a></div></article><?php endforeach; ?></div><?php endif; ?></section>
<section class="newsletter"><div><span class="eyebrow">YOUR FUTURE STARTS HERE</span><h2>One opportunity can change your future.</h2><p>Use Global Dream to discover opportunities and verify details at the source.</p></div><a class="button button-light" href="#scholarships">Find an opportunity →</a></section>
</main>
<?php require __DIR__.'/includes/footer.php'; ?>
