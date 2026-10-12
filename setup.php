<?php
require __DIR__.'/includes/bootstrap.php';
$count=(int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
if($count>0){http_response_code(403);exit('Setup is disabled because an administrator already exists. Delete setup.php after installation.');}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$name=trim($_POST['name']??'');$email=filter_var(trim($_POST['email']??''),FILTER_VALIDATE_EMAIL);$password=(string)($_POST['password']??'');
 if(!$name||!$email||strlen($password)<12)$error='Enter a name, valid email, and password of at least 12 characters.';
 else{$st=$pdo->prepare('INSERT INTO admins(name,email,password_hash,active,created_at) VALUES(?,?,?,1,NOW())');$st->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);session_regenerate_id(true);$_SESSION['admin_id']=(int)$pdo->lastInsertId();$_SESSION['admin_name']=$name;header('Location: admin/dashboard.php');exit;}
}
$pageTitle='Initial setup';require __DIR__.'/includes/header.php';
?>
<main class="auth-wrap"><form class="auth-card" method="post"><span class="eyebrow">ONE-TIME SETUP</span><h1>Create admin account</h1><p>This page stops working after the first administrator is created.</p><?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Your name<input name="name" required></label><label>Email<input type="email" name="email" required></label><label>Password (minimum 12 characters)<input type="password" name="password" minlength="12" required></label><button class="button" type="submit">Create admin →</button></form></main>
<?php require __DIR__.'/includes/footer.php'; ?>
