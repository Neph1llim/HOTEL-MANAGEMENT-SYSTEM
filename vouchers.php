<?php require 'config.php'; $u=require_login();
$v=all("SELECT * FROM vouchers WHERE user_id=? ORDER BY status,id DESC",[$u['id']]); top('My vouchers'); ?>
<main class="page narrow"><h1 class="mono">My vouchers</h1>
  <p>Only one voucher can be used per booking.</p>
  <?php if(!$v){ ?><p class="empty">No vouchers yet. Spin the wheel with 100 points to win one. <a href="rewards.php">Go to rewards</a></p><?php } ?>
  <?php foreach($v as $x){ ?>
    <div class="voucher <?=$x['status']?>">
      <div><strong><?=e($x['label'])?></strong><small><?=$x['status']==='used'?'Already used':'Ready to use'?></small></div>
      <?php if($x['status']==='unused' && $x['kind']!=='meal'){ ?><a class="btn solid" href="reserve.php?voucher=<?=$x['id']?>">Use voucher</a>
      <?php } elseif($x['status']==='unused'){ ?><small>Show this at the front desk during your stay.</small><?php } ?>
    </div>
  <?php } ?>
</main>
<?php bottom();
