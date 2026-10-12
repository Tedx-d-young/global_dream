<?php
require __DIR__.'/../includes/bootstrap.php';require_admin();
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);$editing=$id && $id>0;
$fields=['title','provider','country','study_level','funding_type','deadline','summary','description','eligibility','documents','official_url','status'];
$data=array_fill_keys($fields,'');$data['status']='draft';$data['funding_type']='Fully funded';
if($editing){$st=$pdo->prepare("SELECT * FROM scholarships WHERE id=?");$st->execute([$id]);$record=$st->fetch();if(!$record){http_response_code(404);exit('Post not found');}foreach($fields as $f)$data[$f]=$record[$f]??'';}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();foreach($fields as $f)$data[$f]=trim((string)($_POST[$f]??''));
 if($data['title']===''||$data['provider']===''||$data['summary']==='')$error='Title, provider, and summary are required.';
 elseif($data['official_url']!==''&&!valid_http_url($data['official_url']))$error='Enter a valid http or https official URL.';
 elseif(!in_array($data['status'],['draft','published','archived'],true))$error='Invalid status.';
 elseif(!in_array($data['funding_type'],['Fully funded','Tuition waiver','Partial funding','Other'],true))$error='Invalid funding type.';
 else{
  $data['deadline']=$data['deadline']!==''?$data['deadline']:null;
  $cols=['title','provider','country','study_level','funding_type','deadline','summary','description','eligibility','documents','official_url','status'];
  if($editing){$sets=implode(',',array_map(fn($f)=>"$f=:$f",$cols));$sql="UPDATE scholarships SET $sets,updated_at=NOW() WHERE id=:id";}else{$names=implode(',',$cols);$binds=implode(',',array_map(fn($f)=>":$f",$cols));$sql="INSERT INTO scholarships ($names,created_at,updated_at) VALUES ($binds,NOW(),NOW())";}
  $st=$pdo->prepare($sql);foreach($cols as $f)$st->bindValue(':'.$f,$data[$f]!==''?$data[$f]:null);if($editing)$st->bindValue(':id',$id,PDO::PARAM_INT);$st->execute();flash('Scholarship saved.');header('Location: dashboard.php');exit;
 }
}
$pageTitle=$editing?'Edit scholarship':'Add scholarship';$adminArea=true;require __DIR__.'/../includes/header.php';
?>
<main class="section admin-page"><a class="text-link" href="dashboard.php">← Dashboard</a><div class="form-heading"><span class="eyebrow">SCHOLARSHIP CONTENT</span><h1><?= $editing?'Edit scholarship':'Add scholarship' ?></h1><p>Confirm the official page and verify the information before publishing.</p></div><form class="editor-form" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?><div class="form-grid">
<label>Title *<input name="title" required value="<?= e($data['title']) ?>"></label><label>Provider / university *<input name="provider" required value="<?= e($data['provider']) ?>"></label>
<label>Country / region<input name="country" value="<?= e($data['country']) ?>"></label><label>Study level<select name="study_level"><option value="">Choose level</option><?php foreach(['Undergraduate','Master’s','PhD','Postdoctoral','Short course','Various levels'] as $v): ?><option <?= $data['study_level']===$v?'selected':'' ?>><?= e($v) ?></option><?php endforeach; ?></select></label>
<label>Funding type<select name="funding_type"><?php foreach(['Fully funded','Tuition waiver','Partial funding','Other'] as $v): ?><option <?= $data['funding_type']===$v?'selected':'' ?>><?= e($v) ?></option><?php endforeach; ?></select></label><label>Deadline<input type="date" name="deadline" value="<?= e($data['deadline']) ?>"></label>
<label class="full">Summary *<textarea name="summary" rows="3" required><?= e($data['summary']) ?></textarea></label><label class="full">Description<textarea name="description" rows="5"><?= e($data['description']) ?></textarea></label><label class="full">Eligibility<textarea name="eligibility" rows="4"><?= e($data['eligibility']) ?></textarea></label><label class="full">Documents to check<textarea name="documents" rows="3"><?= e($data['documents']) ?></textarea></label><label class="full">Official URL<input type="url" name="official_url" value="<?= e($data['official_url']) ?>" placeholder="https://..."></label><label>Status<select name="status"><?php foreach(['draft','published','archived'] as $v): ?><option value="<?= $v ?>" <?= $data['status']===$v?'selected':'' ?>><?= ucfirst($v) ?></option><?php endforeach; ?></select></label>
</div><div class="form-actions"><button class="button" type="submit">Save scholarship →</button><a class="button button-outline" href="dashboard.php">Cancel</a></div></form>
<?php if($editing): ?><form method="post" action="delete.php" class="delete-form" onsubmit="return confirm('Permanently delete this scholarship?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$id ?>"><button class="danger-button" type="submit">Delete this post</button></form><?php endif; ?></main>
<?php require __DIR__.'/../includes/footer.php'; ?>
