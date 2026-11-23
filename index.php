<?php include 'head.php'; ?>
<section class="hero">
    <h1 data-t>Rest at the foot of the mountains</h1>
    <p data-t>A quiet eco-chic retreat for families, travelers and celebrations.</p><a class="btn" href="reserve.php" data-t>Book now</a>
</section>
<section id="about" class="wrap two">
    <div>
        <h2 data-t>About us</h2>
        <p data-t>Bella Vista Hotel & Events Place is a premier eco-chic boutique hotel and events destination at the scenic foothills of the mountains. It offers a tranquil, elegant and exclusive retreat for families, tourists and corporate clients, away from the hustle and bustle of the city.</p>
    </div>
    <div>
        <h2 data-t>Our history</h2>
        <p data-t>Established in 2021, Bella Vista began as an exclusive family-owned resthouse. Growing demand for intimate celebrations and restful getaways turned it into a boutique hotel and events venue, known for warm Filipino hospitality, picturesque views and top-tier service.</p>
    </div>
</section>
<section id="rooms" class="wrap">
    <h2 data-t>Rooms and event hall</h2>
    <div class="grid">
        <article class="card">
            <h3 data-t>Standard Rooms</h3>
            <p data-t>2 to 3 guests · ₱3,500 per night</p>
            <p class="mu" data-t>₱1,000 extra for a 3rd guest</p>
        </article>
        <article class="card">
            <h3 data-t>Family Rooms</h3>
            <p data-t>4 to 5 guests · ₱6,000 per night</p>
            <p class="mu" data-t>Covers up to 5 guests</p>
        </article>
        <article class="card">
            <h3 data-t>Event Hall</h3>
            <p data-t>Up to 30 guests · ₱15,000 per night</p>
            <p class="mu" data-t>₱500 per guest beyond 30, up to 40</p>
        </article>
    </div>
    <p><a class="btn ghost" href="rooms.php" data-t>See room photos</a></p>
</section>
<section class="wrap">
    <h2 data-t>Gallery</h2>
    <div class="gal"><?php for ($i = 1; $i <= 6; $i++) echo "<div style=\"background-image:url(assets/img/g$i.jpg),linear-gradient(135deg,var(--pri),var(--acc))\"></div>"; ?></div>
</section>
<section id="terms" class="wrap">
    <h2 data-t>Terms and conditions</h2>
    <ol class="mu">
        <li data-t>Downpayment: A downpayment secures your reservation. The balance is paid at check-in.</li>
        <li data-t>Cancellation: Cancellations less than 3 days before check-in are non-refundable.</li>
        <li data-t>Damage: Guests are billed for any damage or loss to hotel property, furniture or fixtures.</li>
        <li data-t>No smoking: Smoking inside guest rooms is prohibited. Violations are fined ₱2,000.</li>
        <li data-t>Quiet hours: Keep noise low after 10:00 PM around the accommodation areas.</li>
    </ol>
</section>
<section id="book" class="wrap">
    <h2 data-t>Book your stay</h2>
    <p class="mu"><span data-t>Check-in</span> 2:00 PM &middot; <span data-t>Check-out</span> 12:00 NN</p>
    <form id="qb" class="form" action="reserve.php" method="get"><label><span data-t>Check-in</span><input type="date" name="ci" required></label><label><span data-t>Check-out</span><input type="date" name="co" required></label>
        <label><span data-t>Guests</span><input type="number" name="pax" min="1" max="40" value="2" required></label><button class="btn" data-t>Check availability</button>
    </form>
</section>
<?php include 'foot.php'; ?>