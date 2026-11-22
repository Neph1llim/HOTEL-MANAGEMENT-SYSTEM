<?php include 'head.php'; ?>
<section id="book" class="wrap"><h1 data-t>Book your stay</h1><p class="mu"><span data-t>Check-in</span> 2:00 PM &middot; <span data-t>Check-out</span> 12:00 NN</p>
<form id="bf" class="form"><label><span data-t>Check-in</span><input type="date" name="ci" required></label><label><span data-t>Check-out</span><input type="date" name="co" required></label>
<label><span data-t>Guests</span><input type="number" name="pax" min="1" max="40" value="2" required></label>

<label><span data-t>Voucher</span><select name="voucher"><option value="" data-t>None</option></select></label><button class="btn" data-t>Check availability</button></form>
<div id="results" class="grid"></div></section>
<dialog id="tc"><h3 data-t>Terms and conditions</h3><ol>
<li data-t>Downpayment: A downpayment secures your reservation. The balance is paid at check-in.</li>
<li data-t>Cancellation: Cancellations less than 3 days before check-in are non-refundable.</li>
<li data-t>Damage: Guests are billed for any damage or loss to hotel property, furniture or fixtures.</li>
<li data-t>No smoking: Smoking inside guest rooms is prohibited. Violations are fined ₱2,000.</li>
<li data-t>Quiet hours: Keep noise low after 10:00 PM around the accommodation areas.</li></ol>
<label class="chk"><input type="checkbox" id="agree"><span data-t>I agree to the terms and conditions</span></label>
<div class="row"><button class="btn" id="confirm" data-t>Confirm reservation</button><button class="btn ghost" id="cancelTc" data-t>Cancel</button></div></dialog>
<?php include 'foot.php'; ?>
