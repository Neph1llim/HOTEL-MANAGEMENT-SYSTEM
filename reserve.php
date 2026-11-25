<?php include 'head.php'; ?>
<style>
/* availability calendar: clear, theme-proof highlight for check-in, check-out and every night in between */
#cal .day.inr { background: color-mix(in srgb, #D4AF37 32%, var(--sf)); border-color: #D4AF37; color: var(--tx) }
#cal .day.inr small { color: var(--tx) }
#cal .day.pick { background: linear-gradient(135deg, #E8C868, #D4AF37 55%, #B88E28); border-color: #B88E28; color: #0A2214; outline: 0; box-shadow: 0 6px 16px -8px rgba(212, 175, 55, .9) }
#cal .day.pick small { color: #0A2214 }
.calact { margin: 1.2rem 0 0 }
</style>
<section id="book" class="wrap">
    <h1 data-t>Book your stay</h1>
    <p class="mu"><span data-t>Check-in</span> 2:00 PM &middot; <span data-t>Check-out</span> 12:00 NN</p>
    <form id="bf" class="form"><label><span data-t>Check-in</span><input type="date" name="ci"
                required></label><label><span data-t>Check-out</span><input type="date" name="co" required></label>
        <label><span data-t>Guests</span><input type="number" name="pax" min="1" max="40" value="2" required></label>
        <label><span data-t>Room</span><select id="croom" aria-label="Room">
                <option value="" data-t>All rooms (rooms free)</option>
            </select></label>
        <label><span data-t>Voucher</span><select name="voucher">
                <option value="" data-t>None</option>
            </select></label>
    </form>
</section>
<section id="availability" class="wrap">
    <h2 data-t>Check availability</h2>
    <p class="mu" data-t>Press and hold on your check-in date, then drag to your check-out date and release.</p>
    <div class="calbar"><button type="button" class="btn ghost sm" id="cprev"
            aria-label="Previous month">&lsaquo;</button><b id="cmon"></b><button type="button" class="btn ghost sm"
            id="cnext" aria-label="Next month">&rsaquo;</button></div>
    <div id="cal" class="cal"></div>
    <p class="mu" id="csel" aria-live="polite"></p>
    <p class="calact"><button class="btn" form="bf" data-t>Check availability</button></p>
</section>
<section id="roomresults" class="wrap">
    <p class="mu" id="rhint" data-t>Results update automatically as you change the dates, guests or voucher. Fully booked rooms are hidden.</p>
    <div id="results" class="grid"></div>
</section>
<?php include 'foot.php'; ?>
