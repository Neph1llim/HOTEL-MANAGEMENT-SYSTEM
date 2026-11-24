<?php
$page = 'overview';
$title = 'Overview';
$sub = 'Bookings and payments that need your attention this month.';
require __DIR__ . '/admin-top.php';
?>
            <div id="stats" class="kpis"></div>
            <div class="panel ovcard">
                <div class="ovh"><h2>Payments to verify<span id="ovpn" class="ovn" hidden></span></h2><a href="admin-payments.php">All payments</a></div>
                <div id="ovpays"></div>
            </div>
            <div class="panel ovcard">
                <div class="ovh"><h2>Reservations to action</h2><a href="admin-reservations.php">All reservations</a></div>
                <div id="ovres"></div>
            </div>
            <h2>Booked value by room type</h2>
            <div id="bytype" class="panel"></div>
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
                </div>
            </section>
            <?php require __DIR__ . '/admin-refund-dialog.php'; ?>
<?php require __DIR__ . '/admin-bottom.php'; ?>
