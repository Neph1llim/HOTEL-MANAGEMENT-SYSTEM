<?php require 'config.php';
if(empty($_SESSION['admin'])){http_response_code(401);exit;}
$s=$pdo->prepare('SELECT proof FROM payments WHERE id=?');$s->execute([$_GET['id']??0]);$p=$s->fetch();
$f='uploads/'.basename($p['proof']??'');
if(!$p||!is_file($f)){http_response_code(404);exit;}
header('Content-Type: '.getimagesize($f)['mime']);readfile($f);
