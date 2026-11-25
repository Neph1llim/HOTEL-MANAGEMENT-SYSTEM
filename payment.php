<?php require 'config.php';
$ref = $_GET['ref'] ?? '';
if (empty($_SESSION['gid'])) {
    header('Location: login.php?next=' . urlencode('payment.php?ref=' . $ref));
    exit;
}
$s = $pdo->prepare('SELECT r.*,m.name room FROM reservations r JOIN rooms m ON m.id=r.room_id WHERE r.ref=? AND r.guest_id=?');
$s->execute([$ref, $_SESSION['gid']]);
$r = $s->fetch();
$pending = false;
$rej = null;
if ($r) {
    $s = $pdo->prepare('SELECT COUNT(*) FROM payments WHERE reservation_id=? AND status="pending"');
    $s->execute([$r['id']]);
    $pending = (bool) $s->fetchColumn();
    $s = $pdo->prepare('SELECT MAX(created_at) FROM payments WHERE reservation_id=? AND status="rejected"');
    $s->execute([$r['id']]);
    $rej = $s->fetchColumn();
}
$e = fn($v) => htmlspecialchars((string) $v);
/* back button returns to the reservation form with the same room, dates and guests filled in */
$back = 'make-reservation.php' . ($r ? '?' . http_build_query(['room' => $r['room_id'], 'ci' => $r['checkin'], 'co' => $r['checkout'], 'pax' => $r['pax']]) : '');
$bodyClass = 'flow';
include 'head.php'; ?>
<section class="flowhero">
    <div class="wrap">
        <p class="steps"><span data-t>1 Reservation</span><span>›</span><b data-t>2 Payment</b></p>
        <h1 data-t>Secure your reservation request.</h1>
        <p data-t>Pay the 50% downpayment, then upload your proof of payment so the hotel can verify it.</p>
    </div>
</section>
<section class="wrap flowbody narrowflow">
    <p class="paytop"><a class="btn gold sm" href="<?= $e($back) ?>" data-t>← Back to reservation</a></p>
<?php if (!$r): ?>
    <div class="callout err"><b data-t>Reservation not found.</b> <a href="make-reservation.php" data-t>Make a new reservation</a></div>
<?php elseif ($r['status'] == 'cancelled'): ?>
    <div class="callout err"><b data-t>This reservation was cancelled, so it can no longer be paid.</b> <a href="make-reservation.php" data-t>Make a new reservation</a></div>
<?php elseif ($r['status'] != 'pending'): ?>
    <div class="callout ok"><b data-t>This reservation is already paid and confirmed.</b></div>
    <p class="payactions"><a class="btn" href="reservations.php" data-t>View my reservations</a></p>
<?php else:
    $exp = date('M j, g:i A', strtotime(max($r['created_at'], (string) $rej)) + HOLD_HOURS * 3600); ?>
    <div class="card paycard pay">
        <h2 data-t>Submit your 50% downpayment proof</h2>
        <div class="paygrid">
            <div class="qrbox">
                <img class="qr" src="assets/img/payment-qr.png" alt="Hotel payment QR code (add it at assets/img/payment-qr.png)">
                <p class="mu"><small data-t>Scan with GCash or your banking app.</small></p>
            </div>
            <div class="callouts">
                <div class="callout info">
                    <b data-t>Reservation</b> <?= $e($r['ref']) ?><br>
                    <?= $e($r['room']) ?> · <?= $e($r['checkin']) ?> → <?= $e($r['checkout']) ?> · <?= (int) $r['pax'] ?> <span data-t>Guests</span><br>
                    <span data-t>Total</span> <?= peso($r['total']) ?> · <b><span data-t>Downpayment</span> <?= peso($r['downpayment']) ?></b>
                </div>
                <?php if ($rej): ?><div class="callout err" data-t>Your last proof was rejected. Please upload a clear proof of payment.</div><?php endif; ?>
                <?php if ($pending): ?>
                    <div class="callout warn"><span data-t>Your payment is already waiting to be verified.</span></div>
                <?php else: ?>
                    <div class="callout warn"><span data-t>Pay before</span> <?= $e($exp) ?> <span data-t>or the room will be released.</span></div>
                <?php endif; ?>
            </div>
        </div>
        <?php if (!$pending): ?>
        <form class="pf form payform" data-ref="<?= $e($r['ref']) ?>" novalidate>
            <label class="full"><span data-t>Reference number / GCash transaction reference</span><input name="ref_no" required></label>
            <label><span data-t>Amount paid</span><input type="number" name="amount" step="0.01" min="0.01" value="<?= $e($r['downpayment']) ?>" required></label>
            <label><span data-t>Proof of payment (PNG or JPG/JPEG, max 3 MB)</span><input type="file" name="proof" accept=".png,.jpg,.jpeg,image/png,image/jpeg" required></label>
            <button class="btn full" data-t>Submit payment</button>
        </form>
        <p class="err"></p>
        <?php else: ?>
        <p class="payactions"><a class="btn" href="reservations.php" data-t>View my reservations</a></p>
        <?php endif; ?>
    </div>
<?php endif; ?>
</section>
<?php include 'foot.php'; ?>
