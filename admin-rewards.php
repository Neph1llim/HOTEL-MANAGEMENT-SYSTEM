<?php
$page = 'rewards';
$title = 'Rewards';
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
            <div class="panel tw">
                <table id="rtt"></table>
            </div>
            <p id="rtmsg" class="mu" aria-live="polite"></p>
<?php require __DIR__ . '/admin-bottom.php'; ?>
