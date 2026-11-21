<?php require '../config.php'; $u=me(); if(!$u) json_out(['ok'=>false,'error'=>'Please log in.']);
json_out(compute_quote((int)($_GET['room']??0),$_GET['in']??'',$_GET['out']??'',$_GET['guests']??0,(int)($_GET['voucher']??0),$u));
