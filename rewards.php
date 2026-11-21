<?php require 'config.php'; $u=require_login(); top('Points & rewards'); ?>
<main class="page narrow center">
  <h1 class="mono">My points &amp; rewards</h1>
  <p class="points"><strong id="pts"><?=number_format($u['points'])?></strong> points</p>
  <p class="mono tiny">You earn 1% of everything you spend after each completed stay. Each spin costs <?=SPIN_COST?> points.</p>
  <div class="wheel-box"><div class="pointer" aria-hidden="true"></div><canvas id="wheel" width="360" height="360" aria-label="Reward wheel"></canvas></div>
  <button id="spinBtn" class="btn solid" <?=$u['points']<SPIN_COST?'disabled':''?>>Spin for <?=SPIN_COST?> points</button>
  <p id="spinMsg" class="notice" hidden></p>
  <h2 class="mono">What you can win</h2>
  <ul class="odds">
    <li><span class="sw s0"></span>20% discount voucher <em>50%</em></li>
    <li><span class="sw s1"></span>50% discount voucher <em>30%</em></li>
    <li><span class="sw s2"></span>75% discount voucher + free breakfast, lunch and dinner stubs for 2 days <em>12.5%</em></li>
    <li><span class="sw s3"></span>Free Standard Room booking, 3 days 2 nights <em>5%</em></li>
    <li><span class="sw s4"></span>Free Family Room booking, 3 days 2 nights <em>2.4%</em></li>
    <li><span class="sw s5"></span>VIP status <button class="qmark" data-modal="vipInfo" aria-label="What does VIP include?">?</button> <em>0.1%</em></li>
  </ul>
  <p class="mono tiny">Free booking vouchers cover the room rate for up to 2 nights. Extra guest fees still apply.</p>
</main>
<?php include 'vip_dialog.php'; bottom();
