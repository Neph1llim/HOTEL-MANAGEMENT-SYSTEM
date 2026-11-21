<?php require '../config.php'; if(!me()) json_out(['ok'=>false]);
$in=$_GET['in']??''; $out=$_GET['out']??''; $res=[];
$d1=DateTime::createFromFormat('!Y-m-d',$in); $d2=DateTime::createFromFormat('!Y-m-d',$out);
if(!$d1||!$d2||$d2<=$d1) json_out(['ok'=>false,'rooms'=>new stdClass]);
foreach(all("SELECT id FROM rooms") as $r) $res[$r['id']]=is_free($r['id'],$in,$out);
json_out(['ok'=>true,'rooms'=>$res]);
