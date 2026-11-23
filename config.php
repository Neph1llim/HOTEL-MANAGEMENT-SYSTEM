<?php
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=bellavista;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
if (!$pdo->query('SELECT COUNT(*) c FROM admins')->fetch()['c'])
   $pdo->prepare('INSERT INTO admins(username,pass) VALUES("admin",?)')->execute([password_hash('admin123', PASSWORD_DEFAULT)]);
// [label, kind, percent, weight out of 10000] - same order as the wheel in assets/app.js
const PRIZES = [['20% discount voucher', 'pct', 20, 5000], ['50% discount voucher', 'pct', 50, 3000], ['75% discount voucher + meal stubs', 'pct', 75, 1250], ['Free Standard Room, 2 nights', 'free_standard', 0, 500], ['Free Family Room, 2 nights', 'free_family', 0, 240], ['VIP status (5% off every booking)', 'vip', 0, 10]];
const SPIN_COST = 100;
function j($d, $c = 200)
{
   http_response_code($c);
   header('Content-Type: application/json');
   echo json_encode($d);
   exit;
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
   if (!$r || !$a || !$b) return ['error' => 'Invalid dates', 'fatal' => 1];
   $n = (int)$a->diff($b)->format('%r%a');
   if ($a < date_create('today')) return ['error' => 'Check-in cannot be in the past', 'fatal' => 1];
   if ($n < 1) return ['error' => 'Check-out must be at least 1 night after check-in (check-in 2:00 PM, check-out 12:00 NN)', 'fatal' => 1];
   if ($pax < 1 || $pax > $r['max_cap']) return ['error' => "Maximum capacity is {$r['max_cap']} guests"];
   $x = $r['type'] == 'standard' ? ($pax > 2 ? 1000 : 0) : ($r['type'] == 'hall' ? max(0, $pax - 30) * 500 : 0);
   $extra = $x * $n;
   $sub = $r['rate'] * $n;
   $d = 0;
   if ($vid) {
      $v = $pdo->prepare('SELECT * FROM vouchers WHERE id=? AND guest_id=? AND status="unused"');
      $v->execute([$vid, $gid]);
      $v = $v->fetch();
      if (!$v) return ['error' => 'Voucher not valid'];
      if ($v['kind'] == 'pct') $d = $sub * $v['pct'] / 100;
      elseif ($v['kind'] == 'free_' . $r['type']) $d = $r['rate'] * min($n, 2);
      else return ['error' => 'Voucher does not apply to this room'];
   } elseif ($gid) {
      $s = $pdo->prepare('SELECT vip FROM guests WHERE id=?');
      $s->execute([$gid]);
      if ($s->fetch()['vip']) $d = $sub * .05;
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
   if (!in_array($r['status'], ['pending', 'approved']) || !$in || $in < $today) return ['ok' => false, 'refundable' => false];
   return ['ok' => true, 'refundable' => (int)$today->diff($in)->format('%r%a') >= 3];
}
