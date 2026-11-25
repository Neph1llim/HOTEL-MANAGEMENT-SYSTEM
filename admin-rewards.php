<?php
$page = 'rewards';
$title = 'Rewards';
$sub = 'Set how many points guests earn and manage the rewards on the spin wheel. Add, edit or remove rewards, then press Save Reward Settings once to save everything together. A free room reward names the room and the number of nights it covers. Reward types are managed at the bottom of this page. The number on a VIP status reward is the discount VIP guests get on every booking, and it changes as soon as you save. Changes apply to the next spin; vouchers guests already won are not affected.';
require __DIR__ . '/admin-top.php';
?>
            <div id="rwp" class="panel wform compact">
                <b class="wfh">Points</b>
                <label>Points earned (% of total spent)<input id="rwpct" type="number" min="0" max="100" step="0.01"
                        required></label>
                <span id="rwhint" class="mu"></span>
            </div>
            <div class="panel tw">
                <table id="rwt"></table>
            </div>
            <p id="rwtotal" class="rwtotal"></p>
            <form id="rwaf" class="panel wform compact"><b class="wfh">Add a reward</b><label>Reward name<input name="label" required
                        maxlength="60"></label><label>Reward type<select name="kind"></select></label><label data-for="pct vip">Discount
                    (%)<input type="number" name="pct" min="1" max="100" step="1" value="10"></label><label data-for="free_room" hidden>Room<select
                        name="room"></select></label><label data-for="free_room" hidden>Free nights<input type="number" name="nights" min="1"
                        max="30" step="1" value="2"></label><label>Probability (%)<input type="number" name="chance" min="0" max="100"
                        step="0.01" value="0" required></label><button class="btn">Add reward</button></form>
            <div class="sh toolbar savebar">
                <div class="rwbtns">
                    <button id="rwdiscard" class="btn ghost" type="button" disabled>Discard Changes</button>
                    <button id="rwsave" class="btn" type="button" disabled>Save Reward Settings</button>
                </div>
                <span id="rwstate" class="mu"></span>
                <span id="rwmsg" class="err"></span>
            </div>
            <h2>Reward types</h2>
            <p class="mu">A reward type is the kind of reward you pick in the Reward type list above. A <b>Discount voucher</b> type gives a % off, a
                <b>Free room stay</b> type gives free nights in the room you choose for each reward (name the reward after the room and nights so guests can tell them apart), and a
                <b>Free food</b> type gives a food voucher (name the reward after the food, such as &ldquo;Free breakfast for two&rdquo;).
                You can rename any of them. Types save straight away (no need to press Save Reward Settings).</p>
            <div class="panel tw">
                <table id="rtt"></table>
            </div>
            <p id="rtmsg" class="mu" aria-live="polite"></p>
<?php require __DIR__ . '/admin-bottom.php'; ?>
