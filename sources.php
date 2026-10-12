<?php
require __DIR__.'/../includes/bootstrap.php';require_admin();
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$action=$_POST['action']??'';
 if($action==='save'){
  $id=(int)($_POST['id']??0);$name=trim($_POST['name']??'');$url=trim($_POST['feed_url']??'');$kind=$_POST['source_type']??'rss';
  if($name===''||!valid_http_url($url))$error='Enter a source name and valid http/https feed URL.';
  elseif(!in_array($kind,['rss','atom'],true))$error='Unsupported feed type.';
  else{
   $host=strtolower((string)parse_url($url,PHP_URL_HOST));
   if(!$host){$error='Feed URL must include a hostname.';}
   else{
    if($id){$st=$pdo->prepare('UPDATE discovery_sources SET name=?,feed_url=?,host_allowlist=?,source_type=?,active=?,updated_at=NOW() WHERE id=?');$st->execute([$name,$url,$host,$kind,isset($_POST['active'])?1:0,$id]);}
    else{$st=$pdo->prepare('INSERT INTO discovery_sources(name,feed_url,host_allowlist,source_type,active,created_at,updated_at) VALUES(?,?,?,?,?,NOW(),NOW())');$st->execute([$name,$url,$host,$kind,isset($_POST['active'])?1:0]);}
    flash('Source saved. Fetch only feeds you are permitted to use.');header('Location: sources.php');exit;
   }
  }
 } elseif($action==='toggle'){$st=$pdo->prepare('UPDATE discovery_sources SET active=IF(active=1,0,1),updated_at=NOW() WHERE id=?');$st->execute([(int)$_POST['id']]);flash('Source status updated.');header('Location: sources.php');exit;}
 elseif($action==='delete'){$st=$pdo->prepare('DELETE FROM discovery_sources WHERE id=?');$st->execute([(int)$_POST['id']]);flash('Source removed.');header('Location: sources.php');exit;}
}
$edit=null;if(isset($_GET['edit'])){$st=$pdo->prepare('SELECT * FROM discovery_sources WHERE id=?');$st->execute([(int)$_GET['edit']]);$edit=$st->fetch();}
$sources=$pdo->query('SELECT s.*, (SELECT COUNT(*) FROM discovery_items d WHERE d.source_id=s.id) item_count FROM discovery_sources s ORDER BY s.created_at DESC')->fetchAll();
$pageTitle='Discovery sources';$adminArea=true;require __DIR__.'/../includes/header.php';
?>
<main class="section admin-page"><div class="admin-head"><div><span class="eyebrow">AUTOMATED DISCOVERY</span><h1>Feed sources</h1><p>Add RSS or Atom feeds you are permitted to access. Every discovered item goes to manual review.</p></div><a class="button" href="discovery.php">Review queue →</a></div>
<?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<form class="editor-form compact-form" method="post"><h2><?= $edit?'Edit source':'Add a feed source' ?></h2><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>"><div class="form-grid"><label>Source name<input name="name" required value="<?= e($edit['name']??'') ?>" placeholder="University announcements"></label><label>Feed URL<input type="url" name="feed_url" required value="<?= e($edit['feed_url']??'') ?>" placeholder="https://example.edu/feed.xml"></label><label>Feed format<select name="source_type"><option value="rss" <?= ($edit['source_type']??'rss')==='rss'?'selected':'' ?>>RSS</option><option value="atom" <?= ($edit['source_type']??'')==='atom'?'selected':'' ?>>Atom</option></select></label><label class="check-label"><input type="checkbox" name="active" value="1" <?= !isset($edit['active'])||(int)$edit['active']===1?'checked':'' ?>> Active</label></div><p class="muted">Security: the fetcher validates the URL host and blocks private/local IP destinations. Use HTTPS feed URLs from institutions or providers whose terms permit feed use.</p><div class="form-actions"><button class="button" type="submit">Save source</button><?php if($edit): ?><a class="button button-outline" href="sources.php">Cancel edit</a><?php endif; ?></div></form>
<div class="table-wrap"><table class="admin-table"><thead><tr><th>Source</th><th>Feed URL</th><th>Items found</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach($sources as $s): ?><tr><td><strong><?= e($s['name']) ?></strong><small><?= e($s['source_type']) ?> · <?= e($s['host_allowlist']) ?></small></td><td class="url-cell"><?= e($s['feed_url']) ?></td><td><?= (int)$s['item_count'] ?></td><td><?= $s['active']?'Active':'Paused' ?></td><td class="actions"><a href="sources.php?edit=<?= (int)$s['id'] ?>">Edit</a><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$s['id'] ?>"><button class="table-button" type="submit"><?= $s['active']?'Pause':'Activate' ?></button></form><form method="post" onsubmit="return confirm('Delete this source and its discovered items?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$s['id'] ?>"><button class="delete-link" type="submit">Delete</button></form></td></tr><?php endforeach; ?><?php if(!$sources): ?><tr><td colspan="5">No sources configured. Add an RSS or Atom feed above.</td></tr><?php endif; ?></tbody></table></div></main>
<?php require __DIR__.'/../includes/footer.php'; ?>
