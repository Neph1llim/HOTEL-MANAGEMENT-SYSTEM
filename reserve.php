<?php include 'head.php'; ?>
<style>
/* availability calendar: clear, theme-proof highlight for check-in, check-out and every night in between */
#cal .day.inr { background: color-mix(in srgb, #D4AF37 32%, var(--sf)); border-color: #D4AF37; color: var(--tx) }
#cal .day.inr small { color: var(--tx) }
#cal .day.pick { background: linear-gradient(135deg, #E8C868, #D4AF37 55%, #B88E28); border-color: #B88E28; color: #0A2214; outline: 0; box-shadow: 0 6px 16px -8px rgba(212, 175, 55, .9) }
#cal .day.pick small { color: #0A2214 }
/* title row: heading on the left, room dropdown on the right */
.booktop { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem 2rem; flex-wrap: wrap }
.booktop h1 { margin: 0 }
.roomsel { min-width: 260px; max-width: 100%; text-align: left }
/* one row of fields: check-in, check-out, guests, voucher */
#bf { grid-template-columns: repeat(4, minmax(0, 1fr)) }
@media (max-width: 800px) { #bf { grid-template-columns: repeat(2, minmax(0, 1fr)) } }
/* full-width calendar */
#availability .cal { max-width: none; width: 100%; gap: 8px }
#availability .day { min-height: clamp(3.6rem, 7vw, 5.2rem); font-size: .95rem }
#availability .day span { font-size: 1.05rem }
#availability .day small { font-size: .72rem }
#availability .dow { font-size: .8rem; padding: .3rem 0 }
/* room result tiles: always 3 per row, same size even when fewer than 3 are shown */
#results { grid-template-columns: repeat(3, minmax(0, 1fr)); align-items: stretch }
#results .card { display: flex; flex-direction: column }
#results .card .btn { margin-top: auto; text-align: center }
@media (max-width: 900px) { #results { grid-template-columns: repeat(2, minmax(0, 1fr)) } }
@media (max-width: 560px) { #results { grid-template-columns: 1fr } }
#results > .mu, #results > .err { grid-column: 1 / -1 }
.calbar { margin: 1rem 0 .8rem }
.calbar b { font-size: 1.05rem; min-width: 8rem }
#book { padding-bottom: 0 }
#availability { padding-top: 0 }
.vnote { margin: .8rem 0 0; padding: .6rem .9rem; color: #F5F2EB; background: #0A2214; border-left: 4px solid #D4AF37; border-radius: 8px }
</style>
<section id="book" class="wrap">
    <div class="booktop">
        <h1 data-t>Book your stay</h1>
        <label class="roomsel"><span data-t>Room</span><select id="croom" aria-label="Room">
                <option value="" data-t>All rooms (rooms free)</option>
            </select></label>
    </div>
    <h2 data-t style="margin-top:1.2rem">Check availability</h2>
    <p class="mu"><span data-t>Check-in</span> 2:00 PM &middot; <span data-t>Check-out</span> 12:00 NN</p>
    <form id="bf" class="form"><label><span data-t>Check-in</span><input type="date" name="ci"
                required></label><label><span data-t>Check-out</span><input type="date" name="co" required></label>
        <label><span data-t>Guests</span><input type="number" name="pax" min="1" max="40" value="2" required></label>
        <label><span data-t>Voucher</span><select name="voucher">
                <option value="" data-t>None</option>
            </select></label>
    </form>
    <p id="vnote" class="vnote" aria-live="polite" hidden></p>
</section>
<section id="availability" class="wrap">
    <div class="calbar"><button type="button" class="btn ghost sm" id="cprev"
            aria-label="Previous month">&lsaquo;</button><b id="cmon"></b><button type="button" class="btn ghost sm"
            id="cnext" aria-label="Next month">&rsaquo;</button></div>
    <div id="cal" class="cal"></div>
    <p class="mu" id="csel" aria-live="polite"></p>
</section>
<section id="roomresults" class="wrap">
    <p class="mu" id="rhint" data-t>Results update automatically as you change the dates, guests or voucher. Fully booked rooms are hidden.</p>
    <div id="results" class="grid"></div>
</section>
<?php include 'foot.php'; ?>
