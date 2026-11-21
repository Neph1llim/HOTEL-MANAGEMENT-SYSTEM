<?php require 'config.php'; $u=require_login(); $rooms=all("SELECT * FROM rooms ORDER BY id"); top('All rooms'); ?>
<main class="page">
  <h1 class="mono">All rooms</h1>
  <p>Check-in 2:00 PM. Check-out 12:00 NN. Open Monday to Sunday.</p>
  <div class="room-grid">
  <?php foreach($rooms as $r){ ?>
    <article class="room-card">
      <?=img_slot('room-'.strtolower($r['code']).'.jpg',$r['name'].' photo','rounded')?>
      <h3><?=e($r['name'])?></h3>
      <p class="mono tiny"><?=e($r['description'])?></p>
      <p class="mono tiny"><strong>Capacity:</strong> <?=$r['min_cap']?> to <?=$r['max_cap']?> guests<br><strong>Rate:</strong> <?=peso($r['base_price'])?> per night</p>
      <a class="btn outline" href="reserve.php?room=<?=$r['id']?>">RESERVE</a>
    </article>
  <?php } ?>
  </div>
  <h2 id="pricing" class="mono">Pricing</h2>
  <div class="table-wrap"><table class="tbl">
    <tr><th>Room</th><th>Names</th><th>Guests</th><th>Rate per night</th><th>Extra guest fee</th></tr>
    <tr><td>Standard</td><td>Room A, B, C</td><td>2 to 3</td><td>₱3,500</td><td>₱1,000 for a 3rd guest</td></tr>
    <tr><td>Family</td><td>FR1, FR2, FR3</td><td>4 to 6</td><td>₱6,000 fixed</td><td>None</td></tr>
    <tr><td>Event Hall</td><td>Event Hall</td><td>30 to 40</td><td>₱15,000</td><td>₱500 per guest over 30 (max 40)</td></tr>
  </table></div>
  <p class="mono tiny">VIP members get 20% off all hotel expenses. Vouchers are limited to one per booking.</p>
</main>
<?php bottom();
