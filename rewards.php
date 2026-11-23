<?php require 'config.php';
include 'head.php'; ?>
<section class="wrap narrow">
    <h1 data-t>My points and rewards</h1>
    <p id="rmsg" class="err"></p>
    <p class="pts"><b id="pts">0</b> <span data-t>points</span></p>
    <p class="mu" data-t>You earn 1% of what you spend after each completed stay. Each spin costs 100 points.</p>
    <div class="wheelbox"><i class="ptr"></i><canvas id="wheel" width="360" height="360"></canvas></div>
    <button class="btn" id="spin" disabled data-t>Spin for 100 points</button>
    <p id="win" class="win"></p>
    <h2 id="vouchers" data-t>Your vouchers</h2>
    <ul id="vl" class="list"></ul>
    <h2 data-t>What you can win</h2>
    <ul class="list"><?php foreach (PRIZES as $p) echo '<li>' . htmlspecialchars($p[0]) . '<b>' . ($p[3] / 100) . '%</b></li>'; ?></ul>
    <p class="mu" data-t>Free room vouchers cover the room rate for up to 2 nights. Extra guest fees still apply.</p>
</section>
<?php include 'foot.php'; ?>