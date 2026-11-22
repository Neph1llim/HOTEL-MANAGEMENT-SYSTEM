<?php
require 'config.php';
$a=$_POST['a']??'';
function P($k){return trim($_POST[$k]??'');}
function guest($pdo){if(empty($_SESSION['gid']))return false;$s=$pdo->prepare('SELECT * FROM guests WHERE id=?');$s->execute([$_SESSION['gid']]);return $s->fetch();}
function adm(){if(empty($_SESSION['admin']))j(['error'=>'Login required'],401);}
try{switch($a){
case 'search':
 $g=guest($pdo);$out=[];
 foreach($pdo->query('SELECT * FROM rooms')->fetchAll() as $r){
  $q=quote($pdo,$r['id'],P('ci'),P('co'),(int)P('pax'),P('voucher')?:null,$g['id']??null);
  if(!empty($q['fatal']))j($q,422);
  $q['room']=$r;$q['free']=!isset($q['error'])&&avail($pdo,$r['id'],P('ci'),P('co'));$out[]=$q;}
 j(['rooms'=>$out]);
case 'book':
 if(!P('agree'))j(['error'=>'Please accept the Terms & Conditions'],422);
 $g=guest($pdo);if(!$g)j(['error'=>'Please log in to book'],401);
 $pdo->beginTransaction();
 $pdo->prepare('SELECT id FROM rooms WHERE id=? FOR UPDATE')->execute([P('room')]);
 $gid=$g['id'];
 $q=quote($pdo,(int)P('room'),P('ci'),P('co'),(int)P('pax'),($g&&P('voucher'))?P('voucher'):null,$g['id']??null);
 if(isset($q['error'])||!avail($pdo,P('room'),P('ci'),P('co'))){$pdo->rollBack();j(['error'=>$q['error']??'Sorry, this room was just booked'],409);}
 $ref='BV'.strtoupper(bin2hex(random_bytes(3)));$vid=($g&&P('voucher'))?P('voucher'):null;
 $pdo->prepare('INSERT INTO reservations(ref,guest_id,room_id,checkin,checkout,pax,extra_fee,discount,total,downpayment,voucher_id) VALUES(?,?,?,?,?,?,?,?,?,?,?)')->execute([$ref,$gid,P('room'),P('ci'),P('co'),(int)P('pax'),$q['extra'],$q['disc'],$q['total'],$q['down'],$vid]);
 if($vid)$pdo->prepare('UPDATE vouchers SET status="used" WHERE id=?')->execute([$vid]);
 $pdo->commit();j(['ref'=>$ref,'total'=>$q['total'],'down'=>$q['down']]);
case 'pay':
 $g=guest($pdo);$s=$pdo->prepare('SELECT * FROM reservations WHERE ref=? AND guest_id=?');$s->execute([P('ref'),$g['id']??0]);$r=$s->fetch();
 if(!$r||$r['status']!='pending')j(['error'=>'Reservation not found or not awaiting payment'],404);
 if(!P('ref_no')||(float)P('amount')<=0)j(['error'=>'Enter the payment reference number and amount paid'],422);
 $f=$_FILES['proof']??null;
 if(!$f||$f['error']||$f['size']>3000000||!($i=@getimagesize($f['tmp_name']))||!in_array($i[2],[IMAGETYPE_JPEG,IMAGETYPE_PNG,IMAGETYPE_WEBP]))j(['error'=>'Upload a JPG, PNG or WEBP image under 3 MB'],422);
 $fn=bin2hex(random_bytes(12)).image_type_to_extension($i[2]);
 if(!is_dir('uploads'))mkdir('uploads');
 move_uploaded_file($f['tmp_name'],"uploads/$fn");
 $pdo->prepare('INSERT INTO payments(reservation_id,amount,ref_no,proof) VALUES(?,?,?,?)')->execute([$r['id'],(float)P('amount'),P('ref_no'),$fn]);
 j(['ok'=>1]);
case 'pay_status':
 adm();$n=P('s');if(!in_array($n,['verified','rejected']))j(['error'=>'Invalid change'],422);
 $pdo->beginTransaction();
 $s=$pdo->prepare('SELECT * FROM payments WHERE id=? FOR UPDATE');$s->execute([P('id')]);$p=$s->fetch();
 if(!$p||$p['status']!='pending'){$pdo->rollBack();j(['error'=>'Payment already reviewed'],422);}
 $pdo->prepare('UPDATE payments SET status=? WHERE id=?')->execute([$n,$p['id']]);
 if($n=='verified')$pdo->prepare('UPDATE reservations SET status="approved" WHERE id=? AND status="pending"')->execute([$p['reservation_id']]);
 $pdo->commit();j(['ok'=>1]);
case 'register':
 $pw=$_POST['pass']??'';
 if(!P('name')||!filter_var(P('email'),FILTER_VALIDATE_EMAIL)||strlen(P('phone'))<7||strlen($pw)<6)j(['error'=>'Fill in every field. The password needs at least 6 characters'],422);
 $s=$pdo->prepare('SELECT id FROM guests WHERE email=?');$s->execute([P('email')]);if($s->fetch())j(['error'=>'That email is already registered'],409);
 $pdo->prepare('INSERT INTO guests(name,email,phone,pass) VALUES(?,?,?,?)')->execute([P('name'),P('email'),P('phone'),password_hash($pw,PASSWORD_DEFAULT)]);
 session_regenerate_id(true);$_SESSION['gid']=$pdo->lastInsertId();j(['ok'=>1]);
case 'glogin':
 $s=$pdo->prepare('SELECT * FROM guests WHERE email=?');$s->execute([P('email')]);$u=$s->fetch();
 if(!$u||!$u['pass']||!password_verify($_POST['pass']??'',$u['pass']))j(['error'=>'Wrong email or password'],401);
 session_regenerate_id(true);$_SESSION['gid']=$u['id'];j(['ok'=>1]);
case 'glogout':unset($_SESSION['gid']);j(['ok'=>1]);
case 'profile':
 $g=guest($pdo);if(!$g)j(['error'=>'Please log in'],401);
 if(!P('name')||strlen(P('phone'))<7)j(['error'=>'Enter a valid name and mobile number'],422);
 $pdo->prepare('UPDATE guests SET name=?,phone=? WHERE id=?')->execute([P('name'),P('phone'),$g['id']]);
 $np=$_POST['new_pass']??'';
 if($np!==''){if(!password_verify($_POST['cur_pass']??'',$g['pass']))j(['error'=>'Current password is wrong'],422);if(strlen($np)<6)j(['error'=>'New password needs at least 6 characters'],422);
  $pdo->prepare('UPDATE guests SET pass=? WHERE id=?')->execute([password_hash($np,PASSWORD_DEFAULT),$g['id']]);}
 j(['ok'=>1]);
case 'mybookings':
 $g=guest($pdo);if(!$g)j(['error'=>'Please log in'],401);
 $s=$pdo->prepare('SELECT r.ref,r.checkin,r.checkout,r.pax,r.total,r.downpayment,r.status,m.name room,(SELECT p.status FROM payments p WHERE p.reservation_id=r.id ORDER BY p.id DESC LIMIT 1) pay FROM reservations r JOIN rooms m ON m.id=r.room_id WHERE r.guest_id=? ORDER BY r.id DESC');$s->execute([$g['id']]);
 $rows=$s->fetchAll();foreach($rows as &$x){$c=cancelInfo($x);$x['can_cancel']=$c['ok'];$x['refundable']=$c['refundable'];}unset($x);
 j(['res'=>$rows]);
case 'cancel_booking':
 $g=guest($pdo);if(!$g)j(['error'=>'Please log in'],401);
 $pdo->beginTransaction();
 $s=$pdo->prepare('SELECT * FROM reservations WHERE ref=? AND guest_id=? FOR UPDATE');$s->execute([P('ref'),$g['id']]);$r=$s->fetch();
 if(!$r){$pdo->rollBack();j(['error'=>'Reservation not found'],404);}
 $c=cancelInfo($r);
 if(!$c['ok']){$pdo->rollBack();j(['error'=>'This reservation can no longer be cancelled online. Please contact the hotel.'],422);}
 $pdo->prepare('UPDATE reservations SET status="cancelled" WHERE id=?')->execute([$r['id']]);
 /* the voucher goes back to the guest only when the cancellation is free (3+ days before check-in) */
 if($r['voucher_id']&&$c['refundable'])$pdo->prepare('UPDATE vouchers SET status="unused" WHERE id=? AND guest_id=?')->execute([$r['voucher_id'],$g['id']]);
 $pdo->commit();j(['ok'=>1,'refundable'=>$c['refundable']]);
case 'lookup':
 $g=guest($pdo);if(!$g)j(['error'=>'Please log in'],401);
 $v=$pdo->prepare('SELECT id,prize FROM vouchers WHERE guest_id=? AND status="unused"');$v->execute([$g['id']]);
 j(['guest'=>['name'=>$g['name'],'email'=>$g['email'],'phone'=>$g['phone'],'points'=>(int)$g['points'],'vip'=>(int)$g['vip']],'vouchers'=>$v->fetchAll()]);
case 'spin':
 $g=guest($pdo);if(!$g)j(['error'=>'Please log in'],401);
 $pdo->beginTransaction();
 $s=$pdo->prepare('SELECT points FROM guests WHERE id=? FOR UPDATE');$s->execute([$g['id']]);$pts=(int)$s->fetch()['points'];
 if($pts<SPIN_COST){$pdo->rollBack();j(['error'=>'You need '.SPIN_COST.' points to spin'],422);}
 $x=random_int(1,10000);$c=0;$i=0;foreach(PRIZES as $k=>$p){$c+=$p[3];if($x<=$c){$i=$k;break;}}
 $p=PRIZES[$i];
 $pdo->prepare('UPDATE guests SET points=points-?'.($p[1]=='vip'?',vip=1':'').' WHERE id=?')->execute([SPIN_COST,$g['id']]);
 if($p[1]!='vip')$pdo->prepare('INSERT INTO vouchers(guest_id,prize,kind,pct) VALUES(?,?,?,?)')->execute([$g['id'],$p[0],$p[1],$p[2]]);
 $pdo->commit();j(['index'=>$i,'prize'=>$p[0],'points'=>$pts-SPIN_COST]);
case 'login':
 $s=$pdo->prepare('SELECT * FROM admins WHERE username=?');$s->execute([P('user')]);$u=$s->fetch();
 if(!$u||!password_verify($_POST['pass']??'',$u['pass']))j(['error'=>'Wrong username or password'],401);
 $_SESSION['admin']=$u['id'];j(['ok'=>1]);
case 'logout':session_destroy();j(['ok'=>1]);
case 'dash':
 adm();
 $st=$pdo->query('SELECT SUM(status="pending") pending,SUM(status="approved") approved,SUM(status="completed") completed,SUM(status="cancelled") cancelled,COALESCE(SUM(IF(status IN("approved","completed"),total,0)),0) revenue,COUNT(*) bookings FROM reservations')->fetch();
 $bt=$pdo->query('SELECT m.type,COUNT(*) n,SUM(r.total) rev FROM reservations r JOIN rooms m ON m.id=r.room_id WHERE r.status IN("approved","completed") GROUP BY m.type')->fetchAll();
 $rooms=$pdo->query('SELECT m.name,m.type,(SELECT COUNT(*) FROM reservations r WHERE r.room_id=m.id AND r.status="approved" AND r.checkin<=CURDATE() AND r.checkout>CURDATE()) occ,(SELECT COUNT(*) FROM reservations r WHERE r.room_id=m.id AND r.status IN("pending","approved") AND r.checkin>CURDATE()) upcoming FROM rooms m')->fetchAll();
 $rs=$pdo->query('SELECT r.id,r.ref,r.checkin,r.checkout,r.pax,r.total,r.downpayment,r.status,g.name guest,g.email,m.name room FROM reservations r JOIN guests g ON g.id=r.guest_id JOIN rooms m ON m.id=r.room_id ORDER BY r.id DESC LIMIT 100')->fetchAll();
 $pays=$pdo->query('SELECT p.id,p.ref_no,p.amount,p.status,p.created_at,r.ref,r.downpayment,g.name guest FROM payments p JOIN reservations r ON r.id=p.reservation_id JOIN guests g ON g.id=r.guest_id ORDER BY p.status="pending" DESC,p.id DESC LIMIT 100')->fetchAll();
 j(['stats'=>$st,'byType'=>$bt,'rooms'=>$rooms,'res'=>$rs,'pays'=>$pays]);
case 'status':
 adm();
 $s=$pdo->prepare('SELECT * FROM reservations WHERE id=?');$s->execute([P('id')]);$r=$s->fetch();$n=P('s');
 if(!$r||$r['status']=='cancelled'||!in_array($n,['pending','approved','cancelled','completed']))j(['error'=>'Invalid change'],422);
 $pdo->beginTransaction();
 $pdo->prepare('UPDATE reservations SET status=? WHERE id=?')->execute([$n,$r['id']]);
 $pts=(int)floor($r['total']*0.01);
 if($n=='completed'&&$r['status']!='completed')$pdo->prepare('UPDATE guests SET points=points+? WHERE id=?')->execute([$pts,$r['guest_id']]);
 if($r['status']=='completed'&&$n!='completed')$pdo->prepare('UPDATE guests SET points=GREATEST(points-?,0) WHERE id=?')->execute([$pts,$r['guest_id']]);
 if($n=='cancelled'&&$r['voucher_id'])$pdo->prepare('UPDATE vouchers SET status="unused" WHERE id=?')->execute([$r['voucher_id']]);
 $pdo->commit();j(['ok'=>1]);
default:j(['error'=>'Unknown action'],400);
}}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();j(['error'=>'Server error: '.$e->getMessage()],500);}
