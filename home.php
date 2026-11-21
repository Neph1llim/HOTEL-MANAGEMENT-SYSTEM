<?php require 'config.php'; $u=require_login(); top('Welcome',['banner'=>true]); ?>
<section class="hero">
  <?=img_slot('hero-bg.jpg','Hero background: leaves / greenery','hero-bg')?>
  <div class="hero-frame">
    <?=img_slot('hero-lobby.jpg','Hero photo: hotel lobby with green wall','hero-photo')?>
    <div class="hero-copy">
      <h1>Book your stay at Bella Vista</h1>
      <p>Rest, celebrate and unwind at the foot of the mountains.</p>
      <a class="btn ghost" href="reserve.php">RESERVE</a>
    </div>
  </div>
</section>

<section class="intro">
  <svg class="home-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 11 12 4l8 7v9H4z" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M10 20v-6h4v6" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
  <h2 class="mono">A peaceful eco-chic escape<br>away from the city</h2>
</section>

<section class="split">
  <?=img_slot('rooms-feature.jpg','Rooms section: bedroom photo','rounded tall')?>
  <div>
    <h3 class="mono small">Rooms</h3>
    <h2 class="mono">Stay with us</h2>
    <p class="mono tiny">Bella Vista is an eco-chic boutique hotel with warm Filipino hospitality. Choose the room that fits your group.</p>
    <ul class="linklist">
      <li><a href="reserve.php?type=standard">Standard Room (2 to 3 guests)</a></li>
      <li><a href="reserve.php?type=family">Family Room (up to 6 guests)</a></li>
      <li><a href="reserve.php?type=event">Event Hall (30 to 40 guests)</a></li>
    </ul>
    <a class="btn outline mono" href="rooms.php">view all rooms</a>
  </div>
</section>

<section class="gallery" aria-label="Photo gallery">
  <?php foreach([1=>'Lobby lounge',2=>'Garden view',3=>'Standard room',4=>'Family room',5=>'Event hall',6=>'Mountain view'] as $i=>$t) echo img_slot("gallery-$i.jpg","Gallery photo $i: $t",'gal'); ?>
</section>

<section class="feature">
  <div><h2>Party</h2><p class="mono tiny">Gather your friends at our Event Hall, which seats up to 30 guests and up to 40 with a small per-guest fee. Bring your own playlist and make a night of it.</p>
    <a class="btn outline" href="reserve.php?type=event">PARTY WITH US</a></div>
  <?=img_slot('party.jpg','Party photo: friends toasting','sq')?>
</section>
<section class="feature flip">
  <?=img_slot('celebrate.jpg','Celebrate photo: group celebrating','sq')?>
  <div><h2>Celebrate</h2><p class="mono tiny">Birthdays, reunions, team events and small weddings fit right in. Book the hall for the night at ₱15,000 and we will help you settle in.</p>
    <a class="btn outline" href="reserve.php?type=event">FUN WITH US</a></div>
</section>
<section class="feature">
  <div><h2>Sleep</h2><p class="mono tiny">Check in from 2:00 PM and sleep soundly in a quiet, mountain-fresh room. Quiet hours start at 10:00 PM so everyone can rest.</p>
    <a class="btn outline" href="reserve.php?type=standard">REST WITH US</a></div>
  <?=img_slot('sleep.jpg','Sleep photo: guest resting on pillow','sq')?>
</section>

<section class="about">
  <?=img_slot('about-bg.jpg','About section background: leaves','about-bg')?>
  <article class="about-card">
    <svg class="home-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 11 12 4l8 7v9H4z" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M10 20v-6h4v6" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
    <h2 class="mono">ABOUT THE HOTEL<br>(HISTORY)</h2>
    <p class="mono tiny">Bella Vista Hotel &amp; Events Place is a premier eco-chic boutique hotel and events destination nestled at the scenic foothills of the mountains. It offers a tranquil, elegant and exclusive retreat for families, tourists and corporate clients looking for a peaceful sanctuary away from the hustle and bustle of the city.</p>
    <p class="mono tiny">Established in 2021, Bella Vista began as an exclusive family-owned resthouse. Due to growing demand for intimate celebrations and restful getaways, it was expanded and transformed into a boutique hotel and events venue. Today, it is celebrated for its warm Filipino hospitality, picturesque views, and top-tier service for both accommodations and special events.</p>
  </article>
</section>
<?php bottom();
