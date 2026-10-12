<?php
require __DIR__.'/../includes/bootstrap.php';require_admin();
$stats=[
 'published'=>(int)$pdo->query("SELECT COUNT(*) FROM scholarships WHERE status='published'")->fetchColumn(),
 'drafts'=>(int)$pdo->query("SELECT COUNT(*) FROM scholarships WHERE status='draft'")->fetchColumn(),
 'queue'=>(int)$pdo->query("SELECT COUNT(*) FROM discovery_items WHERE review_status='pending'")->fetchColumn(),
 'sources'=>(int)$pdo->query("SELECT COUNT(*) FROM discovery_sources WHERE active=1")->fetchColumn()
];
$rows=$pdo->query("SELECT id,title,provider,country,deadline,status,updated_at FROM scholarships ORDER BY updated_at DESC LIMIT 30")->fetchAll();
$pageTitle='Dashboard';$adminArea=true;require __DIR__.'/../includes/header.php';
?>
<main class="section admin-page"><div class="admin-head"><div><span class="eyebrow">GLOBAL DREAM CMS</span><h1>Dashboard</h1><p>Welcome, <?= e($_SESSION['admin_name']??'Admin') ?>. Review discovered items before publishing them.</p></div><div class="admin-actions"><a class="button" href="edit.php">＋ Add scholarship</a><a class="button button-outline" href="discovery.php">Review discovery queue</a></div></div>
<div class="stats-grid"><div><strong><?= $stats['published'] ?></strong><span>Published</span></div><div><strong><?= $stats['drafts'] ?></strong><span>Drafts</span></div><div><strong><?= $stats['queue'] ?></strong><span>Awaiting review</span></div><div><strong><?= $stats['sources'] ?></strong><span>Active feeds</span></div></div>
<div class="section-heading"><div><h2>Recent scholarship posts</h2><p>Edit published posts or keep them as drafts.</p></div></div>
<div class="table-wrap"><table class="admin-table"><thead><tr><th>Title</th><th>Country</th><th>Deadline</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><strong><?= e($r['title']) ?></strong><small><?= e($r['provider']) ?></small></td><td><?= e($r['country']) ?></td><td><?= $r['deadline']?e(date('d M Y',strtotime($r['deadline']))):'Not listed' ?></td><td><span class="status"><?= e(ucfirst($r['status'])) ?></span></td><td class="actions"><a href="edit.php?id=<?= (int)$r['id'] ?>">Edit</a><?php if($r['status']==='published'): ?><a target="_blank" href="../scholarship.php?id=<?= (int)$r['id'] ?>">View</a><?php endif; ?></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="5">No posts yet. Add a scholarship or review discovered items.</td></tr><?php endif; ?></tbody></table></div>
<p class="muted">Automatic discovery adds candidates to a review queue. It never publishes a feed item automatically.</p></main>
<?php require __DIR__.'/../includes/footer.php'; ?>
