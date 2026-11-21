<?php require '../config.php'; if(!me()) json_out(['ok'=>false]);
$r=current_qr(); json_out($r ? ['ok'=>true,'path'=>base().$r['path'],'date'=>date('F j, Y',strtotime($r['qr_date'])),'v'=>$r['path']] : ['ok'=>false]);
