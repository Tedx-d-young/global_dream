<?php
require __DIR__.'/../includes/bootstrap.php';require_admin();
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$id=(int)($_POST['id']??0);$action=$_POST['action']??'';
 $st=$pdo->prepare('SELECT * FROM discovery_items WHERE id=?');$st->execute([$id]);$item=$st->fetch();
 if($item){
  if($action==='dismiss'){$pdo->prepare("UPDATE discovery_items SET review_status='dismissed',reviewed_by=?,reviewed_at=NOW() WHERE id=?")->execute([$_SESSION['admin_id'],$id]);flash('Item dismissed.');}
  elseif($action==='publish'){
   $title=trim($_POST['title']??$item['title']);$provider=trim($_POST['provider']??$item['source_name']);$country=trim($_POST['country']??'International');$level=trim($_POST['study_level']??'Various levels');$funding=$_POST['funding_type']??'Other';$deadline=trim($_POST['deadline']??'');$summary=trim($_POST['summary']??$item['summary']);$url=trim($_POST['official_url']??$item['item_url']);
   if($title===''||$provider===''||$summary===''||!valid_http_url($url)||!in_array($funding,['Fully funded','Tuition waiver','Partial funding','Other'],true)){flash('Check required fields, funding type, and official URL.','error');}
   else{
    $pdo->beginTransaction();
    try{
     $ins=$pdo->prepare("INSERT INTO scholarships(title,provider,country,study_level,funding_type,deadline,official_url,summary,description,status,source_name,source_item_url,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?, 'published',?,?,NOW(),NOW())");
     $ins->execute([$title,$provider,$country,$level,$funding,$deadline!==''?$deadline:null,$url,$summary,trim($_POST['description']??$summary),$item['source_name'],$item['item_url']]);
     $pdo->prepare("UPDATE discovery_items SET review_status='published',reviewed_by=?,reviewed_at=NOW(),scholarship_id=? WHERE id=?")->execute([$_SESSION['admin_id'],(int)$pdo->lastInsertId(),$id]);
     $pdo->commit();flash('Reviewed item published.');
    }catch(Throwable $e){$pdo->rollBack();error_log('Discovery publish failed: '.$e->getMessage());flash('Could not publish this item. Check server logs.','error');}
   }
  }
 }
 header('Location: discovery.php');exit;
}
$status=$_GET['status']??'pending';if(!in_array($status,['pending','published','dismissed','all'],true))$status='pending';
$sql='SELECT d.*,s.name AS source_name FROM discovery_items d JOIN discovery_sources s ON s.id=d.source_id';$params=[];
if($status!=='all'){$sql.=' WHERE d.review_status=?';$params[]=$status;}
$sql.=' ORDER BY d.discovered_at DESC LIMIT 100';$st=$pdo->prepare($sql);$st->execute($params);$items=$st->fetchAll();
$pageTitle='Discovery queue';$adminArea=true;require __DIR__.'/../includes/header.php';
?>
<main class="section admin-page"><div class="admin-head"><div><span class="eyebrow">HUMAN REVIEW REQUIRED</span><h1>Discovery queue</h1><p>Feed items are candidates, not verified scholarship announcements. Check details before publishing.</p></div><a class="button" href="sources.php">Manage feeds →</a></div>
<div class="tabs"><?php foreach(['pending'=>'Pending','published'=>'Published','dismissed'=>'Dismissed','all'=>'All items'] as $key=>$label): ?><a class="<?= $status===$key?'active':'' ?>" href="discovery.php?status=<?= $key ?>"><?= $label ?></a><?php endforeach; ?></div>
<?php if(!$items): ?><div class="empty-state"><h3>Nothing in this view</h3><p>Run the feed importer from cron or the command line, then review newly discovered items here.</p></div><?php endif; ?>
<div class="discovery-list"><?php foreach($items as $it): ?><article class="discovery-card"><div class="discovery-top"><div><span class="status"><?= e(ucfirst($it['review_status'])) ?></span><p class="provider"><?= e($it['source_name']) ?> · discovered <?= e(date('d M Y H:i',strtotime($it['discovered_at']))) ?></p><h2><?= e($it['title']) ?></h2></div><a class="text-link" href="<?= e($it['item_url']) ?>" target="_blank" rel="noopener noreferrer">Open source item ↗</a></div><p><?= e($it['summary']) ?></p><details><summary>Review and publish</summary><form method="post" class="review-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$it['id'] ?>"><div class="form-grid"><label>Title *<input name="title" required value="<?= e($it['title']) ?>"></label><label>Provider / institution *<input name="provider" required value="<?= e($it['source_name']) ?>"></label><label>Country<input name="country" value="International"></label><label>Study level<select name="study_level"><?php foreach(['Various levels','Undergraduate','Master’s','PhD','Postdoctoral','Short course'] as $v): ?><option><?= e($v) ?></option><?php endforeach; ?></select></label><label>Funding type<select name="funding_type"><?php foreach(['Other','Fully funded','Tuition waiver','Partial funding'] as $v): ?><option><?= e($v) ?></option><?php endforeach; ?></select></label><label>Deadline (only if verified)<input type="date" name="deadline"></label><label class="full">Summary *<textarea name="summary" required rows="3"><?= e($it['summary']) ?></textarea></label><label class="full">Description<textarea name="description" rows="3"><?= e($it['summary']) ?></textarea></label><label class="full">Official URL *<input type="url" name="official_url" required value="<?= e($it['item_url']) ?>"></label></div><div class="form-actions"><button class="button" name="action" value="publish" type="submit">Publish after verification</button><button class="danger-button" name="action" value="dismiss" type="submit" formnovalidate>Dismiss item</button></div><p class="muted">Do not mark an item fully funded or enter a deadline unless you verified that information from the official page.</p></form></details></article><?php endforeach; ?></div></main>
<?php require __DIR__.'/../includes/footer.php'; ?>
