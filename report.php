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