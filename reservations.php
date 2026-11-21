<?php require 'config.php'; $u=require_login(); $msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  check_csrf(); $r=one("SELECT * FROM reservations WHERE id=? AND user_id=?",[(int)$_POST['id'],$u['id']]);
  if($r && in_array($r['status'],['pending','approved'])){ $msg = cancel_res($r) ? 'Reservation cancelled.' : 'Reservation cancelled. It was less than 3 days before check-in, so the downpayment is non-refundable.'; }
}
$rows=all("SELECT r.*,m.name room FROM reservations r JOIN rooms m ON m.id=r.room_id WHERE r.user_id=? ORDER BY r.checkin DESC",[$u['id']]);
$cur=array_filter($rows,fn($r)=>in_array($r['status'],['pending','approved','checked_in']));
$past=array_filter($rows,fn($r)=>!in_array($r['status'],['pending','approved','checked_in']));
function res_card($r){ ?>
  <article class="res st-<?=$r['status']?>">
    <div><strong><?=e($r['room'])?></strong> <span class="pill"><?=status_label($r['status'])?></span>
      <p><?=date('M j, Y',strtotime($r['checkin']))?> 2:00 PM to <?=date('M j, Y',strtotime($r['checkout']))?> 12:00 NN (<?=$r['nights']?> night<?=$r['nights']>1?'s':''?>, <?=$r['guests']?> guests)</p>
      <p>Total <?=peso($r['total'])?>. Downpayment <?=peso($r['downpayment'])?>. Balance due at check-in <?=peso($r['total']-$r['downpayment'])?>.</p>
      <?php if($r['status']==='cancelled'){ ?><p class="tiny"><?=$r['refundable']?'Cancelled 3 or more days before check-in.':'Non-refundable: cancelled less than 3 days before check-in.'?></p><?php } ?></div>
    <?php if(in_array($r['status'],['pending','approved'])){ ?>
      <form method="post" data-confirm="Cancel this reservation? Cancellations less than 3 days before check-in are non-refundable."><?=csrf_field()?><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn outline">Cancel</button></form><?php } ?>
  </article>
<?php }
top('My reservations'); ?>
<main class="page narrow"><h1 class="mono">My reservations</h1>
  <?php if(isset($_GET['ok'])){ ?><p class="notice">Reservation sent. We will confirm it once the admin checks your downpayment.</p><?php } if($msg){ ?><p class="notice"><?=e($msg)?></p><?php } ?>
  <h2>Current and upcoming</h2>
  <?php if(!$cur) echo '<p class="empty">Nothing booked yet. <a href="reserve.php">Reserve a room</a></p>'; foreach($cur as $r) res_card($r); ?>
  <h2>Past reservations</h2>
  <?php if(!$past) echo '<p class="empty">Your past stays will appear here.</p>'; foreach($past as $r) res_card($r); ?>
</main>
<?php bottom();
