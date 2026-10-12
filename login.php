<?php
require __DIR__.'/../includes/bootstrap.php';
if(!empty($_SESSION['admin_id'])){header('Location: dashboard.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $email=filter_var(trim($_POST['email']??''),FILTER_VALIDATE_EMAIL);$password=(string)($_POST['password']??'');
 if($email && $password){
  $st=$pdo->prepare("SELECT id,name,email,password_hash,active FROM admins WHERE email=? LIMIT 1");$st->execute([$email]);$a=$st->fetch();
  if($a && (int)$a['active']===1 && password_verify($password,$a['password_hash'])){
   session_regenerate_id(true);$_SESSION['admin_id']=(int)$a['id'];$_SESSION['admin_name']=$a['name'];
   $pdo->prepare("UPDATE admins SET last_login_at=NOW() WHERE id=?")->execute([$a['id']]);
   header('Location: dashboard.php');exit;
  }
 }
 usleep(300000);$error='Email or password is incorrect.';
}
$pageTitle='Admin login';$adminArea=true;require __DIR__.'/../includes/header.php';
?>
<main class="auth-wrap"><form class="auth-card" method="post"><span class="eyebrow">CONTENT MANAGEMENT</span><h1>Admin sign in</h1><p>Manage scholarship posts and discovery sources.</p><?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Email<input type="email" name="email" autocomplete="username" required></label><label>Password<input type="password" name="password" autocomplete="current-password" required></label><button class="button" type="submit">Sign in →</button><p class="muted">First installation? Use the one-time setup page at <a href="../setup.php">setup.php</a>.</p></form></main>
<?php require __DIR__.'/../includes/footer.php'; ?>
