<?php require '../config.php'; $u=me(); if(!$u||$u['role']!=='user'||$_SERVER['REQUEST_METHOD']!=='POST') json_out(['ok'=>false,'error'=>'Not allowed.']); check_csrf();
// Prize table. Order matches the wheel slices in app.js. Weights are out of 1000 (50%, 30%, 12.5%, 5%, 2.4%, 0.1%).
$prizes=[[500,'discount','20% discount voucher',20],[300,'discount','50% discount voucher',50],[125,'discount','75% discount voucher',75],
         [50,'free_standard','Free Standard Room booking (3 days, 2 nights)',0],[24,'free_family','Free Family Room booking (3 days, 2 nights)',0],[1,'vip','VIP status',0]];
$pdo->beginTransaction();
$row=one("SELECT points,is_vip FROM users WHERE id=? FOR UPDATE",[$u['id']]);
if($row['points']<SPIN_COST){ $pdo->rollBack(); json_out(['ok'=>false,'error'=>'You need '.SPIN_COST.' points to spin.']); }
$roll=random_int(1,1000); $acc=0; $idx=0;
foreach($prizes as $i=>$p){ $acc+=$p[0]; if($roll<=$acc){ $idx=$i; break; } }
$p=$prizes[$idx]; $cost=SPIN_COST; $msg='You won: '.$p[2].'.';
if($p[1]==='vip'){
  if($row['is_vip']){ $cost=0; $msg='You rolled VIP, but you already have it. Your 100 points were refunded.'; }
  else q("UPDATE users SET is_vip=1 WHERE id=?",[$u['id']]);
} else {
  q("INSERT INTO vouchers(user_id,kind,label,pct) VALUES(?,?,?,?)",[$u['id'],$p[1],$p[2],$p[3]]);
  if($idx===2){ q("INSERT INTO vouchers(user_id,kind,label) VALUES(?,'meal','Free meal stubs: breakfast, lunch and dinner for 2 days')",[$u['id']]); $msg.=' Plus free meal stubs (breakfast, lunch, dinner) for 2 days.'; }
}
q("UPDATE users SET points=points-? WHERE id=?",[$cost,$u['id']]);
$pdo->commit();
json_out(['ok'=>true,'index'=>$idx,'message'=>$msg,'points'=>(int)one("SELECT points FROM users WHERE id=?",[$u['id']])['points']]);
