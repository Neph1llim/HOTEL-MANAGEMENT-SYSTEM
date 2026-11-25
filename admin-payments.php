<?php
$page = 'payments';
$title = 'Payments';
require __DIR__ . '/admin-top.php';
?>
            <div class="sh toolbar">
                <input id="psearch" class="search" type="search" placeholder="Search name, email, contact no. or reference no." aria-label="Search payments" autocomplete="off">
            </div>
            <div class="panel tw">
                <table id="pays"></table>
            </div>
<?php require __DIR__ . '/admin-bottom.php'; ?>
