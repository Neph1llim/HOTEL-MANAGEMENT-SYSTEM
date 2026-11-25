<?php
require 'config.php';
$a=$_POST['a']??'';
function P($k){return trim($_POST[$k]??'');}
function guest($pdo){if(empty($_SESSION['gid']))return false;$s=$pdo->prepare('SELECT * FROM guests WHERE id=?');$s->execute([$_SESSION['gid']]);return $s->fetch();}
function adm(){if(empty($_SESSION['admin']))j(['error'=>'Login required'],401);}
function resById($pdo,$id,$lock=false){$s=$pdo->prepare('SELECT * FROM reservations WHERE id=?'.($lock?' FOR UPDATE':''));$s->execute([$id]);return $s->fetch();}
/* what the wheel needs to draw each slice: its name and kind (VIP slices are gold) */
function wheelSlices($pr){$o=[];foreach($pr as $p)$o[]=['label'=>$p['label'],'kind'=>$p['base']];return $o;}
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
 $pw=$_POST['pass']??'';$fn=P('first_name');$ln=P('last_name');$nm=$fn.' '.$ln;
 if($fn===''||$ln===''||mb_strlen($fn)>50||mb_strlen($ln)>50||!filter_var(P('email'),FILTER_VALIDATE_EMAIL)||strlen(P('phone'))<7||strlen($pw)<6)j(['error'=>'Fill in every field. The password needs at least 6 characters'],422);
 $s=$pdo->prepare('SELECT id,pass FROM guests WHERE email=?');$s->execute([P('email')]);$old=$s->fetch();
 if($old&&$old['pass'])j(['error'=>'That email is already registered'],409);
 if($old){ /* a walk-in guest the hotel already recorded with this email: attach the new login to that record so points carry over */
  $pdo->prepare('UPDATE guests SET name=?,first_name=?,last_name=?,phone=?,pass=? WHERE id=?')->execute([$nm,$fn,$ln,P('phone'),password_hash($pw,PASSWORD_DEFAULT),$old['id']]);$gid=$old['id'];
 }else{
  $pdo->prepare('INSERT INTO guests(name,first_name,last_name,email,phone,pass) VALUES(?,?,?,?,?,?)')->execute([$nm,$fn,$ln,P('email'),P('phone'),password_hash($pw,PASSWORD_DEFAULT)]);$gid=$pdo->lastInsertId();}
 session_regenerate_id(true);$_SESSION['gid']=$gid;j(['ok'=>1]);
case 'glogin':
 $s=$pdo->prepare('SELECT * FROM guests WHERE email=?');$s->execute([P('email')]);$u=$s->fetch();
 if(!$u||!$u['pass']||!password_verify($_POST['pass']??'',$u['pass']))j(['error'=>'Wrong email or password'],401);
 session_regenerate_id(true);$_SESSION['gid']=$u['id'];j(['ok'=>1]);
case 'glogout':unset($_SESSION['gid']);j(['ok'=>1]);
case 'profile':
 $g=guest($pdo);if(!$g)j(['error'=>'Please log in'],401);
 $fn=P('first_name');$ln=P('last_name');
 if($fn===''||$ln===''||mb_strlen($fn)>50||mb_strlen($ln)>50||strlen(P('phone'))<7)j(['error'=>'Enter your first name, last name and a valid mobile number'],422);
 $pdo->prepare('UPDATE guests SET name=?,first_name=?,last_name=?,phone=? WHERE id=?')->execute([$fn.' '.$ln,$fn,$ln,P('phone'),$g['id']]);
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
 $s=$pdo->prepare('SELECT id,msg,is_read,created_at FROM notifications WHERE guest_id=? ORDER BY id DESC LIMIT '.max(1,min(100,(int)(P('limit')?:30))));$s->execute([$g['id']]);
 j(['notes'=>$s->fetchAll()]);
case 'notes_read':
 $g=guest($pdo);if(!$g)j(['error'=>'Please log in'],401);
 $id=(int)P('id'); /* one notification when an id is sent, otherwise all of them */
 $pdo->prepare('UPDATE notifications SET is_read=1 WHERE guest_id=?'.($id?' AND id=?':''))->execute($id?[$g['id'],$id]:[$g['id']]);
 $u=$pdo->prepare('SELECT COUNT(*) FROM notifications WHERE guest_id=? AND is_read=0');$u->execute([$g['id']]);j(['ok'=>1,'unread'=>(int)$u->fetchColumn()]);
case 'notes_count': /* unread total for the navbar bell */
 $g=guest($pdo);if(!$g)j(['error'=>'Please log in'],401);
 $u=$pdo->prepare('SELECT COUNT(*) FROM notifications WHERE guest_id=? AND is_read=0');$u->execute([$g['id']]);j(['unread'=>(int)$u->fetchColumn()]);
case 'lookup':
 $g=guest($pdo);if(!$g)j(['error'=>'Please log in'],401);
 $v=$pdo->prepare('SELECT id,prize,kind,CONCAT("VCH-",id) code FROM vouchers WHERE guest_id=? AND status="unused"');$v->execute([$g['id']]);
 $u=$pdo->prepare('SELECT COUNT(*) c FROM notifications WHERE guest_id=? AND is_read=0');$u->execute([$g['id']]);
 j(['guest'=>['name'=>$g['name'],'first_name'=>$g['first_name']??null,'last_name'=>$g['last_name']??null,'email'=>$g['email'],'phone'=>$g['phone'],'points'=>(int)$g['points'],'vip'=>(int)$g['vip']],'vouchers'=>$v->fetchAll(),'unread'=>(int)$u->fetch()['c']]);
case 'wheel': /* public: the enabled rewards in wheel order, so the wheel on the Rewards page always matches Admin > Rewards */
 j(['prizes'=>wheelSlices(wheelPrizes($pdo))]);
case 'spin':
 $g=guest($pdo);if(!$g)j(['error'=>'Please log in'],401);
 $pdo->beginTransaction();
 $s=$pdo->prepare('SELECT points FROM guests WHERE id=? FOR UPDATE');$s->execute([$g['id']]);$pts=(int)$s->fetch()['points'];
 if($pts<SPIN_COST){$pdo->rollBack();j(['error'=>'You need '.SPIN_COST.' points to spin'],422);}
 $pr=wheelPrizes($pdo);
 /* chances are percentages with up to 2 decimals, so they are weighed in hundredths of a percent */
 $tot=0;foreach($pr as $k=>$p){$pr[$k]['w']=(int)round((float)$p['chance']*100);$tot+=$pr[$k]['w'];}
 if(!$pr||$tot<1){$pdo->rollBack();j(['error'=>'The rewards wheel is not available right now'],422);}
 $x=random_int(1,$tot);$c=0;$i=count($pr)-1;foreach($pr as $k=>$p){$c+=$p['w'];if($x<=$c){$i=$k;break;}}
 $p=$pr[$i];
 $pdo->prepare('UPDATE guests SET points=points-?'.($p['base']=='vip'?',vip=1':'').' WHERE id=?')->execute([SPIN_COST,$g['id']]);
 /* the voucher keeps its own copy of what it is worth (name, discount, room, nights), so editing or deleting the reward later never changes vouchers already won */
 if($p['base']!='vip'){$fr=$p['base']=='free_room';$pdo->prepare('INSERT INTO vouchers(guest_id,prize,kind,pct,room_id,room_type,nights) VALUES(?,?,?,?,?,?,?)')->execute([$g['id'],$p['label'],$p['base'],$p['base']=='pct'?(int)$p['pct']:0,$fr?$p['room_id']:null,$fr?$p['room_type']:null,$fr?(int)$p['nights']:2]);}
 notify($pdo,$g['id'],$p['base']=='vip'?'Congratulations! You won VIP status: '.$p['label'].'.':'You spun the rewards wheel and won: '.$p['label'].'. Find it under My Vouchers.');
 $pdo->commit();j(['index'=>$i,'prize'=>$p['label'],'points'=>$pts-SPIN_COST,'prizes'=>wheelSlices($pr)]);

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
 notify($pdo,$g['id'],'You earned '.$pts.' point'.($pts>1?'s':'').' from '.($gm=='toss'?'Golden Token Toss':'Resort Harvest Catch').' today.');
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
 j(['stats'=>$st,'rooms'=>$rooms,'res'=>$rs,'pays'=>$pays,'ov'=>['res'=>$ovr,'pays'=>$ovp],'month'=>$month,'now_month'=>$nowM,'max_month'=>$maxM,'yearly'=>['year'=>$yr,'cur'=>$series($yr),'prev'=>$series($yr-1)],'occ'=>['month'=>$month,'rows'=>bookingsByType($pdo,$ms,$me)]]);
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
case 'rewards_get': /* Admin > Rewards: the points rate and every wheel reward (enabled or not) */
 adm();
 j(['percent'=>pointsPercent($pdo),'prizes'=>$pdo->query('SELECT id,label,kind,pct,room_id,room_type,nights,chance,active FROM wheel_prizes ORDER BY id')->fetchAll(),'max_wheel'=>MAX_WHEEL,'max_nights'=>MAX_FREE_NIGHTS,
  'types'=>rewardTypes($pdo),'rooms'=>$pdo->query('SELECT id,name,type,active FROM rooms WHERE deleted=0 AND type<>"hall" ORDER BY id')->fetchAll()]);
case 'rewards_save': /* "Save Reward Settings": the points rate and the whole list of wheel rewards (added, edited, removed) in one go. All valid or nothing is saved. */
 adm();
 $pp=trim(P('percent'));
 if($pp===''||!is_numeric($pp)||(float)$pp<0||(float)$pp>100)j(['error'=>'Points earned must be a number from 0 to 100 (% of the amount spent)'],422);
 $rows=json_decode($_POST['rows']??'[]',true);$known=json_decode($_POST['known']??'[]',true);
 if(!is_array($rows)||!is_array($known))j(['error'=>'Nothing to save'],422);
 $types=[];foreach(rewardTypes($pdo) as $t)$types[$t['code']]=$t['base'];
 $rooms=[];foreach($pdo->query('SELECT id,type FROM rooms WHERE deleted=0')->fetchAll() as $m)$rooms[(int)$m['id']]=$m['type'];
 $seen=[];$on=0;$tot=0;$clean=[];
 foreach($rows as $d){
  $lb=trim((string)($d['label']??''));$kind=(string)($d['kind']??'');$act=!empty($d['active']);
  if($lb===''||mb_strlen($lb)>60)j(['error'=>'Every reward needs a name (up to 60 characters)'],422);
  if(isset($seen[mb_strtolower($lb)]))j(['error'=>"Two rewards cannot have the same name ($lb)"],422);
  $seen[mb_strtolower($lb)]=1;
  if(!isset($types[$kind]))j(['error'=>"$lb: choose a reward type"],422);
  $base=$types[$kind];$pc=0;$rid=null;$rty=null;$nt=2;
  if($base==='pct'){$pc=(int)($d['pct']??0);if($pc<1||$pc>100)j(['error'=>"$lb: the discount must be from 1% to 100%"],422);}
  if($base==='vip'){$pc=(int)($d['pct']??0);if($pc<1||$pc>100)j(['error'=>"$lb: the VIP discount must be from 1% to 100%"],422);}
  if($base==='free_room'){
   $room=(string)($d['room']??'');
   if($room!==''&&ctype_digit($room)){if(!isset($rooms[(int)$room])||$rooms[(int)$room]==='hall')j(['error'=>"$lb: choose a room that is still on the rooms list"],422);$rid=(int)$room;}
   elseif($room==='standard'||$room==='family')$rty=$room;
   elseif($room!=='any')j(['error'=>"$lb: choose which room the free stay is for"],422);
   $nt=(int)($d['nights']??0);if($nt<1||$nt>MAX_FREE_NIGHTS)j(['error'=>"$lb: free nights must be from 1 to ".MAX_FREE_NIGHTS],422);
  }
  $ch=$d['chance']??'';
  if(!is_numeric($ch)||(float)$ch<0||(float)$ch>100)j(['error'=>"$lb: the probability must be a number from 0 to 100"],422);
  $w=(int)round((float)$ch*100);
  if(abs((float)$ch*100-$w)>1e-6)j(['error'=>"$lb: use at most 2 decimal places for the probability"],422);
  if($act){
   if($w<1)j(['error'=>"$lb: an enabled reward needs a probability above 0"],422);
   $on++;$tot+=$w;
  }
  $clean[]=['id'=>(int)($d['id']??0),'label'=>$lb,'kind'=>$kind,'base'=>$base,'pct'=>$pc,'room_id'=>$rid,'room_type'=>$rty,'nights'=>$nt,'chance'=>number_format($w/100,2,'.',''),'active'=>$act?1:0];
 }
 if($on<2)j(['error'=>'Enable at least 2 rewards so the wheel has something to spin'],422);
 if($on>MAX_WHEEL)j(['error'=>'The wheel can show at most '.MAX_WHEEL.' enabled rewards. Switch some off.'],422);
 if($tot!==10000)j(['error'=>'The probabilities of the enabled rewards must add up to exactly 100% (they add up to '.rtrim(rtrim(number_format($tot/100,2,'.',''),'0'),'.').'%)'],422);
 $pdo->beginTransaction();
 $have=array_map('intval',array_column($pdo->query('SELECT id FROM wheel_prizes FOR UPDATE')->fetchAll(),'id'));
 $kn=array_map('intval',$known);sort($have);sort($kn);
 if($have!==$kn){$pdo->rollBack();j(['error'=>'The rewards were changed somewhere else. Reload the page and try again.'],409);}
 $keep=[];
 foreach($clean as $c){
  if($c['id']){
   if(!in_array($c['id'],$have,true)){$pdo->rollBack();j(['error'=>'A reward in the table no longer exists. Reload the page and try again.'],404);}
   $pdo->prepare('UPDATE wheel_prizes SET label=?,kind=?,pct=?,room_id=?,room_type=?,nights=?,chance=?,active=? WHERE id=?')->execute([$c['label'],$c['kind'],$c['pct'],$c['room_id'],$c['room_type'],$c['nights'],$c['chance'],$c['active'],$c['id']]);
   $keep[]=$c['id'];
  }else{
   $pdo->prepare('INSERT INTO wheel_prizes(label,kind,pct,room_id,room_type,nights,chance,active) VALUES(?,?,?,?,?,?,?,?)')->execute([$c['label'],$c['kind'],$c['pct'],$c['room_id'],$c['room_type'],$c['nights'],$c['chance'],$c['active']]);
   $keep[]=(int)$pdo->lastInsertId();
  }
 }
 /* removed rewards: vouchers already won keep their own name/kind/discount, so deleting a reward never breaks them */
 foreach($have as $id)if(!in_array($id,$keep,true))$pdo->prepare('DELETE FROM wheel_prizes WHERE id=?')->execute([$id]);
 $pdo->prepare('INSERT INTO settings(k,v) VALUES(\'points_percent\',?) ON DUPLICATE KEY UPDATE v=VALUES(v)')->execute([(string)(float)$pp]);
 /* the VIP discount that guests get on every booking is the number on the VIP status reward (the highest one if there are several; unchanged if none is left) */
 $vp=array_column(array_filter($clean,function($c){return $c['base']==='vip';}),'pct');
 if($vp)$pdo->prepare('INSERT INTO settings(k,v) VALUES(\'vip_percent\',?) ON DUPLICATE KEY UPDATE v=VALUES(v)')->execute([(string)max($vp)]);
 $pdo->commit();j(['ok'=>1,'n'=>count($clean)]);
case 'type_save': /* rename a reward type (what it does cannot be changed) */
 adm();
 $code=P('code');$lb=trim(P('label'));
 if($lb===''||mb_strlen($lb)>60)j(['error'=>'Enter a name for the reward type (up to 60 characters)'],422);
 $s=$pdo->prepare('SELECT 1 FROM reward_types WHERE code=?');$s->execute([$code]);
 if(!$s->fetch())j(['error'=>'That reward type no longer exists. Reload the page.'],404);
 $s=$pdo->prepare('SELECT 1 FROM reward_types WHERE LOWER(label)=LOWER(?) AND code<>?');$s->execute([$lb,$code]);
 if($s->fetch())j(['error'=>'A reward type with that name already exists'],422);
 $pdo->prepare('UPDATE reward_types SET label=? WHERE code=?')->execute([$lb,$code]);
 j(['ok'=>1,'types'=>rewardTypes($pdo)]);
case 'type_delete': /* only a custom type that no wheel reward uses */
 adm();
 $code=P('code');
 $s=$pdo->prepare('SELECT builtin FROM reward_types WHERE code=?');$s->execute([$code]);$t=$s->fetch();
 if(!$t)j(['error'=>'That reward type no longer exists. Reload the page.'],404);
 if((int)$t['builtin'])j(['error'=>'Built-in reward types cannot be deleted'],422);
 $s=$pdo->prepare('SELECT COUNT(*) FROM wheel_prizes WHERE kind=?');$s->execute([$code]);
 if((int)$s->fetchColumn())j(['error'=>'Rewards on the wheel still use this type. Change or remove them, press Save Reward Settings, then delete the type.'],422);
 $pdo->prepare('DELETE FROM reward_types WHERE code=?')->execute([$code]);
 j(['ok'=>1,'types'=>rewardTypes($pdo)]);
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
