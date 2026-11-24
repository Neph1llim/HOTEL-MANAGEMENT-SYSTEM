<?php
$page = 'walkin';
$title = 'Walk-in booking';
$sub = 'For a guest at the front desk. The booking is approved straight away and the downpayment is recorded as cash received.';
require __DIR__ . '/admin-top.php';
?>
            <form id="wf" class="panel wform"><label>Guest name<input name="name" required
                        maxlength="80"></label><label>Mobile number<input name="phone" required
                        maxlength="30"></label><label>Email (optional)<input type="email" name="email"
                        maxlength="100"></label>
                <label>Room<select name="room" required></select></label><label>Check-in<input type="date" name="ci"
                        required></label><label>Check-out<input type="date" name="co"
                        required></label><label>Guests<input type="number" name="pax" min="1" value="2"
                        required></label>
                <label>Cash received (₱)<input type="number" name="amount" min="1" step="0.01"
                        placeholder="Downpayment"></label><button class="btn">Book and approve</button>
                <p id="wq" class="wq" hidden></p>
                <p id="wmsg" class="wmsg"></p>
            </form>
<?php require __DIR__ . '/admin-bottom.php'; ?>
