<?php
$page = 'rooms';
$title = 'Rooms & Rates';
$sub = 'Edit any rows, then press Save All Changes once to save them together. A new rate applies to future bookings only; existing reservations keep the price they were made at. Switch a room off to stop new online bookings (for maintenance, say).';
require __DIR__ . '/admin-top.php';
?>
            <div class="panel tw">
                <table id="rooms"></table>
            </div>
            <div class="sh toolbar savebar">
                <button id="rsave" class="btn" type="button" disabled>Save All Changes</button>
                <span id="rstate" class="mu"></span>
                <span id="rmsg" class="err"></span>
            </div>
            <form id="raf" class="panel wform compact"><b class="wfh">Add a room</b><label>Name<input name="name" required
                        maxlength="30"></label><label>Type<select name="type">
                        <option value="standard">Standard</option>
                        <option value="family">Family</option>
                        <option value="hall">Event hall</option>
                    </select></label><label>Rate (₱/night)<input type="number" name="rate" min="1"
                        required></label><label>Standard capacity<input type="number" name="base_cap" min="1"
                        value="2" required></label><label>Maximum capacity<input type="number" name="max_cap"
                        min="1" value="3" required></label><button class="btn">Add room</button></form>
<?php require __DIR__ . '/admin-bottom.php'; ?>
