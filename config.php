<?php
// ===== Bella Vista — shared config & helpers =====
session_start();
date_default_timezone_set('Asia/Manila');
const DB_HOST='localhost', DB_NAME='bellavista_db', DB_USER='root', DB_PASS='';   // XAMPP defaults
const SPIN_COST=100, VIP_RATE=0.20, DOWNPAYMENT_RATE=0.50;

try {
  $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS,
    [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false]);
} catch (PDOException $ex) { exit('Database connection failed. Start MySQL in XAMPP and import sql/bellavista.sql.'); }

const TERMS = [
 ['Booking Confirmation & Downpayment','A downpayment is required to secure and confirm any reservation. The remaining balance must be settled upon check-in.'],
 ['Cancellation Policy','Cancellations made less than 3 days prior to the check-in date are strictly non-refundable.'],
 ['Damage Policy','Guests shall be held liable and billed for any damage or loss caused to hotel property, furniture, or fixtures during their stay.'],
 ['No Smoking Policy','Smoking is strictly prohibited inside all guest rooms. A fine of ₱2,000 will be charged for violations.'],
 ['Quiet Hours','Noise and loud activities must be kept to a minimum after 10:00 PM around the accommodation areas to ensure the peace and comfort of all guests.'],
];

// ---- db helpers
function q($sql,$p=[]){ global $pdo; $s=$pdo->prepare($sql); $s->execute($p); return $s; }
function one($sql,$p=[]){ return q($sql,$p)->fetch() ?: null; }
function all($sql,$p=[]){ return q($sql,$p)->fetchAll(); }
function e($s){ return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8'); }
function peso($n){ return '₱'.number_format((float)$n,2); }
function base(){ $d=dirname($_SERVER['SCRIPT_NAME']); if(preg_match('#/(admin|api)$#',$d)) $d=dirname($d); return rtrim($d,'/').'/'; }
function go($p){ header('Location: '.base().$p); exit; }

// preset admin (created once)
if(!one("SELECT id FROM users WHERE role='admin' LIMIT 1"))
  q("INSERT INTO users(name,email,phone,password,role) VALUES('Hotel Admin','admin@bellavista.local','09000000000',?, 'admin')",[password_hash('Admin@123',PASSWORD_DEFAULT)]);

// ---- auth & csrf
function me(){ return isset($_SESSION['uid']) ? one("SELECT * FROM users WHERE id=?",[$_SESSION['uid']]) : null; }
function require_login(){ $u=me(); if(!$u) go('index.php'); if($u['role']==='admin') go('admin/index.php'); return $u; }
function require_admin(){ $u=me(); if(!$u||$u['role']!=='admin') go('index.php'); return $u; }
function csrf(){ if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(16)); return $_SESSION['csrf']; }
function csrf_field(){ return '<input type="hidden" name="csrf" value="'.csrf().'">'; }
function check_csrf(){ $t=$_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF'] ?? ''); if(!hash_equals(csrf(),$t)){ http_response_code(403); exit('Invalid request token. Go back and try again.'); } }
function json_out($a){ header('Content-Type: application/json'); echo json_encode($a); exit; }

// ---- image slots: every placeholder shows its filename and has an HTML comment to find it fast
function img_slot($file,$label,$cls=''){
  $src=base().'assets/img/'.$file;
  return "\n<!-- ✏️ INSERT IMAGE HERE → save your photo as assets/img/$file ($label) -->\n".
    '<div class="slot '.e($cls).'"><span>'.e($label).'<small>assets/img/'.e($file).'</small></span><img src="'.e($src).'" alt="'.e($label).'"></div>'."\n";
}
function save_image($f,$dir){
  if(!isset($f) || $f['error']===UPLOAD_ERR_NO_FILE) return null;
  if($f['error']!==UPLOAD_ERR_OK || $f['size']>3*1024*1024) return false;
  $m=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
  $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$m] ?? null; if(!$ext) return false;
  $d=__DIR__.'/uploads/'.$dir; if(!is_dir($d)) mkdir($d,0775,true);
  $n=bin2hex(random_bytes(8)).'.'.$ext; move_uploaded_file($f['tmp_name'],"$d/$n"); return "uploads/$dir/$n";
}

// ---- booking logic (single source of truth: used by the quote API and the final booking)
function is_free($roomId,$in,$out,$ignore=0){
  // Check-out is 12:00 NN and check-in is 2:00 PM, so a stay may begin on the day another ends.
  return !one("SELECT id FROM reservations WHERE room_id=? AND status IN('pending','approved','checked_in') AND checkin<? AND checkout>? AND id<>?",[$roomId,$out,$in,$ignore]);
}
function earliest_checkin(){ return new DateTime(date('H')>=14 ? 'tomorrow' : 'today'); }   // today's 2:00 PM check-in has passed

function compute_quote($roomId,$in,$out,$guests,$voucherId,$user){
  $room=one("SELECT * FROM rooms WHERE id=?",[$roomId]); if(!$room) return ['ok'=>false,'error'=>'Choose a room.'];
  $d1=DateTime::createFromFormat('!Y-m-d',(string)$in); $d2=DateTime::createFromFormat('!Y-m-d',(string)$out);
  if(!$d1||!$d2) return ['ok'=>false,'error'=>'Select your check-in (2:00 PM) and check-out (12:00 NN) dates.'];
  $min=earliest_checkin(); $min->setTime(0,0);
  if($d1<$min) return ['ok'=>false,'error'=>'Earliest check-in is '.$min->format('M j, Y').' (check-in starts 2:00 PM).'];
  if($d2<=$d1) return ['ok'=>false,'error'=>'Check-out (12:00 NN) must be at least one night after check-in.'];
  $n=$d1->diff($d2)->days; if($n>30) return ['ok'=>false,'error'=>'Stays are limited to 30 nights.'];
  $guests=(int)$guests; if($guests<1||$guests>$room['max_cap']) return ['ok'=>false,'error'=>"{$room['name']} accepts 1 to {$room['max_cap']} guests."];
  $perNightExtra = $room['type']==='standard' ? ($guests>2?1000:0) : ($room['type']==='event' ? max(0,$guests-30)*500 : 0);
  $base=$room['base_price']*$n; $extra=$perNightExtra*$n; $sub=$base+$extra;
  $vd=0; $vlabel=null;
  if($voucherId){
    $v=one("SELECT * FROM vouchers WHERE id=? AND user_id=? AND status='unused'",[$voucherId,$user['id']]);
    if(!$v) return ['ok'=>false,'error'=>'That voucher is not available.'];
    if($v['kind']==='discount') $vd=round($sub*$v['pct']/100,2);
    elseif($v['kind']==='free_standard' && $room['type']==='standard') $vd=min($n,2)*$room['base_price'];   // 3 days / 2 nights free
    elseif($v['kind']==='free_family' && $room['type']==='family') $vd=min($n,2)*$room['base_price'];
    else return ['ok'=>false,'error'=>'This voucher does not apply to the selected room type.'];
    $vlabel=$v['label'];
  }
  $vip = $user['is_vip'] ? round(($sub-$vd)*VIP_RATE,2) : 0;     // VIP 20% applies after the voucher
  $total=$sub-$vd-$vip; $down=ceil($total*DOWNPAYMENT_RATE);
  return ['ok'=>true,'nights'=>$n,'base'=>$base,'extra'=>$extra,'subtotal'=>$sub,'voucher_discount'=>$vd,'voucher_label'=>$vlabel,
    'vip_discount'=>$vip,'total'=>$total,'down'=>$down,'balance'=>$total-$down,'available'=>is_free($roomId,$d1->format('Y-m-d'),$d2->format('Y-m-d'))];
}
function current_qr(){ return one("SELECT path,qr_date FROM qr_codes WHERE qr_date<=CURDATE() ORDER BY qr_date DESC LIMIT 1"); }
function cancel_res($r){
  $days=(int)(new DateTime('today'))->diff(new DateTime($r['checkin']))->format('%r%a'); $ref=$days>=3?1:0;
  q("UPDATE reservations SET status='cancelled',refundable=? WHERE id=?",[$ref,$r['id']]); return $ref;
}
function status_label($s){ return ['pending'=>'Awaiting approval','approved'=>'Confirmed','checked_in'=>'Checked in','completed'=>'Completed','cancelled'=>'Cancelled'][$s] ?? $s; }

// ---- page chrome
function top($title,$o=[]){ $u=me(); $b=base(); ?>
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($title)?> | Bella Vista Hotel &amp; Events Place</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&amp;family=EB+Garamond:wght@400;600;700&amp;display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?=$b?>assets/css/style.css"></head>
<body class="<?=e($o['body']??'')?>" data-csrf="<?=csrf()?>" data-base="<?=$b?>">
<?php if(empty($o['bare'])){ if(!empty($o['banner'])){ ?>
<div class="banner">Open Monday to Sunday. Check-in 2:00 PM, check-out 12:00 NN. Book directly for the best rates.</div>
<?php } ?>
<header class="hdr">
  <a href="<?=$b?>home.php" class="logo-link"><!-- ✏️ INSERT LOGO HERE → save as assets/img/logo.png -->
    <?=img_slot('logo.png','Logo','logo')?></a>
  <div class="hdr-r">
    <a class="phone" href="tel:+639852164561">&#9990; +63 985 216 4561</a>
    <?php if($u){ ?><button id="menuBtn" class="burger" aria-label="Open menu" aria-expanded="false"><i></i><i></i><i></i></button><?php } ?>
  </div>
</header>
<?php if($u){ ?>
<div id="scrim" class="scrim"></div>
<nav id="drawer" class="drawer" aria-label="Main menu">
  <div class="who"><strong><?=e($u['name'])?></strong><?php if($u['is_vip']){ ?> <span class="vip-tag">VIP</span><?php } ?><small><?=e($u['email'])?></small></div>
  <a href="<?=$b?>reserve.php">Reserve a Room</a>
  <a href="<?=$b?>rooms.php#pricing">Pricing</a>
  <a href="<?=$b?>profile.php">Account Profile</a>
  <a href="<?=$b?>reservations.php">My Reservations</a>
  <a href="<?=$b?>rewards.php">My Points &amp; Rewards</a>
  <a href="<?=$b?>vouchers.php">My Vouchers</a>
  <a href="<?=$b?>terms.php">Terms &amp; Conditions</a>
  <a href="<?=$b?>logout.php" class="logout">Log out</a>
</nav>
<?php } } }

function bottom($o=[]){ $b=base(); if(empty($o['bare'])){ ?>
<footer class="foot">
  <div class="foot-in">
    <h3>CONTACT US!</h3>
    <div><strong>Visit</strong><br><!-- ✏️ EDIT: put the hotel's real address here -->Bella Vista Hotel &amp; Events Place<br>Foothills of the mountains, Philippines</div>
    <div><strong>Call or message</strong><br>+63 985 216 4561<br>reservations@bellavista.example</div>
    <div><strong>Hours</strong><br>Open Monday to Sunday<br>Check-in 2:00 PM<br>Check-out 12:00 NN</div>
  </div>
</footer>
<?php } ?>
<script src="<?=$b?>assets/js/app.js"></script></body></html>
<?php }
