<?php
$page = 'reports';
$title = 'Reports';
require __DIR__ . '/admin-top.php';
?>
            <h2>Printable report</h2>
            <form id="rpf" class="panel wform compact" action="report.php" method="get" target="_blank"><label>From<input
                        type="date" name="from" required></label><label>To<input type="date" name="to"
                        required></label>
                <label>Date refers to<select name="by">
                        <option value="checkin">Check-in date</option>
                        <option value="booked">Date booked</option>
                    </select></label><button class="btn">Open report</button>
            </form>
            <section class="an" aria-labelledby="an-h">
                <div class="anh"><h2 id="an-h">Analytics</h2><span id="anyear" class="mu"></span></div>
                <div class="angrid">
                    <div class="panel ancard">
                        <div class="anct"><h3>Revenue by month</h3><span id="anleg1" class="leg"></span></div>
                        <div id="chmonth"></div>
                    </div>
                    <div class="panel ancard">
                        <div class="anct"><h3>Cumulative revenue</h3><span id="anleg2" class="leg"></span></div>
                        <div id="chcum"></div>
                    </div>
                    <div class="panel ancard wide">
                        <div class="anct"><h3>Occupancy by room type</h3><span id="anleg3" class="leg"></span></div>
                        <div id="choc"></div>
                    </div>
                </div>
            </section>
<?php require __DIR__ . '/admin-bottom.php'; ?>
