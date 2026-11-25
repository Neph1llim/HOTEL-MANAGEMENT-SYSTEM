<?php
/* Printable revenue and reservations report (staff only). Open from Admin > Reports. */
require 'config.php';
if (empty($_SESSION['admin'])) {
    http_response_code(401);
    exit('<p style="font:16px sans-serif;padding:2rem">Please sign in on the <a href="admin.php">admin page</a> first, then open the report again.</p>');
}
function h($s)
{
    return htmlspecialchars((string) $s, ENT_QUOTES);
}
function vd($d, $def)
{
    $x = date_create_from_format('Y-m-d', (string) $d);
    return ($x && $x->format('Y-m-d') == $d) ? $d : $def;
}
$from = vd($_GET['from'] ?? '', date('Y-m-01'));
$to = vd($_GET['to'] ?? '', date('Y-m-t'));
if ($from > $to)
    [$from, $to] = [$to, $from];
$by = ($_GET['by'] ?? '') == 'booked' ? 'booked' : 'checkin';
$col = $by == 'booked' ? 'DATE(r.created_at)' : 'r.checkin';
$q = $pdo->prepare("SELECT r.*,g.name guest,m.name room,m.type,(SELECT COALESCE(SUM(p.amount),0) FROM payments p WHERE p.reservation_id=r.id AND p.status='verified') paid FROM reservations r JOIN guests g ON g.id=r.guest_id JOIN rooms m ON m.id=r.room_id WHERE $col>=? AND $col<=? ORDER BY r.checkin,r.id");
$q->execute([$from, $to]);
$rows = $q->fetchAll();
$add = function (&$a, $k, $r) {
    $a[$k] ??= ['n' => 0, 'nights' => 0, 'rev' => 0];
    $a[$k]['n']++;
    $a[$k]['nights'] += $r['nights'];
    $a[$k]['rev'] += $r['total']; };
$cnt = ['pending' => 0, 'approved' => 0, 'completed' => 0, 'cancelled' => 0];
$rev = $collected = $refunded = $refPending = $outstanding = 0;
$type = [];
$room = [];
foreach ($rows as &$r) {
    $r['nights'] = (int) date_create($r['checkin'])->diff(date_create($r['checkout']))->format('%a');
    $cnt[$r['status']]++;
    $collected += $r['paid'];
    if ($r['refund_status'] == 'refunded')
        $refunded += $r['refund_amount'];
    if ($r['refund_status'] == 'pending')
        $refPending += $r['refund_amount'];
    $r['balance'] = in_array($r['status'], ['approved', 'completed']) ? balanceDue($r, $r['paid']) : 0;
    if (in_array($r['status'], ['approved', 'completed'])) {
        $rev += $r['total'];
        $outstanding += $r['balance'];
        $add($type, $r['type'], $r);
        $add($room, $r['room'], $r);
    }
}
unset($r);
ksort($type);
ksort($room);
/* ---- analytics: the same two charts as Admin > Reports (revenue by month, cumulative revenue), drawn as inline SVG so they print ----
   Revenue = verified payments minus refunds paid out, per calendar month. The charts cover the whole year of the report's "To" date
   and the year before it, whatever date range is chosen above. */
$yr = (int) substr($to, 0, 4);
$series = function ($y) use ($pdo) {
    $a = array_fill(0, 12, 0.0);
    $q = $pdo->prepare('SELECT MONTH(created_at) m,COALESCE(SUM(amount),0) s FROM payments WHERE status="verified" AND created_at>=? AND created_at<? GROUP BY MONTH(created_at)');
    $q->execute(["$y-01-01", ($y + 1) . '-01-01']);
    foreach ($q->fetchAll() as $x)
        $a[(int) $x['m'] - 1] += (float) $x['s'];
    $q = $pdo->prepare('SELECT MONTH(COALESCE(cancelled_at,created_at)) m,COALESCE(SUM(refund_amount),0) s FROM reservations WHERE refund_status="refunded" AND COALESCE(cancelled_at,created_at)>=? AND COALESCE(cancelled_at,created_at)<? GROUP BY MONTH(COALESCE(cancelled_at,created_at))');
    $q->execute(["$y-01-01", ($y + 1) . '-01-01']);
    foreach ($q->fetchAll() as $x)
        $a[(int) $x['m'] - 1] -= (float) $x['s'];
    return $a;
};
$nowY = (int) date('Y');
$lastI = $yr < $nowY ? 11 : ($yr == $nowY ? (int) date('n') - 1 : -1);   /* months of the chosen year that have started */
$prevS = $series($yr - 1);
$curS = $series($yr);
foreach ($curS as $i => $v)
    if ($i > $lastI)
        $curS[$i] = null;
$cum = function ($a) {
    $t = 0;
    return array_map(function ($v) use (&$t) {
        return $v === null ? null : ($t += $v); }, $a);
};
$niceMax = function ($v) {
    $p = 10 ** floor(log10($v));
    $f = $v / $p;
    return ($f <= 1 ? 1 : ($f <= 2 ? 2 : ($f <= 2.5 ? 2.5 : ($f <= 5 ? 5 : 10)))) * $p;
};
$kfmt = function ($v) {
    $a = abs($v);
    $s = fn($x) => rtrim(rtrim(number_format($x, 1, '.', ''), '0'), '.');
    $g = $a >= 1e6 ? $s($a / 1e6) . 'M' : ($a >= 1e3 ? $s($a / 1e3) . 'k' : (string) round($a));
    return ($v < 0 ? '-' : '') . '₱' . $g;
};
$lineChart = function ($labels, $sr) use ($niceMax, $kfmt) {
    $W = 640; $H = 270; $L = 58; $R = 14; $T = 14; $B = 30;
    $pw = $W - $L - $R; $ph = $H - $T - $B; $n = count($labels);
    $vals = [];
    foreach ($sr as $s)
        foreach ($s['data'] as $v)
            if ($v !== null)
                $vals[] = $v;
    $mx = max(0, ...$vals); $mn = min(0, ...$vals);
    $hi = $mx > 0 ? $niceMax($mx) : 1000; $lo = $mn < 0 ? -$niceMax(-$mn) : 0;
    $f = fn($x) => number_format($x, 1, '.', '');
    $x = fn($i) => $L + $pw * ($i + .5) / $n;
    $y = fn($v) => $T + $ph * (1 - ($v - $lo) / ($hi - $lo));
    $g = '';
    for ($i = 0; $i <= 4; $i++) {
        $v = $lo + ($hi - $lo) * $i / 4; $yy = $y($v);
        $g .= '<line class="gl" x1="' . $L . '" x2="' . ($W - $R) . '" y1="' . $f($yy) . '" y2="' . $f($yy) . '"/><text x="' . ($L - 7) . '" y="' . $f($yy + 4) . '" text-anchor="end">' . $kfmt($v) . '</text>';
    }
    foreach ($labels as $i => $l)
        $g .= '<text x="' . $f($x($i)) . '" y="' . ($H - 10) . '" text-anchor="middle">' . $l . '</text>';
    foreach ($sr as $s) {
        $d = ''; $prev = false;
        foreach ($s['data'] as $i => $v) {
            if ($v === null) { $prev = false; continue; }
            $d .= ($prev ? 'L' : 'M') . $f($x($i)) . ',' . $f($y($v)); $prev = true;
        }
        $g .= '<path class="ln ' . $s['cls'] . '" d="' . $d . '"/>';
        foreach ($s['data'] as $i => $v)
            if ($v !== null)
                $g .= '<circle class="dot ' . $s['cls'] . '" cx="' . $f($x($i)) . '" cy="' . $f($y($v)) . '" r="3"/>';
    }
    return '<svg class="chart" viewBox="0 0 ' . $W . ' ' . $H . '" role="img" aria-label="Revenue by month">' . $g . '</svg>';
};
$MS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$k = $lastI + 1;
$sumTo = fn($a) => array_sum(array_slice($a, 0, $k));
$cTot = $sumTo($curS); $pTot = $sumTo($prevS);
$anSummary = $k
    ? $yr . ': <b>' . peso($cTot) . '</b>' . ($yr < $nowY ? ' in total' : ' so far') . ' · ' . ($yr - 1) . ' same months: ' . peso($pTot) . ($pTot > 0 ? ' <b>' . ($cTot >= $pTot ? '▲' : '▼') . ' ' . abs(round(($cTot - $pTot) / $pTot * 100)) . '%</b>' : '')
    : 'No revenue recorded yet in ' . $yr;
/* occupancy by room type for the report period (stays overlapping the dates, approved + completed) */
$occRows = bookingsByType($pdo, $from, date('Y-m-d', strtotime("$to +1 day")));
$occMax = max(1, ...array_column($occRows, 'bookings'));
$legend = '<i class="lg a"></i>' . $yr . '<i class="lg b"></i>' . ($yr - 1);
$adm = $pdo->prepare('SELECT username FROM admins WHERE id=?');
$adm->execute([$_SESSION['admin']]);
$who = $adm->fetch()['username'] ?? 'staff';
$fmt = fn($d) => date('M j, Y', strtotime($d));
?><!doctype html>
<html lang="en" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Bella Vista | Report <?= h($from) ?> to <?= h($to) ?></title>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@500;600;700&text=0123456789&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="apple-touch-icon" href="assets/img/apple-touch-icon.png">
    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="rpbody">
    <main class="rp">
        <div class="noprint rpbar"><a class="btn ghost sm" href="admin-reports.php">&larr; Back to Reports</a>
            <form method="get" class="rpform"><label>From<input type="date" name="from" value="<?= h($from) ?>"
                        required></label><label>To<input type="date" name="to" value="<?= h($to) ?>" required></label>
                <label>Date refers to<select name="by">
                        <option value="checkin" <?= $by == 'checkin' ? ' selected' : '' ?>>Check-in date</option>
                        <option value="booked" <?= $by == 'booked' ? ' selected' : '' ?>>Date booked</option>
                    </select></label><button class="btn sm">Update</button>
            </form>
            <button class="btn sm" onclick="window.print()">Print report</button>
        </div>

        <header class="rph">
            <h1>Bella Vista Hotel &amp; Events Place</h1>
            <h2>Revenue and reservations report</h2>
            <p><b><?= h($fmt($from)) ?> to <?= h($fmt($to)) ?></b> ·
                <?= $by == 'booked' ? 'reservations booked in this period' : 'reservations with check-in in this period' ?><br><small>Generated
                    <?= h(date('M j, Y g:i A')) ?> by <?= h($who) ?></small></p>
        </header>

        <section class="rpk">
            <div><span>Reservations</span><b><?= count($rows) ?></b></div>
            <div><span>Booked value (approved + completed)</span><b><?= peso($rev) ?></b></div>
            <div><span>Payments received</span><b><?= peso($collected) ?></b></div>
            <div><span>Refunded</span><b><?= peso($refunded) ?></b></div>
            <div><span>Net received</span><b><?= peso($collected - $refunded) ?></b></div>
            <div><span>Balance still to collect</span><b><?= peso($outstanding) ?></b></div>
            <div><span>Refunds pending</span><b><?= peso($refPending) ?></b></div>
        </section>

        <section class="rpan">
            <h3>Analytics: revenue by month, <?= $yr ?> vs <?= $yr - 1 ?></h3>
            <p class="rpansum"><?= $anSummary ?></p>
            <div class="rp2">
                <div>
                    <div class="rpch"><b>Revenue by month</b><span class="leg"><?= $legend ?></span></div>
                    <?= $lineChart($MS, [['name' => (string) ($yr - 1), 'cls' => 'b', 'data' => $prevS], ['name' => (string) $yr, 'cls' => 'a', 'data' => $curS]]) ?>
                </div>
                <div>
                    <div class="rpch"><b>Cumulative revenue</b><span class="leg"><?= $legend ?></span></div>
                    <?= $lineChart($MS, [['name' => (string) ($yr - 1), 'cls' => 'b', 'data' => $cum($prevS)], ['name' => (string) $yr, 'cls' => 'a', 'data' => $cum($curS)]]) ?>
                </div>
            </div>
            <div class="rpoc">
                <div class="rpch"><b>Occupancy by room type</b><span class="leg"><?= h($fmt($from)) ?> to <?= h($fmt($to)) ?></span></div>
                <?php foreach ($occRows as $x)
                    echo '<div class="bar"><span>' . h($x['type']) . '</span><i style="width:' . ($x['bookings'] / $occMax * 100) . '%"></i><b>' . $x['bookings'] . ' <small class="mu">' . ($x['bookings'] == 1 ? 'booking' : 'bookings') . ' (' . $x['nights'] . ' night' . ($x['nights'] == 1 ? '' : 's') . ')</small></b></div>'; ?>
            </div>
            <p class="mu rpnote">Occupancy shows the number of bookings per room type in the report period (the dates above, whichever date option is chosen):
                approved and completed reservations with at least one night in the period; nights = the nights that fall inside it. The revenue charts use
                payments received minus refunds paid out, by calendar month, for the whole of <?= $yr ?> (the year of the report's "To" date) compared with
                <?= $yr - 1 ?>. They do not change with the date range above.</p>
        </section>

        <h3>Reservations by status</h3>
        <table class="rpt sm">
            <thead>
                <tr><?php foreach ($cnt as $k => $v)
                    echo '<th>' . ucfirst($k) . '</th>'; ?></tr>
            </thead>
            <tbody>
                <tr><?php foreach ($cnt as $v)
                    echo "<td>$v</td>"; ?></tr>
            </tbody>
        </table>

        <div class="rp2">
            <div>
                <h3>Booked value by room type</h3>
                <table class="rpt">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Bookings</th>
                            <th>Nights</th>
                            <th class="r">Booked value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($type as $k => $v)
                            echo '<tr><td>' . h(ucfirst($k)) . '</td><td>' . $v['n'] . '</td><td>' . $v['nights'] . '</td><td class="r">' . peso($v['rev']) . '</td></tr>';
                        if (!$type)
                            echo '<tr><td colspan="4">No approved or completed bookings in this period</td></tr>'; ?>
                    </tbody>
                </table>
            </div>
            <div>
                <h3>Booked value by room</h3>
                <table class="rpt">
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Bookings</th>
                            <th>Nights</th>
                            <th class="r">Booked value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($room as $k => $v)
                            echo '<tr><td>' . h($k) . '</td><td>' . $v['n'] . '</td><td>' . $v['nights'] . '</td><td class="r">' . peso($v['rev']) . '</td></tr>';
                        if (!$room)
                            echo '<tr><td colspan="4">No approved or completed bookings in this period</td></tr>'; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <h3>All reservations in this period</h3>
        <table class="rpt det">
            <thead>
                <tr>
                    <th>Ref</th>
                    <th>Guest</th>
                    <th>Room</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Nights</th>
                    <th class="r">Total</th>
                    <th class="r">Paid</th>
                    <th class="r">Balance</th>
                    <th>Status</th>
                    <th>Refund</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r) {
                    $rf = $r['refund_status'] == 'none' ? '' : ucfirst($r['refund_status']) . ' ' . peso($r['refund_amount']);
                    echo '<tr><td>' . h($r['ref']) . ($r['source'] == 'walkin' ? ' (walk-in)' : '') . '</td><td>' . h($r['guest']) . '</td><td>' . h($r['room']) . '</td><td>' . h($r['checkin']) . '</td><td>' . h($r['checkout']) . '</td><td>' . $r['nights'] . '</td><td class="r">' . peso($r['total']) . '</td><td class="r">' . peso($r['paid']) . '</td><td class="r">' . ($r['balance'] > 0 ? peso($r['balance']) : '') . '</td><td>' . h($r['status']) . '</td><td>' . h($rf) . '</td></tr>';
                }
                if (!$rows)
                    echo '<tr><td colspan="11">No reservations in this period</td></tr>'; ?>
            </tbody>
        </table>
        <p class="mu rpnote">Booked value is the total price of approved and completed reservations. Payments received
            are all verified payments (downpayments and balances) on the reservations listed, including cancelled ones
            where the hotel kept the downpayment. Net received = payments received minus refunds already paid out.
            Cancelled reservations never count toward booked value.</p>
    </main>
</body>

</html>