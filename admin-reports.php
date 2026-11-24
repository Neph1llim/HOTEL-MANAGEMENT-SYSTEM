<?php
$page = 'reports';
$title = 'Reports';
$sub = 'Print a report for any date range.';
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
<?php require __DIR__ . '/admin-bottom.php'; ?>
