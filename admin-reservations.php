<?php
$page = 'reservations';
$title = 'Reservations';
require __DIR__ . '/admin-top.php';
?>
            <div class="sh toolbar">
                <input id="rsearch" class="search" type="search" placeholder="Search name, email, contact no. or reference no." aria-label="Search reservations" autocomplete="off">
                <label class="mu" for="rfilter">Show</label>
                <select id="rfilter">
                    <option value="">All statuses</option>
                    <option>pending</option>
                    <option>approved</option>
                    <option>completed</option>
                    <option>cancelled</option>
                </select>
            </div>
            <div class="panel tw">
                <table id="res"></table>
            </div>
            <?php require __DIR__ . '/admin-refund-dialog.php'; ?>
<?php require __DIR__ . '/admin-bottom.php'; ?>
