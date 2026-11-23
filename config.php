<?php
session_start();
date_default_timezone_set('Asia/Manila');
$pdo = new PDO('mysql:host=localhost;dbname=bellavista;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone='+08:00'"]);
if (!$pdo->query('SELECT COUNT(*) c FROM admins')->fetch()['c'])
   $pdo->prepare('INSERT INTO admins(username,pass) VALUES("admin",?)')->execute([password_hash('admin123', PASSWORD_DEFAULT)]);
// [label, kind, percent, weight out of 10000] - same order as the wheel in assets/app.js
const PRIZES = [['20% discount voucher', 'pct', 20, 5000], ['50% discount voucher', 'pct', 50, 3000], ['75% discount voucher + meal stubs', 'pct', 75, 1250], ['Free Standard Room, 2 nights', 'free_standard', 0, 500], ['Free Family Room, 2 nights', 'free_family', 0, 240], ['VIP status (5% off every booking)', 'vip', 0, 10]];
const SPIN_COST = 100;
const HOLD_HOURS = 24;   // an online reservation with no payment proof is released after this many hours
function j($d, $c = 200)
{
   http_response_code($c);
   header('Content-Type: application/json');
   echo json_encode($d);
   exit;
}
function peso($n)
{
   return '₱' . number_format((float) $n, 2);
}
function avail($pdo, $room, $ci, $co)
{
   $s = $pdo->prepare('SELECT COUNT(*) c FROM reservations WHERE room_id=? AND status IN("pending","approved") AND checkin<? AND checkout>?');
   $s->execute([$room, $co, $ci]);
   return !$s->fetch()['c'];
}
function quote($pdo, $rid, $ci, $co, $pax, $vid = null, $gid = null)
{
   $r = $pdo->prepare('SELECT * FROM rooms WHERE id=?');
   $r->execute([$rid]);
   $r = $r->fetch();
   $a = date_create($ci ?: 'x');
   $b = date_create($co ?: 'x');
   if (!$r || !$a || !$b)
      return ['error' => 'Invalid dates', 'fatal' => 1];
   if (!$r['active'])
      return ['error' => 'This room is not open for booking', 'fatal' => 1];
   $n = (int) $a->diff($b)->format('%r%a');
   if ($a < date_create('today'))
      return ['error' => 'Check-in cannot be in the past', 'fatal' => 1];
   if ($n < 1)
      return ['error' => 'Check-out must be at least 1 night after check-in (check-in 2:00 PM, check-out 12:00 NN)', 'fatal' => 1];
   if ($pax < 1 || $pax > $r['max_cap'])
      return ['error' => "Maximum capacity is {$r['max_cap']} guests"];
   /* extra-guest fee uses the room's standard capacity, so editing it in Admin > Rooms changes the price */
   $x = $r['type'] == 'standard' ? ($pax > $r['base_cap'] ? 1000 : 0) : ($r['type'] == 'hall' ? max(0, $pax - $r['base_cap']) * 500 : 0);
   $extra = $x * $n;
   $sub = $r['rate'] * $n;
   $d = 0;
   if ($vid) {
      $v = $pdo->prepare('SELECT * FROM vouchers WHERE id=? AND guest_id=? AND status="unused"');
      $v->execute([$vid, $gid]);
      $v = $v->fetch();
      if (!$v)
         return ['error' => 'Voucher not valid'];
      if ($v['kind'] == 'pct')
         $d = $sub * $v['pct'] / 100;
      elseif ($v['kind'] == 'free_' . $r['type'])
         $d = $r['rate'] * min($n, 2);
      else
         return ['error' => 'Voucher does not apply to this room'];
   } elseif ($gid) {
      $s = $pdo->prepare('SELECT vip FROM guests WHERE id=?');
      $s->execute([$gid]);
      if ($s->fetch()['vip'])
         $d = $sub * .05;
   }
   $t = $sub + $extra - $d;
   return ['nights' => $n, 'sub' => $sub, 'extra' => $extra, 'disc' => $d, 'total' => $t, 'down' => round($t / 2, 2)];
}
/* Guest self-cancel: only pending/approved reservations, up to the check-in day.
   Per the Terms & Conditions, cancelling less than 3 days before check-in is non-refundable. */
function cancelInfo($r)
{
   $in = date_create($r['checkin']);
   $today = date_create('today');
   if (!in_array($r['status'], ['pending', 'approved']) || !$in || $in < $today)
      return ['ok' => false, 'refundable' => false];
   return ['ok' => true, 'refundable' => (int) $today->diff($in)->format('%r%a') >= 3];
}

/* ---------- payments, balance, notifications ---------- */
function paidSum($pdo, $rid)
{
   $s = $pdo->prepare('SELECT COALESCE(SUM(amount),0) s FROM payments WHERE reservation_id=? AND status="verified"');
   $s->execute([$rid]);
   return (float) $s->fetch()['s'];
}
/* what the guest still owes: total price minus every verified payment (downpayment + cash at check-in) */
function balanceDue($r, $paid)
{
   return max(0, round((float) $r['total'] - (float) $paid, 2));
}
/* in-system message shown on the guest's My Reservations page (walk-in guests without an account get none) */
function notify($pdo, $gid, $msg)
{
   $pdo->prepare('INSERT INTO notifications(guest_id,msg) SELECT id,? FROM guests WHERE id=? AND pass IS NOT NULL')->execute([$msg, $gid]);
}

/* Cancel a reservation inside the caller's transaction.
   $by: guest | admin | expired.  $refundable: true when money paid should come back.
   Verified payments on a refundable cancellation become "refund pending" for the admin to settle.
   The voucher is returned unless the guest forfeited a payment by cancelling late. */
function cancelRes($pdo, $r, $by, $refundable)
{
   $paid = paidSum($pdo, $r['id']);
   $refund = ($refundable && $paid > 0) ? $paid : 0;
   $pdo->prepare('UPDATE reservations SET status="cancelled",cancel_reason=?,cancelled_at=?,refund_status=?,refund_amount=? WHERE id=?')
      ->execute([$by, date('Y-m-d H:i:s'), $refund > 0 ? 'pending' : 'none', $refund, $r['id']]);
   if ($r['voucher_id'] && ($refundable || $paid <= 0))
      $pdo->prepare('UPDATE vouchers SET status="unused" WHERE id=? AND guest_id=?')->execute([$r['voucher_id'], $r['guest_id']]);
   $ref = $r['ref'];
   $rf = $refund > 0 ? ' Your refund of ' . peso($refund) . ' is pending. The hotel will contact you.' : '';
   if ($by == 'expired')
      $m = "Reservation $ref was cancelled because no payment was received within " . HOLD_HOURS . " hours. The room has been released.";
   elseif ($by == 'admin')
      $m = "Reservation $ref was cancelled by the hotel.$rf";
   else
      $m = "You cancelled reservation $ref." . ($refund > 0 ? $rf : ($paid > 0 ? ' Per the Terms & Conditions, the downpayment is non-refundable.' : ''));
   notify($pdo, $r['guest_id'], $m);
   return $refund;
}
/* Mark an approved reservation completed and award 1% of the total as points. Returns the points, or false if it was not approved. */
function completeStay($pdo, $id)
{
   $own = !$pdo->inTransaction();
   if ($own)
      $pdo->beginTransaction();
   $s = $pdo->prepare('SELECT * FROM reservations WHERE id=? FOR UPDATE');
   $s->execute([$id]);
   $r = $s->fetch();
   if (!$r || $r['status'] != 'approved') {
      if ($own)
         $pdo->rollBack();
      return false;
   }
   $pdo->prepare('UPDATE reservations SET status="completed" WHERE id=?')->execute([$id]);
   $pts = (int) floor($r['total'] * 0.01);
   $pdo->prepare('UPDATE guests SET points=points+? WHERE id=?')->execute([$pts, $r['guest_id']]);
   notify($pdo, $r['guest_id'], "Thank you for staying with us! Reservation {$r['ref']} is completed and you earned $pts points.");
   if ($own)
      $pdo->commit();
   return $pts;
}

/* ---------- automatic rules (run on every request, so no cron job is needed) ----------
   1. An online reservation with no payment proof is cancelled HOLD_HOURS after it was made
      (or after its last rejected proof) and the room is released.
   2. An approved reservation becomes completed after check-out time (12:00 NN), and the points are awarded. */
function sweep($pdo)
{
   static $done = false;
   if ($done)
      return;
   $done = true;
   try {
      $cut = time() - HOLD_HOURS * 3600;
      $rows = $pdo->query('SELECT r.id,r.created_at,(SELECT MAX(p.created_at) FROM payments p WHERE p.reservation_id=r.id AND p.status="rejected") rej FROM reservations r WHERE r.status="pending" AND r.source="online" AND NOT EXISTS(SELECT 1 FROM payments p WHERE p.reservation_id=r.id AND p.status IN("pending","verified"))')->fetchAll();
      foreach ($rows as $x) {
         if (strtotime(max($x['created_at'], (string) $x['rej'])) > $cut)
            continue;
         $pdo->beginTransaction();
         $s = $pdo->prepare('SELECT * FROM reservations WHERE id=? FOR UPDATE');
         $s->execute([$x['id']]);
         $r = $s->fetch();
         $s = $pdo->prepare('SELECT COUNT(*) c FROM payments WHERE reservation_id=? AND status IN("pending","verified")');
         $s->execute([$x['id']]);
         if ($r && $r['status'] == 'pending' && !$s->fetch()['c'])
            cancelRes($pdo, $r, 'expired', true);
         $pdo->commit();
      }
      $last = date('H') >= 12 ? date('Y-m-d') : date('Y-m-d', strtotime('-1 day'));
      $s = $pdo->prepare('SELECT id FROM reservations WHERE status="approved" AND checkout<=?');
      $s->execute([$last]);
      foreach ($s->fetchAll() as $x)
         completeStay($pdo, $x['id']);
   } catch (Throwable $e) {
      if ($pdo->inTransaction())
         $pdo->rollBack();
      error_log('Bella Vista sweep: ' . $e->getMessage());
   }
}
sweep($pdo);
