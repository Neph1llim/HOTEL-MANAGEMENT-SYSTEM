<?php require 'config.php';
include 'head.php';
$rooms = $pdo->query('SELECT id,name,type,rate,base_cap,max_cap FROM rooms WHERE active=1 ORDER BY id')->fetchAll(); ?>
<section id="make" class="wrap">
    <h1 data-t>Make a reservation</h1>
    <p class="mu"><span data-t>Check-in</span> 2:00 PM &middot; <span data-t>Check-out</span> 12:00 NN</p>
    <div class="mrgrid">
        <form id="mr" class="form mrform" autocomplete="off" data-rooms='<?= htmlspecialchars(json_encode($rooms), ENT_QUOTES) ?>'>
            <label class="full"><span data-t>Room</span><select name="room" required></select></label>
            <label><span data-t>Check-in</span><input type="date" name="ci" required></label>
            <label><span data-t>Check-out</span><input type="date" name="co" required></label>
            <label><span data-t>Guests</span><input type="number" name="pax" min="1" max="40" value="2" required></label>
            <label><span data-t>Your vouchers</span><select name="vsel">
                    <option value="" data-t>None</option>
                </select></label>
            <label class="full"><span data-t>Voucher code</span><input name="voucher" placeholder="VCH-12" maxlength="20"
                    spellcheck="false" autocapitalize="characters"></label>
            <p id="vmsg" class="full mu" aria-live="polite"></p>
        </form>
        <aside class="card sumcard" aria-live="polite">
            <h2 data-t>Your Reservation Summary</h2>
            <div id="sum"></div>
            <p id="smsg" class="err"></p>
            <button class="btn" id="reserveBtn" type="button" disabled data-t>Confirm reservation</button>
        </aside>
    </div>
    <div id="done"></div>
</section>
<dialog id="tc">
    <h3 data-t>Terms and conditions</h3>
    <ol>
        <li data-t>Downpayment: A downpayment secures your reservation. The balance is paid at check-in.</li>
        <li data-t>Cancellation: Cancellations less than 3 days before check-in are non-refundable.</li>
        <li data-t>Damage: Guests are billed for any damage or loss to hotel property, furniture or fixtures.</li>
        <li data-t>No smoking: Smoking inside guest rooms is prohibited. Violations are fined ₱2,000.</li>
        <li data-t>Quiet hours: Keep noise low after 10:00 PM around the accommodation areas.</li>
    </ol>
    <label class="chk"><input type="checkbox" id="agree"><span data-t>I agree to the terms and conditions</span></label>
    <div class="row"><button class="btn" id="confirm" data-t>Confirm reservation</button><button class="btn ghost"
            id="cancelTc" data-t>Cancel</button></div>
</dialog>
<?php include 'foot.php'; ?>
