<?php
session_start();
date_default_timezone_set('Asia/Manila');
$pdo = new PDO('mysql:host=localhost;dbname=bellavista;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone='+08:00'"]);
/* rooms can be deleted from Admin > Rooms & Rates; a room with booking history is hidden (deleted=1) instead of erased. Adds the column on databases made before this feature. */
if (!$pdo->query("SHOW COLUMNS FROM rooms LIKE 'deleted'")->fetch())
   $pdo->exec('ALTER TABLE rooms ADD deleted TINYINT DEFAULT 0');
if (!$pdo->query('SELECT COUNT(*) c FROM admins')->fetch()['c'])
   $pdo->prepare('INSERT INTO admins(username,pass) VALUES("admin",?)')->execute([password_hash('admin123', PASSWORD_DEFAULT)]);
/* ---------- rewards: settings + wheel prizes (edited in Admin > Rewards) ----------
   settings      : key/value pairs. points_percent = % of a completed stay's total that is awarded as points.
   wheel_prizes  : one row per wheel reward. chance = probability in percent (enabled rewards add up to 100).
                   kind: the code of a reward type (table reward_types, managed in Admin > Rewards). Every type does one of:
                   pct (discount voucher, uses pct) | free_room (free nights in one room or room type, uses room_id/room_type + nights) | vip (VIP status, pct = its discount on every booking) | free_food (a food voucher).
   Both tables are created (and filled with the original defaults) the first time any page is opened on an older database. */
if (!$pdo->query("SHOW TABLES LIKE 'settings'")->fetch()) {
   $pdo->exec('CREATE TABLE settings (k VARCHAR(40) PRIMARY KEY, v VARCHAR(255))');
   $pdo->exec("INSERT INTO settings(k,v) VALUES('points_percent','1')");
}
if (!$pdo->query("SHOW TABLES LIKE 'wheel_prizes'")->fetch()) {
   $pdo->exec("CREATE TABLE wheel_prizes (id INT AUTO_INCREMENT PRIMARY KEY, label VARCHAR(60), kind VARCHAR(20), pct INT DEFAULT 0, room_id INT NULL, room_type VARCHAR(10) NULL, nights TINYINT DEFAULT 2, chance DECIMAL(5,2) DEFAULT 0, active TINYINT DEFAULT 1)");
   $pdo->exec("INSERT INTO wheel_prizes(label,kind,pct,room_type,nights,chance,active) VALUES
      ('20% discount voucher','pct',20,NULL,2,50,1),('50% discount voucher','pct',50,NULL,2,30,1),('75% discount voucher + meal stubs','pct',75,NULL,2,12.5,1),
      ('Free Standard Room, 2 nights','free_room',0,'standard',2,5,1),('Free Family Room, 2 nights','free_room',0,'family',2,2.4,1),('VIP status (5% off every booking)','vip',5,NULL,2,0.1,1)");
}
/* reward types (Admin > Rewards > Reward types). A type is a named kind of reward; what it DOES is its base:
   pct = discount voucher | free_room = free nights in a room | vip = VIP status | free_food = free food voucher. The built-in types cannot be deleted.
   The old separate "Free Standard Room" and "Free Family Room" kinds are one "Free room stay" type now: the room (a specific room,
   any Standard room, any Family room, or any room) and the number of nights are set on each reward and copied onto the voucher a guest wins. */
if (!$pdo->query("SHOW TABLES LIKE 'reward_types'")->fetch()) {
   $pdo->exec('CREATE TABLE reward_types (code VARCHAR(20) PRIMARY KEY, label VARCHAR(60), base VARCHAR(10), builtin TINYINT DEFAULT 0)');
   $pdo->exec("INSERT INTO reward_types(code,label,base,builtin) VALUES ('pct','Discount voucher (% off)','pct',1),('free_room','Free room stay','free_room',1),('vip','VIP status','vip',1),('free_food','Free food','free_food',1)");
}
/* "Free food" is a built-in reward type too (a meal or food perk the guest claims at the front desk). Added to databases made before it existed. */
if (!$pdo->query("SELECT 1 FROM reward_types WHERE code='free_food'")->fetch())
   $pdo->exec("INSERT INTO reward_types(code,label,base,builtin) VALUES ('free_food','Free food','free_food',1)");
/* the VIP discount is no longer fixed at 5%: it is the discount number on the VIP status reward in Admin > Rewards (kept in settings.vip_percent so VIP guests keep it
   even if that reward is switched off or removed from the wheel). First run on an older database: keep 5% and give the VIP reward that number. */
if (!$pdo->query("SELECT 1 FROM settings WHERE k='vip_percent'")->fetch()) {
   $pdo->exec("INSERT INTO settings(k,v) VALUES('vip_percent','5')");
   $pdo->exec("UPDATE wheel_prizes SET pct=5 WHERE pct=0 AND kind IN (SELECT code FROM reward_types WHERE base='vip')");
   $pdo->exec("UPDATE reward_types SET label='VIP status' WHERE code='vip' AND label='VIP status (5% off every booking)'");
}
foreach (['wheel_prizes', 'vouchers'] as $tb)
   if (!$pdo->query("SHOW COLUMNS FROM $tb LIKE 'nights'")->fetch())
      $pdo->exec("ALTER TABLE $tb ADD room_id INT NULL, ADD room_type VARCHAR(10) NULL, ADD nights TINYINT DEFAULT 2");
if ($pdo->query("SELECT 1 FROM wheel_prizes WHERE kind IN ('free_standard','free_family') UNION ALL SELECT 1 FROM vouchers WHERE kind IN ('free_standard','free_family') LIMIT 1")->fetch())
   foreach (['standard', 'family'] as $ty)
      foreach (['wheel_prizes', 'vouchers'] as $tb)
         $pdo->exec("UPDATE $tb SET kind='free_room', room_type='$ty', nights=2 WHERE kind='free_$ty'");
const PRIZE_BASES = ['pct', 'free_room', 'vip', 'free_food'];   // what a reward type can do
const MAX_FREE_NIGHTS = 30;                         // most free nights one free-room reward can give
const MAX_WHEEL = 8;   // most rewards that can be enabled on the wheel at once, so the labels stay readable
/* percent of a completed stay's total that is awarded as points (Admin > Rewards) */
function pointsPercent($pdo)
{
   $v = $pdo->query("SELECT v FROM settings WHERE k='points_percent'")->fetch();
   return $v ? max(0, min(100, (float) $v['v'])) : 1.0;
}
/* % off every booking for VIP guests (Admin > Rewards > the VIP status reward) */
function vipPercent($pdo)
{
   $v = $pdo->query("SELECT v FROM settings WHERE k='vip_percent'")->fetch();
   return $v ? max(0, min(100, (float) $v['v'])) : 5.0;
}
/* 12.5 -> "12.5", 50.00 -> "50" */
function pctText($n)
{
   return rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
}
/* the rewards currently on the wheel, in wheel order. The wheel in assets/app.js draws them in this same order. */
function wheelPrizes($pdo)
{
   /* base = what the reward does (from its type); the wheel and the voucher use the base, so they never depend on a type that is renamed or deleted later */
   return $pdo->query('SELECT p.id,p.label,p.kind,COALESCE(t.base,p.kind) base,p.pct,p.room_id,p.room_type,p.nights,p.chance FROM wheel_prizes p LEFT JOIN reward_types t ON t.code=p.kind WHERE p.active=1 ORDER BY p.id')->fetchAll();
}
function rewardTypes($pdo)
{
   return $pdo->query('SELECT code,label,base,builtin FROM reward_types ORDER BY builtin DESC, label')->fetchAll();
}
/* what a free-room voucher covers, in words: "Room A", "any Family room", "any Standard or Family room" */
function voucherScope($pdo, $v)
{
   if (!empty($v['room_id'])) {
      $s = $pdo->prepare('SELECT name FROM rooms WHERE id=? AND deleted=0');
      $s->execute([$v['room_id']]);
      $n = $s->fetchColumn();
      return $n !== false ? $n : 'a room that is no longer available';
   }
   return !empty($v['room_type']) ? 'any ' . ucfirst($v['room_type']) . ' room' : 'any Standard or Family room';
}
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
      elseif ($v['kind'] == 'free_room') {
         /* a free-room voucher is for ONE specific room, or any room of one type, or any Standard/Family room; and for a set number of nights */
         $ok = !empty($v['room_id']) ? (int) $v['room_id'] === (int) $r['id']
            : (!empty($v['room_type']) ? $v['room_type'] === $r['type'] : in_array($r['type'], ['standard', 'family'], true));
         if (!$ok)
            return ['error' => 'This voucher only applies to ' . voucherScope($pdo, $v)];
         $d = $r['rate'] * min($n, max(1, (int) $v['nights']));
      } elseif ($v['kind'] == 'free_' . $r['type'])   /* vouchers won before the two free-room kinds were merged */
         $d = $r['rate'] * min($n, 2);
      else
         return ['error' => 'Voucher does not apply to this room'];
   } elseif ($gid) {
      $s = $pdo->prepare('SELECT vip FROM guests WHERE id=?');
      $s->execute([$gid]);
      if ($s->fetch()['vip'])
         $d = $sub * vipPercent($pdo) / 100;
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
/* Mark an approved reservation completed and award points (the % set in Admin > Rewards, 1% by default) of the total. Returns the points, or false if it was not approved. */
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
   $pts = (int) floor(round($r['total'] * pointsPercent($pdo) / 100, 6));
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
