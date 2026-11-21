<?php require 'config.php'; $u=require_login(); $err=[];
$rooms=all("SELECT * FROM rooms ORDER BY id");
$vouchers=all("SELECT * FROM vouchers WHERE user_id=? AND status='unused' AND kind<>'meal' ORDER BY id DESC",[$u['id']]);
$pre=['room'=>(int)($_GET['room']??0),'type'=>$_GET['type']??'','voucher'=>(int)($_GET['voucher']??0)];
foreach($vouchers as $v) if($v['id']==$pre['voucher']){ if($v['kind']==='free_standard')$pre['type']='standard'; if($v['kind']==='free_family')$pre['type']='family'; }
foreach($rooms as $r) if($r['id']==$pre['room']) $pre['type']=$r['type'];
if(!in_array($pre['type'],['standard','family','event'])) $pre['type']='standard';

if($_SERVER['REQUEST_METHOD']==='POST'){
  check_csrf(); $pre['type']=$_POST['type']??'standard'; $pre['room']=(int)($_POST['room']??0); $pre['voucher']=(int)($_POST['voucher']??0);
  $in=$_POST['checkin']??''; $out=$_POST['checkout']??''; $g=(int)($_POST['guests']??0); $ref=trim($_POST['payment_ref']??'');
  if(empty($_POST['agree'])) $err[]='Please agree to the Terms & Conditions before checking out.';
  if(!preg_match('/^[A-Za-z0-9 \-]{6,40}$/',$ref)) $err[]='Enter the GCash reference number from your payment (6 to 40 letters or numbers).';
  $proof=save_image($_FILES['proof']??null,'proofs'); if($proof===false) $err[]='Payment screenshot must be a JPG, PNG or WEBP under 3 MB.';
  if(!$err){
    $pdo->beginTransaction();
    q("SELECT id FROM rooms WHERE id=? FOR UPDATE",[$pre['room']]);       // lock the room so two guests cannot take the same dates
    $qt=compute_quote($pre['room'],$in,$out,$g,$pre['voucher'],$u);
    if(!$qt['ok']) $err[]=$qt['error']; elseif(!$qt['available']) $err[]='Sorry, that room was just booked for these dates. Pick another room or date.';
    if(!$err){
      q("INSERT INTO reservations(user_id,room_id,checkin,checkout,nights,guests,base_amount,extra_fee,voucher_id,voucher_discount,vip_discount,total,downpayment,payment_ref,proof_path)
         VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",[$u['id'],$pre['room'],$in,$out,$qt['nights'],$g,$qt['base'],$qt['extra'],$pre['voucher']?:null,$qt['voucher_discount'],$qt['vip_discount'],$qt['total'],$qt['down'],$ref,$proof]);
      if($pre['voucher']) q("UPDATE vouchers SET status='used' WHERE id=?",[$pre['voucher']]);
      $pdo->commit(); go('reservations.php?ok=1');
    }
    $pdo->rollBack();
  }
}
$qr=current_qr(); $min=earliest_checkin()->format('Y-m-d');
$names=['standard'=>['Standard Room','Room A, B or C. 2 to 3 guests. ₱3,500 per night for 2 guests, +₱1,000 for a 3rd.'],'family'=>['Family Room','FR1, FR2 or FR3. Up to 6 guests. Fixed ₱6,000 per night.'],'event'=>['Event Hall','30 to 40 guests. ₱15,000 per night, +₱500 per guest over 30.']];
top('Reserve a room'); ?>
<main class="page">
  <h1 class="mono">Reserve a room</h1>
  <p>Check-in starts at 2:00 PM. Check-out is 12:00 NN. Earliest check-in: <?=date('F j, Y',strtotime($min))?>.</p>
  <?php foreach($err as $m){ ?><p class="alert" role="alert"><?=e($m)?></p><?php } ?>
  <form id="book" method="post" enctype="multipart/form-data" data-min="<?=$min?>" data-voucher="<?=$pre['voucher']?>" data-room="<?=$pre['room']?>"><?=csrf_field()?>
    <fieldset class="types"><legend>1. Choose what to book</legend>
      <?php foreach($names as $k=>$n){ ?><label class="type-card"><input type="radio" name="type" value="<?=$k?>" <?=$pre['type']===$k?'checked':''?>><span><strong><?=$n[0]?></strong><small><?=$n[1]?></small></span></label><?php } ?>
    </fieldset>
    <fieldset><legend>2. Pick your dates</legend>
      <div class="row"><label>Check-in (2:00 PM)<input type="date" name="checkin" min="<?=$min?>" value="<?=e($_POST['checkin']??'')?>" required></label>
      <label>Check-out (12:00 NN)<input type="date" name="checkout" min="<?=$min?>" value="<?=e($_POST['checkout']??'')?>" required></label>
      <label>Guests<input type="number" name="guests" min="1" value="<?=e($_POST['guests']??2)?>" required></label></div>
    </fieldset>
    <fieldset class="rooms-pick"><legend>3. Choose a room (availability updates live)</legend>
      <?php foreach($rooms as $r){ ?><label class="room-opt" data-type="<?=$r['type']?>" data-max="<?=$r['max_cap']?>">
        <input type="radio" name="room" value="<?=$r['id']?>" <?=$pre['room']==$r['id']?'checked':''?> required><span><strong><?=e($r['name'])?></strong><small class="avail">Select dates to check availability</small></span></label><?php } ?>
    </fieldset>
    <fieldset><legend>4. Voucher (optional, one per booking)</legend>
      <button type="button" class="btn outline" id="voucherBtn">Use a voucher</button>
      <input type="hidden" name="voucher" value="<?=$pre['voucher']?>">
      <div id="voucherList" hidden>
        <label class="v-opt"><input type="radio" name="vpick" value="0" <?=!$pre['voucher']?'checked':''?>> No voucher</label>
        <?php foreach($vouchers as $v){ ?><label class="v-opt" data-applies="<?=$v['kind']==='free_standard'?'standard':($v['kind']==='free_family'?'family':'any')?>"><input type="radio" name="vpick" value="<?=$v['id']?>" <?=$pre['voucher']==$v['id']?'checked':''?>> <?=e($v['label'])?></label><?php } ?>
        <?php if(!$vouchers) echo '<p class="tiny">You have no usable vouchers. Spin the wheel in My Points &amp; Rewards.</p>'; ?>
      </div>
    </fieldset>
    <section class="summary" id="summary" aria-live="polite"><h2>Price summary</h2><p id="sumBody">Choose a room and dates to see your total.</p></section>
    <fieldset><legend>5. Pay the downpayment via GCash</legend>
      <div class="pay">
        <div class="qr-box"><?php if($qr){ ?><img id="qrImg" src="<?=base().e($qr['path'])?>" alt="GCash QR code"><?php } else { ?><p id="qrNone">No GCash QR is set yet. Please contact the hotel.</p><img id="qrImg" alt="GCash QR code" hidden><?php } ?>
          <small id="qrDate"><?=$qr?'QR for '.date('F j, Y',strtotime($qr['qr_date'])):''?></small></div>
        <div><p>Scan the QR, send the downpayment shown above, then enter the reference number.</p>
          <label>GCash reference number<input name="payment_ref" value="<?=e($_POST['payment_ref']??'')?>" required></label>
          <label>Payment screenshot (optional)<input type="file" name="proof" accept="image/jpeg,image/png,image/webp"></label></div>
      </div>
    </fieldset>
    <label class="agree"><input type="checkbox" name="agree" value="1" required> I have read and agree to the <button type="button" class="linkbtn" data-modal="termsModal">Terms &amp; Conditions</button></label>
    <button class="btn solid" id="submitBtn">Check out and reserve</button>
  </form>
</main>
<dialog id="termsModal" class="modal"><h2>Terms &amp; Conditions</h2>
  <ol class="terms"><?php foreach(TERMS as $t){ ?><li><strong><?=e($t[0])?>:</strong> <?=e($t[1])?></li><?php } ?></ol>
  <button class="btn solid" data-close>Close</button></dialog>
<?php bottom();
