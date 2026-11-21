<?php require '../config.php'; $a=require_admin(); $tab=$_GET['tab']??'reports'; $msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  check_csrf(); $act=$_POST['action']??'';
  if($act==='qr'){
    $d=$_POST['qr_date']??''; $p=save_image($_FILES['qr']??null,'qr');
    if(!DateTime::createFromFormat('Y-m-d',$d)||!$p) $msg='Choose a date and a JPG, PNG or WEBP image under 3 MB.';
    else { q("INSERT INTO qr_codes(qr_date,path) VALUES(?,?) ON DUPLICATE KEY UPDATE path=VALUES(path)",[$d,$p]); $msg='GCash QR saved. Guests see it immediately.'; }
    $tab='qr';
  } else {
    $r=one("SELECT * FROM reservations WHERE id=?",[(int)$_POST['id']]); $tab='reservations';
    if($r){
      if($act==='approve'&&$r['status']==='pending') q("UPDATE reservations SET status='approved' WHERE id=?",[$r['id']]);
      if($act==='checkin'&&$r['status']==='approved') q("UPDATE reservations SET status='checked_in' WHERE id=?",[$r['id']]);
      if($act==='complete'&&in_array($r['status'],['approved','checked_in'])){
        q("UPDATE reservations SET status='completed' WHERE id=?",[$r['id']]);
        q("UPDATE users SET total_spent=total_spent+?,total_days=total_days+?,points=points+? WHERE id=?",[$r['total'],$r['nights'],(int)floor($r['total']*0.01),$r['user_id']]);   // 1% of spend becomes points
      }
      if($act==='cancel'&&in_array($r['status'],['pending','approved','checked_in'])) cancel_res($r);
      $msg='Reservation #'.$r['id'].' updated.';
    }
  }
}
top('Admin',['bare'=>true,'body'=>'admin']); ?>
<header class="adm-hdr"><strong>Bella Vista Admin</strong>
  <nav><?php foreach(['reports'=>'Reports','reservations'=>'Reservations','availability'=>'Availability','qr'=>'GCash QR'] as $k=>$l){ ?><a href="?tab=<?=$k?>" class="<?=$tab===$k?'on':''?>"><?=$l?></a><?php } ?><a href="../logout.php">Log out</a></nav></header>
<main class="page wide" <?=$tab==='availability'?'data-refresh="30"':''?>>
<?php if($msg){ ?><p class="notice"><?=e($msg)?></p><?php }

if($tab==='reports'){
  $s=one("SELECT COUNT(*) n, SUM(status='pending') pend, SUM(status IN('approved','checked_in')) act, SUM(status='completed') done, SUM(status='cancelled') canc,
    COALESCE(SUM(CASE WHEN status IN('approved','checked_in','completed') THEN total END),0) booked,
    COALESCE(SUM(CASE WHEN status='completed' THEN total WHEN status IN('approved','checked_in') THEN downpayment END),0) collected FROM reservations");
  $byType=all("SELECT m.type,COUNT(*) n,COALESCE(SUM(r.total),0) rev FROM reservations r JOIN rooms m ON m.id=r.room_id WHERE r.status IN('approved','checked_in','completed') GROUP BY m.type");
  $byMonth=all("SELECT DATE_FORMAT(checkin,'%Y-%m') mth,COUNT(*) n,SUM(total) rev FROM reservations WHERE status IN('approved','checked_in','completed') GROUP BY mth ORDER BY mth DESC LIMIT 6");
  $max=max(1,...array_map(fn($x)=>(float)$x['rev'],$byMonth?:[['rev'=>1]])); ?>
  <h1>Summary reports</h1>
  <div class="stats">
    <div><b><?=(int)$s['n']?></b>Total bookings</div><div><b><?=(int)$s['pend']?></b>Awaiting approval</div><div><b><?=(int)$s['act']?></b>Confirmed or in-house</div>
    <div><b><?=(int)$s['done']?></b>Completed</div><div><b><?=(int)$s['canc']?></b>Cancelled</div>
    <div><b><?=peso($s['booked'])?></b>Booked revenue</div><div><b><?=peso($s['collected'])?></b>Collected so far</div></div>
  <h2>Revenue by month (by check-in date)</h2>
  <table class="tbl"><tr><th>Month</th><th>Bookings</th><th>Revenue</th><th></th></tr>
  <?php foreach($byMonth as $m){ ?><tr><td><?=e($m['mth'])?></td><td><?=$m['n']?></td><td><?=peso($m['rev'])?></td><td><progress value="<?=$m['rev']?>" max="<?=$max?>"></progress></td></tr><?php } if(!$byMonth) echo '<tr><td colspan="4">No confirmed bookings yet.</td></tr>'; ?></table>
  <h2>Revenue by room type</h2>
  <table class="tbl"><tr><th>Type</th><th>Bookings</th><th>Revenue</th></tr>
  <?php foreach($byType as $m){ ?><tr><td><?=ucfirst($m['type'])?></td><td><?=$m['n']?></td><td><?=peso($m['rev'])?></td></tr><?php } ?></table>
<?php } elseif($tab==='reservations'){
  $f=$_GET['status']??''; $w=in_array($f,['pending','approved','checked_in','completed','cancelled'])?"WHERE r.status='$f'":'';
  $rows=all("SELECT r.*,m.name room,u.name uname,u.phone,u.is_vip FROM reservations r JOIN rooms m ON m.id=r.room_id JOIN users u ON u.id=r.user_id $w ORDER BY r.status='pending' DESC,r.created_at DESC"); ?>
  <h1>Reservations</h1>
  <p class="filters"><a href="?tab=reservations">All</a><?php foreach(['pending','approved','checked_in','completed','cancelled'] as $s){ ?><a href="?tab=reservations&amp;status=<?=$s?>"><?=status_label($s)?></a><?php } ?></p>
  <div class="table-wrap"><table class="tbl"><tr><th>#</th><th>Guest</th><th>Room</th><th>Stay</th><th>Guests</th><th>Total / Down</th><th>GCash ref</th><th>Status</th><th>Actions</th></tr>
  <?php foreach($rows as $r){ ?><tr>
    <td><?=$r['id']?></td><td><?=e($r['uname'])?><?=$r['is_vip']?' (VIP)':''?><br><small><?=e($r['phone'])?></small></td><td><?=e($r['room'])?></td>
    <td><?=$r['checkin']?> to <?=$r['checkout']?><br><small><?=$r['nights']?> nights</small></td><td><?=$r['guests']?></td>
    <td><?=peso($r['total'])?><br><small>Down <?=peso($r['downpayment'])?></small></td>
    <td><?=e($r['payment_ref'])?><?php if($r['proof_path']){ ?><br><a target="_blank" href="../<?=e($r['proof_path'])?>">View proof</a><?php } ?></td>
    <td><span class="pill"><?=status_label($r['status'])?></span></td>
    <td><form method="post" class="acts"><?=csrf_field()?><input type="hidden" name="id" value="<?=$r['id']?>">
      <?php if($r['status']==='pending') echo '<button name="action" value="approve" class="btn solid sm">Approve</button>';
            if($r['status']==='approved') echo '<button name="action" value="checkin" class="btn outline sm">Check in</button>';
            if(in_array($r['status'],['approved','checked_in'])) echo '<button name="action" value="complete" class="btn outline sm" data-confirm="Mark as completed? This credits the guest\'s spend, days and points.">Complete</button>';
            if(in_array($r['status'],['pending','approved','checked_in'])) echo '<button name="action" value="cancel" class="btn danger sm" data-confirm="Cancel this reservation?">Cancel</button>'; ?></form></td></tr>
  <?php } if(!$rows) echo '<tr><td colspan="9">No reservations found.</td></tr>'; ?></table></div>
<?php } elseif($tab==='availability'){
  $d=DateTime::createFromFormat('Y-m-d',$_GET['date']??'')?$_GET['date']:date('Y-m-d');
  $rs=all("SELECT m.*,(SELECT CONCAT(r.status,'|',u.name,'|',r.checkout) FROM reservations r JOIN users u ON u.id=r.user_id WHERE r.room_id=m.id AND r.status IN('pending','approved','checked_in') AND r.checkin<=? AND r.checkout>? ORDER BY r.status='checked_in' DESC LIMIT 1) occ FROM rooms m ORDER BY m.id",[$d,$d]); ?>
  <h1>Room &amp; event hall availability</h1>
  <form method="get" class="row"><input type="hidden" name="tab" value="availability"><label>Night of<input type="date" name="date" value="<?=e($d)?>"></label><button class="btn outline">Show</button></form>
  <p class="tiny">Refreshes every 30 seconds.</p>
  <div class="room-grid"><?php foreach($rs as $r){ $o=$r['occ']?explode('|',$r['occ']):null; ?>
    <article class="room-card"><h3><?=e($r['name'])?></h3>
      <?php if(!$o) echo '<span class="pill ok">Available</span>'; elseif($o[0]==='checked_in') echo '<span class="pill bad">Occupied</span><p class="tiny">'.e($o[1]).', leaves '.e($o[2]).' 12:00 NN</p>'; else echo '<span class="pill warn">Reserved ('.($o[0]==='pending'?'awaiting approval':'confirmed').')</span><p class="tiny">'.e($o[1]).'</p>'; ?></article>
  <?php } ?></div>
<?php } else { $list=all("SELECT * FROM qr_codes ORDER BY qr_date DESC LIMIT 14"); $cur=current_qr(); ?>
  <h1>GCash QR per day</h1>
  <p>Guests see the QR for today's date. If none is set for today, the most recent earlier QR is shown. Changes appear on the reservation page within seconds.</p>
  <form method="post" enctype="multipart/form-data" class="row"><?=csrf_field()?><input type="hidden" name="action" value="qr">
    <label>Date<input type="date" name="qr_date" value="<?=date('Y-m-d')?>" required></label><label>QR image<input type="file" name="qr" accept="image/jpeg,image/png,image/webp" required></label><button class="btn solid">Save QR</button></form>
  <?php if($cur){ ?><h2>Showing now</h2><img class="qr-prev" src="../<?=e($cur['path'])?>" alt="Current QR"><?php } ?>
  <h2>Recent QR codes</h2><table class="tbl"><tr><th>Date</th><th>Image</th></tr><?php foreach($list as $x){ ?><tr><td><?=e($x['qr_date'])?></td><td><a target="_blank" href="../<?=e($x['path'])?>">Open</a></td></tr><?php } ?></table>
<?php } ?>
</main>
<?php bottom(['bare'=>true]);
