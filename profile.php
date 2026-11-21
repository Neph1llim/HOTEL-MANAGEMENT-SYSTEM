<?php require 'config.php'; $u=require_login(); top('Account profile'); ?>
<main class="page narrow">
  <h1 class="mono"><?=e($u['name'])?>
    <?php if($u['is_vip']){ ?><span class="vip-tag big">&#9819; VIP</span><button class="qmark" data-modal="vipInfo" aria-label="What does VIP include?">?</button><?php } ?></h1>
  <dl class="facts">
    <div><dt>Email</dt><dd><?=e($u['email'])?></dd></div>
    <div><dt>Mobile number</dt><dd><?=e($u['phone'])?></dd></div>
    <div><dt>Member since</dt><dd><?=date('F j, Y',strtotime($u['created_at']))?></dd></div>
    <div><dt>Total spent</dt><dd><?=peso($u['total_spent'])?></dd></div>
    <div><dt>Total check-in days</dt><dd><?=(int)$u['total_days']?></dd></div>
    <div><dt>Reward points</dt><dd><?=number_format($u['points'])?></dd></div>
  </dl>
  <a class="btn outline" href="rewards.php">SPEND POINTS ON THE WHEEL</a>
</main>
<?php include 'vip_dialog.php'; bottom();
