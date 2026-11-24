<?php require 'config.php';
$bodyClass = 'flow';
include 'head.php';
$rooms = $pdo->query('SELECT id,name,type,rate,base_cap,max_cap FROM rooms WHERE active=1 ORDER BY id')->fetchAll(); ?>
<section class="flowhero">
    <div class="wrap">
        <p class="steps"><b data-t>1 Reservation</b><span>›</span><span data-t>2 Payment</span></p>
        <h1 data-t>Let’s plan your Bella Vista experience.</h1>
        <p data-t>Choose your room and dates. You will upload your 50% downpayment proof on the next page.</p>
    </div>
</section>
<section id="make" class="wrap flowbody">
    <p class="mu"><a href="reserve.php" data-t>← Back to Check availability</a></p>
    <div class="mrgrid">
        <form id="mr" class="card mrform" novalidate autocomplete="off" data-rooms='<?= htmlspecialchars(json_encode($rooms), ENT_QUOTES) ?>'>
            <h2 data-t>Reservation information</h2>
            <label class="full"><span data-t>Accommodation / Room</span><select name="room" required></select></label>
            <label><span data-t>Check-in</span><input type="date" name="ci" required></label>
            <label><span data-t>Check-out</span><input type="date" name="co" required></label>
            <label class="full"><span data-t>Number of guests</span><input type="number" name="pax" min="1" max="40" value="2" required></label>
            <details class="full vbox" id="vbox">
                <summary data-t>Have a voucher? (optional)</summary>
                <div class="vgrid">
                    <label><span data-t>Your vouchers</span><select name="vsel"><option value="" data-t>None</option></select></label>
                    <label><span data-t>Voucher code</span><input name="voucher" placeholder="VCH-12" maxlength="20" spellcheck="false" autocapitalize="characters"></label>
                </div>
                <p id="vmsg" class="mu" aria-live="polite"></p>
            </details>
            <label class="chk full"><input type="checkbox" name="agree" id="agree" value="1" aria-required="true"><span><span data-t>I agree to the</span> <a href="#" id="tcLink" data-t>terms and conditions</a> *</span></label>
            <p id="smsg" class="err full" aria-live="polite"></p>
            <button class="btn full" id="reserveBtn" type="submit" data-t>Continue to payment</button>
        </form>
        <aside class="card sumcard" aria-live="polite">
            <div class="rimg sumimg" id="sumimg" hidden></div>
            <h2 data-t>Your reservation summary</h2>
            <div id="sum"></div>
        </aside>
    </div>
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
    <div class="row"><button class="btn" id="tcAgree" type="button" data-t>I agree</button><button class="btn ghost" id="cancelTc" type="button" data-t>Close</button></div>
</dialog>
<?php include 'foot.php'; ?>
