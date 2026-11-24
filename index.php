<?php require 'config.php';
include 'head.php';
$rate = [];
foreach ($pdo->query('SELECT type,MIN(rate) r FROM rooms WHERE active=1 GROUP BY type')->fetchAll() as $x)
    $rate[$x['type']] = number_format($x['r']); ?>
<section class="hero">
    <h1 data-t>Rest at the foot of the mountains</h1>
    <p data-t>A quiet eco-chic retreat for families, travelers and celebrations.</p><a class="btn" href="reserve.php"
        data-t>Book now</a>
</section>
<section id="about-us" class="creamband">
    <div class="wrap altblock">
        <div class="alttext">
            <p class="eyebrow" data-t>About us</p>
            <h2 data-t>A quiet retreat at the foot of the mountains</h2>
            <p data-t>Bella Vista Hotel & Events Place is a premier eco-chic boutique hotel and events destination at the scenic foothills of the mountains. It offers a tranquil, elegant and exclusive retreat for families, tourists and corporate clients, away from the hustle and bustle of the city.</p>
            <a class="btn ghost sm" href="rooms.php" data-t>See our rooms</a>
        </div>
        <div class="photo" role="img" aria-label="Bella Vista Hotel" style="background-image:url(assets/img/about.jpg),url(assets/img/g1.jpg),linear-gradient(135deg,var(--pri),var(--acc))"></div>
    </div>
    <div id="history" class="wrap altblock rev">
        <div class="alttext">
            <p class="eyebrow" data-t>Our history</p>
            <h2 data-t>From family resthouse to boutique hotel</h2>
            <p data-t>Established in 2021, Bella Vista began as an exclusive family-owned resthouse. Growing demand for intimate celebrations and restful getaways turned it into a boutique hotel and events venue, known for warm Filipino hospitality, picturesque views and top-tier service.</p>
            <a class="btn ghost sm" href="reserve.php" data-t>Book your stay</a>
        </div>
        <div class="photo" role="img" aria-label="Bella Vista through the years" style="background-image:url(assets/img/history.jpg),url(assets/img/g2.jpg),linear-gradient(135deg,var(--acc),var(--pri))"></div>
    </div>
</section>
<section id="rooms" class="wrap">
    <h2 data-t>Rooms and event hall</h2>
    <div class="grid">
        <?php if (isset($rate['standard'])): ?>
            <article class="card">
                <h3 data-t>Standard Rooms</h3>
                <p><span data-t>2 to 3 guests</span> · ₱<?= $rate['standard'] ?> <span data-t>per night</span></p>
                <p class="mu" data-t>₱1,000 extra for a 3rd guest</p>
            </article><?php endif;
        if (isset($rate['family'])): ?>
            <article class="card">
                <h3 data-t>Family Rooms</h3>
                <p><span data-t>4 to 5 guests</span> · ₱<?= $rate['family'] ?> <span data-t>per night</span></p>
                <p class="mu" data-t>Covers up to 5 guests</p>
            </article><?php endif;
        if (isset($rate['hall'])): ?>
            <article class="card">
                <h3 data-t>Event Hall</h3>
                <p><span data-t>Up to 30 guests</span> · ₱<?= $rate['hall'] ?> <span data-t>per night</span></p>
                <p class="mu" data-t>₱500 per guest beyond 30, up to 40</p>
            </article><?php endif; ?>
    </div>
    <p><a class="btn ghost" href="rooms.php" data-t>See room photos</a></p>
</section>
<section class="wrap">
    <h2 data-t>Gallery</h2>
    <div class="gal">
        <?php for ($i = 1; $i <= 6; $i++)
            echo "<div style=\"background-image:url(assets/img/g$i.jpg),linear-gradient(135deg,var(--pri),var(--acc))\"></div>"; ?>
    </div>
</section>
<section class="wrap">
    <p class="mu"><span data-t>Downpayment, cancellation and house rules are in our</span> <a href="policies.php" data-t>Policies</a>.</p>
</section>
<section id="book" class="wrap">
    <h2 data-t>Book your stay</h2>
    <p class="mu"><span data-t>Check-in</span> 2:00 PM &middot; <span data-t>Check-out</span> 12:00 NN</p>
    <form id="qb" class="form" action="reserve.php" method="get"><label><span data-t>Check-in</span><input type="date"
                name="ci" required></label><label><span data-t>Check-out</span><input type="date" name="co"
                required></label>
        <label><span data-t>Guests</span><input type="number" name="pax" min="1" max="40" value="2"
                required></label><button class="btn" data-t>Check availability</button>
    </form>
</section>
<?php include 'foot.php'; ?>