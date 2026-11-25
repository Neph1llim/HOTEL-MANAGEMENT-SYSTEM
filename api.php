<?php
require 'config.php';
$a=$_POST['a']??'';
function P($k){return trim($_POST[$k]??'');}
function guest($pdo){if(empty($_SESSION['gid']))return false;$s=$pdo->prepare('SELECT * FROM guests WHERE id=?');$s->execute([$_SESSION['gid']]);return $s->fetch();}
function adm(){if(empty($_SESSION['admin']))j(['error'=>'Login required'],401);}
function resById($pdo,$id,$lock=false){$s=$pdo->prepare('SELECT * FROM reservations WHERE id=?'.($lock?' FOR UPDATE':''));$s->execute([$id]);return $s->fetch();}
/* a voucher can be given as its id or as its code (VCH-<id>); returns the id or null */
function vid($v){$v=strtoupper(trim((string)$v));if($v==='')return null;if(preg_match('/^(?:VCH-?)?0*(\d+)$/',$v,$m))return (int)$m[1];return -1;}
/* validates the room form fields shared by room_save and room_add; returns an error text or null */
function roomErr($pdo,$id,$d=null,$skip=[]){
 $d=$d??['name'=>P('name'),'rate'=>P('rate'),'base_cap'=>P('base_cap'),'max_cap'=>P('max_cap')];
 $n=trim((string)($d['name']??''));$rate=(int)($d['rate']??0);$b=(int)($d['base_cap']??0);$m=(int)($d['max_cap']??0);
 if($n===''||strlen($n)>30)return 'Enter a room name (up to 30 characters)';
 if($rate<1)return 'The rate must be at least ₱1';
 if($b<1||$m<$b)return 'Capacities must be at least 1, and the maximum cannot be lower than the standard capacity';
 /* rooms the caller is saving together with this one are checked against each other by the caller */
 $ex=implode(',',array_map('intval',array_merge([$id],$skip)));
 $s=$pdo->prepare("SELECT id FROM rooms WHERE LOWER(name)=LOWER(?) AND deleted=0 AND id NOT IN($ex)");$s->execute([$n]);
 return $s->fetch()?'Another room already has that name':null;
}
try{switch($a){
case 'search':
 $g=guest($pdo);$out=[];
 foreach($pdo->query('SELECT * FROM rooms WHERE active=1 ORDER BY id')->fetchAll() as $r){
  $q=quote($pdo,$r['id'],P('ci'),P('co'),(int)P('pax'),vid(P('voucher')),$g['id']??null);
  if(!empty($q['fatal']))j($q,422);
  $q['room']=$r;$q['free']=avail($pdo,$r['id'],P('ci'),P('co'));$out[]=$q;}
 j(['rooms'=>$out]);
case 'quote': /* live price for one room: used by the Make Reservation summary, updates as dates, guests or voucher change */
 $g=guest($pdo);$vid=vid(P('voucher'));
 $q=quote($pdo,(int)P('room'),P('ci'),P('co'),(int)P('pax'),$vid,$g['id']??null);
 if($vid&&!$g)$q=['error'=>'Log in to use a voucher']+(isset($q['nights'])?$q:[]);
 $q['free']=empty($q['fatal'])&&avail($pdo,(int)P('room'),P('ci'),P('co'));
 $q['voucher_ok']=$vid&&!isset($q['error']);
 j($q);
case 'calendar': /* public: which nights are taken, for the availability calendar on the booking page */
 $m=preg_match('/^\d{4}-(0[1-9]|1[0-2])$/',P('month'))?P('month'):date('Y-m');
 $first="$m-01";$next=date('Y-m-d',strtotime("$first +1 month"));
 $s=$pdo->prepare('SELECT room_id,checkin,checkout FROM reservations WHERE status IN("pending","approved") AND checkin<? AND checkout>?');$s->execute([$next,$first]);
 j(['month'=>$m,'today'=>date('Y-m-d'),'rooms'=>$pdo->query('SELECT id,name,type FROM rooms WHERE active=1 ORDER BY id')->fetchAll(),'bookings'=>$s->fetchAll()]);
case 'book':
 if(!P('agree'))j(['error'=>'Please accept the Terms & Conditions'],422);
 $g=guest($pdo);if(!$g)j(['error'=>'Please log in to book'],401);
 $pdo->beginTransaction();
 $pdo->prepare('SELECT id FROM rooms WHERE id=? FOR UPDATE')->execute([P('room')]);
 $gid=$g['id'];
 $vid=vid(P('voucher'));$q=quote($pdo,(int)P('room'),P('ci'),P('co'),(int)P('pax'),$vid,$gid);
 if(isset($q['error'])||!avail($pdo,P('room'),P('ci'),P('co'))){$pdo->rollBack();j(['error'=>$q['error']??'Sorry, this room was just booked'],409);}
 $ref='BV'.strtoupper(bin2hex(random_bytes(3)));
 $pdo->prepare('INSERT INTO reservations(ref,guest_id,room_id,checkin,checkout,pax,extra_fee,discount,total,downpayment,voucher_id) VALUES(?,?,?,?,?,?,?,?,?,?,?)')->execute([$ref,$gid,P('room'),P('ci'),P('co'),(int)P('pax'),$q['extra'],$q['disc'],$q['total'],$q['down'],$vid]);
 if($vid)$pdo->prepare('UPDATE vouchers SET status="used" WHERE id=?')->execute([$vid]);
 notify($pdo,$gid,"Reservation $ref received. Please pay the downpayment of ".peso($q['down'])." and upload your proof of payment within ".HOLD_HOURS." hours, or the room will be released.");
 $pdo->commit();j(['ref'=>$ref,'total'=>$q['total'],'down'=>$q['down'],'hold_hours'=>HOLD_HOURS]);
case 'pay':
 $g=guest($pdo);$s=$pdo->prepare('SELECT * FROM reservations WHERE ref=? AND guest_id=?');$s->execute([P('ref'),$g['id']??0]);$r=$s->fetch();
 if($r&&$r['status']=='cancelled'&&$r['cancel_reason']=='expired')j(['error'=>'This reservation expired because no payment was received in time. Please make a new reservation.'],422);
 if(!$r||$r['status']!='pending')j(['error'=>'Reservation not found or not awaiting payment'],404);
 $s=$pdo->prepare('SELECT COUNT(*) c FROM payments WHERE reservation_id=? AND status="pending"');$s->execute([$r['id']]);
 if($s->fetch()['c'])j(['error'=>'Your payment is already waiting to be verified'],422);
 if(!P('ref_no')||(float)P('amount')<=0)j(['error'=>'Enter the payment reference number and amount paid'],422);
 $f=$_FILES['proof']??null;
 if(!$f||$f['error']||$f['size']>3000000||!preg_match('/\.(png|jpe?g)$/i',$f['name'])||!($i=@getimagesize($f['tmp_name']))||!in_array($i[2],[IMAGETYPE_JPEG,IMAGETYPE_PNG],true))j(['error'=>'Upload a PNG or JPG/JPEG image under 3 MB'],422);
 $fn=bin2hex(random_bytes(12)).image_type_to_extension($i[2]);
 if(!is_dir('uploads'))mkdir('uploads');
 move_uploaded_file($f['tmp_name'],"uploads/$fn");
 $pdo->prepare('INSERT INTO payments(reservation_id,amount,ref_no,proof) VALUES(?,?,?,?)')->execute([$r['id'],(float)P('amount'),P('ref_no'),$fn]);
 notify($pdo,$r['guest_id'],"Payment proof for {$r['ref']} submitted. The hotel will verify it shortly.");
 j(['ok'=>1]);
case 'pay_status':
 adm();$n=P('s');if(!in_array($n,['verified','rejected']))j(['error'=>'Invalid change'],422);
 $pdo->beginTransaction();
 $s=$pdo->prepare('SELECT * FROM payments WHERE id=? FOR UPDATE');$s->execute([P('id')]);$p=$s->fetch();
 if(!$p||$p['status']!='pending'){$pdo->rollBack();j(['error'=>'Payment already reviewed'],422);}
 $pdo->prepare('UPDATE payments SET status=? WHERE id=?')->execute([$n,$p['id']]);
 $r=resById($pdo,$p['reservation_id'],true);
 if($n=='verified'){
  $paid=paidSum($pdo,$r['id']);
  $up=$pdo->prepare('UPDATE reservations SET status="approved" WHERE id=? AND status="pending"');$up->execute([$r['id']]);
  if($up->rowCount())notify($pdo,$r['guest_id'],"Payment verified. Reservation {$r['ref']} is approved. The balance of ".peso(balanceDue($r,$paid))." is payable at check-in.");
  /* proof that was already pending when the reservation got cancelled: the money was received, so queue it for refund */
  elseif($r['status']=='cancelled'&&$r['refund_status']!='refunded')$pdo->prepare('UPDATE reservations SET refund_status="pending",refund_amount=? WHERE id=?')->execute([$paid,$r['id']]);
 }elseif($r['status']=='pending')
  notify($pdo,$r['guest_id'],"Your payment proof for {$r['ref']} was rejected. Please upload a clear proof of payment within ".HOLD_HOURS." hours, or the room will be released.");
 $pdo->commit();j(['ok'=>1]);
case 'pay_revert': /* put a verified or rejected payment back to pending so it can be reviewed again */
 adm();
 $pdo->beginTransaction();
 $s=$pdo->prepare('SELECT * FROM payments WHERE id=? FOR UPDATE');$s->execute([P('id')]);$p=$s->fetch();
 if(!$p||!in_array($p['status'],['verified','rejected'])){$pdo->rollBack();j(['error'=>'Only a verified or rejected payment can be set back to pending'],422);}
 $r=resById($pdo,$p['reservation_id'],true);
 if($r['status']=='completed'){$pdo->rollBack();j(['error'=>'This reservation is already completed, so its payments can no longer be changed'],422);}
 if($r['refund_status']=='refunded'){$pdo->rollBack();j(['error'=>'A refund was already recorded for this reservation, so its payments can no longer be changed'],422);}
 $pdo->prepare('UPDATE payments SET status="pending" WHERE id=?')->execute([$p['id']]);
 if($p['status']=='verified'){
  $paid=paidSum($pdo,$r['id']); /* what is still verified without this payment */
  if($r['status']=='approved'&&$paid<=0)$pdo->prepare('UPDATE reservations SET status="pending" WHERE id=?')->execute([$r['id']]); /* it was approved only because of this payment */
  elseif($r['status']=='cancelled'&&$r['refund_status']=='pending'){
   if($paid>0)$pdo->prepare('UPDATE reservations SET refund_amount=LEAST(refund_amount,?) WHERE id=?')->execute([$paid,$r['id']]);
   else $pdo->prepare('UPDATE reservations SET refund_status="none",refund_amount=0 WHERE id=?')->execute([$r['id']]);
  }
 }
 notify($pdo,$r['guest_id'],"Your payment for reservation {$r['ref']} is back under review. The hotel will verify it shortly.");
 $pdo->commit();j(['ok'=>1]);
case 'register':
 $pw=$_POST['pass']??'';
 if(!P('name')||!filter_var(P('email'),FILTER_VALIDATE_EMAIL)||strlen(P('phone'))<7||strlen($pw)<6)j(['error'=>'Fill in every field. The password needs at least 6 characters'],422);
 $s=$pdo->prepare('SELECT id,pass FROM guests WHERE email=?');$s->execute([P('email')]);$old=$s->fetch();
 if($old&&$old['pass'])j(['error'=>'That email is already registered'],409);
 if($old){ /* a walk-in guest the hotel already recorded with this email: attach the new login to that record so points carry over */
  $pdo->prepare('UPDATE guests SET name=?,phone=?,pass=? WHERE id=?')->execute([P('name'),P('phone'),password_hash($pw,PASSWORD_DEFAULT),$old['id']]);$gid=$old['id'];
 }else{
  $pdo->prepare('INSERT INTO guests(name,email,phone,pass) VALUES(?,?,?,?)')->execute([P('name'),P('email'),P('phone'),password_hash($pw,PASSWORD_DEFAULT)]);$gid=$pdo->lastInsertId();}
 session_regenerate_id(true);$_SESSION['gid']=$gid;j(['ok'=>1]);
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
 $s=$pdo->prepare('SELECT r.ref,r.checkin,r.checkout,r.pax,r.total,r.downpayment,r.status,r.cancel_reason,r.refund_status,r.refund_amount,r.created_at,m.name room,
  (SELECT p.status FROM payments p WHERE p.reservation_id=r.id ORDER BY p.id DESC LIMIT 1) pay,
  (SELECT COALESCE(SUM(p.amount),0) FROM payments p WHERE p.reservation_id=r.id AND p.status="verified") paid,
  (SELECT MAX(p.created_at) FROM payments p WHERE p.reservation_id=r.id AND p.status="rejected") rej
  FROM reservations r JOIN rooms m ON m.id=r.room_id WHERE r.guest_id=? ORDER BY r.id DESC');$s->execute([$g['id']]);
 $rows=$s->fetchAll();foreach($rows as &$x){$c=cancelInfo($x);
  $x['can_cancel']=$c['ok']&&$x['pay']!='pending'; /* a proof under review has to be settled by the hotel first */
  $x['refundable']=$c['refundable'];
  $x['balance']=in_array($x['status'],['approved','completed'])?balanceDue($x,$x['paid']):0;
  $x['expires']=($x['status']=='pending'&&$x['pay']!='pending')?date('M j, g:i A',strtotime(max($x['created_at'],(string)$x['rej']))+HOLD_HOURS*3600):null;
  unset($x['rej'],$x['created_at']);}unset($x);
 j(['res'=>$rows]);
case 'cancel_booking':
 $g=guest($pdo);if(!$g)j(['error'=>'Please log in'],401);
 $pdo->beginTransaction();
 $s=$pdo->prepare('SELECT * FROM reservations WHERE ref=? AND guest_id=? FOR UPDATE');$s->execute([P('ref'),$g['id']]);$r=$s->fetch();
 if(!$r){$pdo->rollBack();j(['error'=>'Reservation not found'],404);}
 $c=cancelInfo($r);
 $s=$pdo->prepare('SELECT COUNT(*) c FROM payments WHERE reservation_id=? AND status="pending"');$s->execute([$r['id']]);
 if(!$c['ok']||$s->fetch()['c']){$pdo->rollBack();j(['error'=>'This reservation cannot be cancelled online right now. Please contact the hotel.'],422);}
 $refund=cancelRes($pdo,$r,'guest',$c['refundable']);
 $pdo->commit();j(['ok'=>1,'refundable'=>$c['refundable'],'refund'=>$refund]);
case 'notes':
 $g=guest($pdo);if(!$g)j(['error'=>'Please log in'],401);
 $s=$pdo->prepare('SELECT id,msg,is_read,created_at FROM notifications WHERE guest_id=? ORDER BY id DESC LIMIT 30');$s->execute([$g['id']]);
 j(['notes'=>$s->fetchAll()]);
case 'notes_read':
 $g=guest($pdo);if(!$g)j(['error'=>'Please log in'],401);
 $pdo->prepare('UPDATE notifications SET is_read=1 WHERE guest_id=?')->execute([$g['id']]);j(['ok'=>1]);
case 'lookup':
 $g=guest($pdo);if(!$g)j(['error'=>'Please log in'],401);
 $v=$pdo->prepare('SELECT id,prize,CONCAT("VCH-",id) code FROM vouchers WHERE guest_id=? AND status="unused"');$v->execute([$g['id']]);
 $u=$pdo->prepare('SELECT COUNT(*) c FROM notifications WHERE guest_id=? AND is_read=0');$u->execute([$g['id']]);
 j(['guest'=>['name'=>$g['name'],'email'=>$g['email'],'phone'=>$g['phone'],'points'=>(int)$g['points'],'vip'=>(int)$g['vip']],'vouchers'=>$v->fetchAll(),'unread'=>(int)$u->fetch()['c']]);
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

case 'mini_status': case 'mini_start': case 'mini_claim': /* daily minigames 'toss' and 'catch': 1 claim per game per day, max 5 points each */
 $g=guest($pdo);if(!$g)j(['error'=>'Please log in'],401);
 $s=$pdo->prepare('SELECT COUNT(*) FROM reservations WHERE guest_id=? AND status="completed"');$s->execute([$g['id']]);$ok=(int)$s->fetchColumn()>0;
 $s=$pdo->prepare('SELECT game,points FROM minigame_claims WHERE guest_id=? AND day=CURDATE()');$s->execute([$g['id']]);
 $done=['toss'=>0,'catch'=>0];foreach($s->fetchAll() as $x)$done[$x['game']]=(int)$x['points'];
 if($a=='mini_status')j(['eligible'=>$ok,'claimed'=>$done,'cap'=>5]);
 $gm=P('game');if(!isset($done[$gm]))j(['error'=>'Unknown game'],422);
 if(!$ok)j(['error'=>'Complete a stay to earn points from minigames'],403);
 if($done[$gm])j(['error'=>'You already claimed this game today. Come back tomorrow'],422);
 if($a=='mini_start'){$_SESSION['mini'][$gm]=microtime(true);j(['ok'=>1]);}
 /* mini_claim: a round must have been started and fully played (18 s) */
 $t=$_SESSION['mini'][$gm]??0;unset($_SESSION['mini'][$gm]);$el=microtime(true)-$t;
 if(!$t||$el<16||$el>600)j(['error'=>'Play a full round first'],422);
 $sc=max(0,min(60,(int)P('score')));
 $pts=min(5,$gm=='toss'?$sc:intdiv($sc,3)); /* toss: 1 point per basket. catch: 1 point per 3 score. both capped at 5 */
 if($pts<1)j(['error'=>'Score too low to earn points. Play again!'],422);
 $pdo->beginTransaction();
 $s=$pdo->prepare('SELECT points FROM guests WHERE id=? FOR UPDATE');$s->execute([$g['id']]);$have=(int)$s->fetch()['points'];
 $c=$pdo->prepare('SELECT 1 FROM minigame_claims WHERE guest_id=? AND game=? AND day=CURDATE()');$c->execute([$g['id'],$gm]);
 if($c->fetch()){$pdo->rollBack();j(['error'=>'You already claimed this game today. Come back tomorrow'],422);}
 $pdo->prepare('INSERT INTO minigame_claims(guest_id,game,day,points) VALUES(?,?,CURDATE(),?)')->execute([$g['id'],$gm,$pts]);
 $pdo->prepare('UPDATE guests SET points=points+? WHERE id=?')->execute([$pts,$g['id']]);
 $pdo->commit();j(['points'=>$pts,'total'=>$have+$pts]);

/* ================= staff ================= */
case 'login':
 $s=$pdo->prepare('SELECT * FROM admins WHERE username=?');$s->execute([P('user')]);$u=$s->fetch();
 if(!$u||!password_verify($_POST['pass']??'',$u['pass']))j(['error'=>'Wrong username or password'],401);
 $_SESSION['admin']=$u['id'];j(['ok'=>1]);
case 'logout':session_destroy();j(['ok'=>1]);
case 'dash':
 adm();
 /* Overview is scoped to one calendar month (default: the current one, never more than 1 month ahead).
    Reservations belong to the month they were booked in; payments to the month they were submitted/received in.
    Nothing is deleted when a month ends: the Payments and Reservations tabs always list everything. */
 $nowM=date('Y-m');$maxM=date('Y-m',strtotime(date('Y-m-01').' +1 month'));
 $month=preg_match('/^\d{4}-(0[1-9]|1[0-2])$/',P('month'))?P('month'):$nowM;
 if($month>$maxM)$month=$maxM;
 $ms="$month-01";$me=date('Y-m-d',strtotime("$ms +1 month"));
 $paidSub='(SELECT COALESCE(SUM(p.amount),0) FROM payments p WHERE p.reservation_id=r.id AND p.status="verified")';
 $q=$pdo->prepare('SELECT SUM(status="pending") pending,SUM(status="approved") approved,SUM(status="completed") completed,SUM(status="cancelled") cancelled,COUNT(*) bookings,
  SUM(refund_status="pending") refunds_n,COALESCE(SUM(IF(refund_status="pending",refund_amount,0)),0) refunds_amt FROM reservations WHERE created_at>=? AND created_at<?');
 $q->execute([$ms,$me]);$st=$q->fetch();
 /* revenue = money actually received this month (verified payments) minus refunds paid out this month */
 $q=$pdo->prepare('SELECT COALESCE(SUM(amount),0) s FROM payments WHERE status="verified" AND created_at>=? AND created_at<?');$q->execute([$ms,$me]);$recv=(float)$q->fetch()['s'];
 $q=$pdo->prepare('SELECT COALESCE(SUM(refund_amount),0) s FROM reservations WHERE refund_status="refunded" AND COALESCE(cancelled_at,created_at)>=? AND COALESCE(cancelled_at,created_at)<?');$q->execute([$ms,$me]);
 $st['revenue']=$recv-(float)$q->fetch()['s'];
 $q=$pdo->prepare('SELECT COUNT(*) c FROM payments WHERE status="pending" AND created_at>=? AND created_at<?');$q->execute([$ms,$me]);$st['pays_pending']=(int)$q->fetch()['c'];
 $q=$pdo->prepare("SELECT COALESCE(SUM(CASE WHEN r.total>$paidSub THEN r.total-$paidSub ELSE 0 END),0) s FROM reservations r WHERE r.status IN('approved','completed') AND r.created_at>=? AND r.created_at<?");$q->execute([$ms,$me]);
 $st['balance_due']=(float)$q->fetch()['s'];
 $q=$pdo->prepare('SELECT m.type,COUNT(*) n,SUM(r.total) rev FROM reservations r JOIN rooms m ON m.id=r.room_id WHERE r.status IN("approved","completed") AND r.created_at>=? AND r.created_at<? GROUP BY m.type');$q->execute([$ms,$me]);$bt=$q->fetchAll();
 $rooms=$pdo->query('SELECT m.*,(SELECT COUNT(*) FROM reservations r WHERE r.room_id=m.id AND r.status="approved" AND r.checkin<=CURDATE() AND r.checkout>CURDATE()) occ,(SELECT COUNT(*) FROM reservations r WHERE r.room_id=m.id AND r.status IN("pending","approved") AND r.checkout>CURDATE()) upcoming FROM rooms m WHERE m.deleted=0 ORDER BY m.id')->fetchAll();
 /* reservations: full list for the Reservations tab; Overview gets only this month's items that still need action
    (pending, approved, or cancelled with a refund still to pay out) */
 $sel="SELECT r.id,r.ref,r.checkin,r.checkout,r.pax,r.total,r.downpayment,r.status,r.source,r.cancel_reason,r.refund_status,r.refund_amount,r.refund_note,g.name guest,g.email,g.phone,m.name room,$paidSub paid FROM reservations r JOIN guests g ON g.id=r.guest_id JOIN rooms m ON m.id=r.room_id";
 /* search box on the Payments / Reservations pages: name, email, contact no. or reference no. */
 $qs=trim(P('q'));$lk='%'.addcslashes($qs,'%_\\').'%';
 $dig=preg_match('/^[\d\s\-+]+$/',$qs)?preg_replace('/\D/','',$qs):''; /* phone typed with spaces, dashes or +63 */
 $ph=strlen(preg_replace('/\D/','',$qs))>=3; /* contact numbers are searched from 3 digits, so "2" does not match every guest whose number happens to contain a 2 */
 $phoneSql='REPLACE(REPLACE(REPLACE(g.phone," ",""),"-",""),"+","")';
 $rw='';$rp=[];
 if($qs!==''){$rw=' WHERE (g.name LIKE ? OR g.email LIKE ? OR r.ref LIKE ?'.($ph?' OR g.phone LIKE ?':'').($ph&&$dig!==''?" OR $phoneSql LIKE ?":'').')';$rp=[$lk,$lk,$lk];if($ph)$rp[]=$lk;if($ph&&$dig!=='')$rp[]='%'.$dig.'%';}
 $sq=$pdo->prepare("$sel$rw ORDER BY r.id DESC LIMIT 500");$sq->execute($rp);$rs=$sq->fetchAll();
 $q=$pdo->prepare("$sel WHERE r.created_at>=? AND r.created_at<? AND (r.status IN('pending','approved') OR (r.status='cancelled' AND r.refund_status='pending')) ORDER BY r.checkin,r.id");$q->execute([$ms,$me]);$ovr=$q->fetchAll();
 foreach($rs as &$x)$x['balance']=in_array($x['status'],['approved','completed'])?balanceDue($x,$x['paid']):0;unset($x);
 foreach($ovr as &$x)$x['balance']=in_array($x['status'],['approved','completed'])?balanceDue($x,$x['paid']):0;unset($x);
 /* payments: full list for the Payments tab; Overview gets only this month's payments still waiting for review */
 $psel='SELECT p.id,p.ref_no,p.amount,p.status,p.kind,p.method,p.created_at,r.ref,r.downpayment,g.name guest,g.email,g.phone FROM payments p JOIN reservations r ON r.id=p.reservation_id JOIN guests g ON g.id=r.guest_id';
 $pw='';$pp=[];
 if($qs!==''){$pw=' WHERE (g.name LIKE ? OR g.email LIKE ? OR r.ref LIKE ? OR p.ref_no LIKE ?'.($ph?' OR g.phone LIKE ?':'').($ph&&$dig!==''?" OR $phoneSql LIKE ?":'').')';$pp=[$lk,$lk,$lk,$lk];if($ph)$pp[]=$lk;if($ph&&$dig!=='')$pp[]='%'.$dig.'%';}
 $sq=$pdo->prepare("$psel$pw ORDER BY p.status=\"pending\" DESC,p.id DESC LIMIT 500");$sq->execute($pp);$pays=$sq->fetchAll();
 $q=$pdo->prepare("$psel WHERE p.status='pending' AND p.created_at>=? AND p.created_at<? ORDER BY p.id DESC");$q->execute([$ms,$me]);$ovp=$q->fetchAll();
 /* revenue per calendar month for the selected year and the year before (Overview analytics) */
 $yr=(int)substr($month,0,4);
 $series=function($y)use($pdo){
  $a=array_fill(0,12,0.0);
  $q=$pdo->prepare('SELECT MONTH(created_at) m,COALESCE(SUM(amount),0) s FROM payments WHERE status="verified" AND created_at>=? AND created_at<? GROUP BY MONTH(created_at)');
  $q->execute(["$y-01-01",($y+1).'-01-01']);
  foreach($q->fetchAll() as $x)$a[(int)$x['m']-1]+=(float)$x['s'];
  $q=$pdo->prepare('SELECT MONTH(COALESCE(cancelled_at,created_at)) m,COALESCE(SUM(refund_amount),0) s FROM reservations WHERE refund_status="refunded" AND COALESCE(cancelled_at,created_at)>=? AND COALESCE(cancelled_at,created_at)<? GROUP BY MONTH(COALESCE(cancelled_at,created_at))');
  $q->execute(["$y-01-01",($y+1).'-01-01']);
  foreach($q->fetchAll() as $x)$a[(int)$x['m']-1]-=(float)$x['s'];
  return $a;};
 j(['stats'=>$st,'byType'=>$bt,'rooms'=>$rooms,'res'=>$rs,'pays'=>$pays,'ov'=>['res'=>$ovr,'pays'=>$ovp],'month'=>$month,'now_month'=>$nowM,'max_month'=>$maxM,'yearly'=>['year'=>$yr,'cur'=>$series($yr),'prev'=>$series($yr-1)]]);
case 'status': /* complete or cancel a reservation */
 adm();$n=P('s');
 $pdo->beginTransaction();
 $r=resById($pdo,P('id'),true);
 if(!$r||!in_array($n,['completed','cancelled'])){$pdo->rollBack();j(['error'=>'Invalid change'],422);}
 if($n=='completed'){
  if($r['status']!='approved'){$pdo->rollBack();j(['error'=>'Only approved reservations can be completed'],422);}
  if(balanceDue($r,paidSum($pdo,$r['id']))>0){$pdo->rollBack();j(['error'=>'Collect the remaining balance before completing this reservation'],422);}
  completeStay($pdo,$r['id']);
 }else{
  if(!in_array($r['status'],['pending','approved'])){$pdo->rollBack();j(['error'=>'This reservation can no longer be cancelled'],422);}
  cancelRes($pdo,$r,'admin',true);
 }
 $pdo->commit();j(['ok'=>1]);
case 'collect_balance': /* guest pays the remaining balance in cash at check-in */
 adm();
 $pdo->beginTransaction();
 $r=resById($pdo,P('id'),true);
 if(!$r||!in_array($r['status'],['approved','completed'])){$pdo->rollBack();j(['error'=>'Only approved or completed reservations have a balance'],422);}
 $bal=balanceDue($r,paidSum($pdo,$r['id']));
 if($bal<=0){$pdo->rollBack();j(['error'=>'Nothing left to collect'],422);}
 $pdo->prepare('INSERT INTO payments(reservation_id,amount,ref_no,proof,kind,method,status) VALUES(?,?,?,NULL,"balance","cash","verified")')->execute([$r['id'],$bal,'BAL-'.$r['ref']]);
 notify($pdo,$r['guest_id'],"Balance of ".peso($bal)." for reservation {$r['ref']} received. Thank you!");
 $pdo->commit();j(['ok'=>1,'amount'=>$bal]);
case 'refund': /* record a refund for a cancelled reservation */
 adm();$st=P('st');
 $pdo->beginTransaction();
 $r=resById($pdo,P('id'),true);
 if(!$r||$r['status']!='cancelled'||!in_array($st,['none','pending','refunded'])){$pdo->rollBack();j(['error'=>'Only cancelled reservations can have a refund'],422);}
 $paid=paidSum($pdo,$r['id']);$amt=$st=='none'?0:round((float)P('amount'),2);
 if($st!='none'&&($amt<=0||$amt>$paid)){$pdo->rollBack();j(['error'=>'The refund must be more than ₱0 and not more than the '.peso($paid).' received'],422);}
 $pdo->prepare('UPDATE reservations SET refund_status=?,refund_amount=?,refund_note=? WHERE id=?')->execute([$st,$amt,mb_substr(P('note'),0,120)?:null,$r['id']]);
 if($st=='refunded'&&$r['refund_status']!='refunded')notify($pdo,$r['guest_id'],"Your refund of ".peso($amt)." for reservation {$r['ref']} has been processed.");
 $pdo->commit();j(['ok'=>1]);
case 'room_save':
 adm();
 $r=$pdo->prepare('SELECT id FROM rooms WHERE id=? AND deleted=0');$r->execute([P('id')]);if(!$r->fetch())j(['error'=>'Room not found'],404);
 if($e=roomErr($pdo,P('id')))j(['error'=>$e],422);
 $pdo->prepare('UPDATE rooms SET name=?,rate=?,base_cap=?,max_cap=?,active=? WHERE id=?')->execute([P('name'),(int)P('rate'),(int)P('base_cap'),(int)P('max_cap'),P('active')?1:0,P('id')]);
 j(['ok'=>1]);
case 'room_add':
 adm();
 if(!in_array(P('type'),['standard','family','hall']))j(['error'=>'Choose a room type'],422);
 if($e=roomErr($pdo,0))j(['error'=>$e],422);
 $pdo->prepare('INSERT INTO rooms(name,type,rate,base_cap,max_cap,active) VALUES(?,?,?,?,?,1)')->execute([P('name'),P('type'),(int)P('rate'),(int)P('base_cap'),(int)P('max_cap')]);
 j(['ok'=>1]);
case 'rooms_save': /* "Save All Changes": every edited row of the Rooms & Rates table in one go. All rows are valid or nothing is saved. */
 adm();
 $rows=json_decode($_POST['rows']??'[]',true);
 if(!is_array($rows)||!$rows)j(['error'=>'There are no changes to save'],422);
 $ids=array_map('intval',array_column($rows,'id'));
 $pdo->beginTransaction();
 $seen=[];
 foreach($rows as $d){
  $id=(int)($d['id']??0);
  $s=$pdo->prepare('SELECT id FROM rooms WHERE id=? AND deleted=0 FOR UPDATE');$s->execute([$id]);
  if(!$s->fetch()){$pdo->rollBack();j(['error'=>'A room in the table no longer exists. Reload the page and try again.'],404);}
  $nm=trim((string)($d['name']??''));
  if($e=roomErr($pdo,$id,$d,$ids)){$pdo->rollBack();j(['error'=>($nm!==''?"$nm: ":'').$e],422);}
  if(isset($seen[strtolower($nm)])){$pdo->rollBack();j(['error'=>"Two rooms cannot have the same name ($nm)"],422);}
  $seen[strtolower($nm)]=1;
  $pdo->prepare('UPDATE rooms SET name=?,rate=?,base_cap=?,max_cap=?,active=? WHERE id=?')->execute([$nm,(int)$d['rate'],(int)$d['base_cap'],(int)$d['max_cap'],!empty($d['active'])?1:0,$id]);
 }
 $pdo->commit();j(['ok'=>1,'n'=>count($rows)]);
case 'room_delete': /* remove a room. Booking history is kept: a room with past bookings is hidden instead of erased. */
 adm();
 $pdo->beginTransaction();
 $s=$pdo->prepare('SELECT id FROM rooms WHERE id=? AND deleted=0 FOR UPDATE');$s->execute([P('id')]);$m=$s->fetch();
 if(!$m){$pdo->rollBack();j(['error'=>'Room not found'],404);}
 $s=$pdo->prepare('SELECT COUNT(*) c FROM reservations WHERE room_id=? AND status IN("pending","approved") AND checkout>CURDATE()');$s->execute([$m['id']]);
 if($s->fetch()['c']){$pdo->rollBack();j(['error'=>'This room still has pending or upcoming bookings. Cancel or complete them first, then delete the room.'],422);}
 $s=$pdo->prepare('SELECT COUNT(*) c FROM reservations WHERE room_id=?');$s->execute([$m['id']]);
 if($s->fetch()['c'])$pdo->prepare('UPDATE rooms SET deleted=1,active=0 WHERE id=?')->execute([$m['id']]);
 else $pdo->prepare('DELETE FROM rooms WHERE id=?')->execute([$m['id']]);
 $pdo->commit();j(['ok'=>1]);
case 'wk_quote': /* live price preview for the walk-in form */
 adm();
 $g=null;if(P('email')){$s=$pdo->prepare('SELECT id FROM guests WHERE email=?');$s->execute([P('email')]);$g=$s->fetch()['id']??null;}
 $q=quote($pdo,(int)P('room'),P('ci'),P('co'),(int)P('pax'),null,$g);
 if(isset($q['error']))j(['error'=>$q['error']]);
 $q['free']=avail($pdo,P('room'),P('ci'),P('co'));j($q);
case 'walkin': /* staff books a guest who walks in: approved straight away, downpayment taken in cash */
 adm();
 if(!P('name')||strlen(P('phone'))<7)j(['error'=>'Enter the guest name and mobile number'],422);
 if(P('email')!==''&&!filter_var(P('email'),FILTER_VALIDATE_EMAIL))j(['error'=>'That email address is not valid'],422);
 $pdo->beginTransaction();
 $pdo->prepare('SELECT id FROM rooms WHERE id=? FOR UPDATE')->execute([P('room')]);
 $gid=null;
 if(P('email')!==''){$s=$pdo->prepare('SELECT id FROM guests WHERE email=?');$s->execute([P('email')]);$gid=$s->fetch()['id']??null;}
 $q=quote($pdo,(int)P('room'),P('ci'),P('co'),(int)P('pax'),null,$gid);
 if(isset($q['error'])||!avail($pdo,P('room'),P('ci'),P('co'))){$pdo->rollBack();j(['error'=>$q['error']??'This room is already booked for those dates'],409);}
 $paid=P('amount')===''?$q['down']:round((float)P('amount'),2);
 if($paid<=0||$paid>$q['total']){$pdo->rollBack();j(['error'=>'Amount received must be more than ₱0 and not more than the total of '.peso($q['total'])],422);}
 if(!$gid){$pdo->prepare('INSERT INTO guests(name,email,phone) VALUES(?,?,?)')->execute([P('name'),P('email')?:null,P('phone')]);$gid=$pdo->lastInsertId();}
 $ref='BV'.strtoupper(bin2hex(random_bytes(3)));
 $pdo->prepare('INSERT INTO reservations(ref,guest_id,room_id,checkin,checkout,pax,extra_fee,discount,total,downpayment,status,source) VALUES(?,?,?,?,?,?,?,?,?,?,"approved","walkin")')->execute([$ref,$gid,P('room'),P('ci'),P('co'),(int)P('pax'),$q['extra'],$q['disc'],$q['total'],$q['down']]);
 $rid=$pdo->lastInsertId();
 $pdo->prepare('INSERT INTO payments(reservation_id,amount,ref_no,proof,kind,method,status) VALUES(?,?,"WALK-IN",NULL,"down","cash","verified")')->execute([$rid,$paid]);
 $pdo->commit();j(['ok'=>1,'ref'=>$ref,'total'=>$q['total'],'paid'=>$paid,'balance'=>max(0,$q['total']-$paid)]);
default:j(['error'=>'Unknown action'],400);
}}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();j(['error'=>'Server error: '.$e->getMessage()],500);}
