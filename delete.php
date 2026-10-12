<?php
require __DIR__.'/../includes/bootstrap.php';require_admin();
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Method not allowed');}
verify_csrf();$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);if(!$id){flash('Invalid post ID.','error');header('Location: dashboard.php');exit;}
$st=$pdo->prepare('DELETE FROM scholarships WHERE id=?');$st->execute([$id]);flash('Scholarship deleted.');header('Location: dashboard.php');exit;
