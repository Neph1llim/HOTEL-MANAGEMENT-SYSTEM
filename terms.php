<?php require 'config.php'; $u=require_login(); top('Terms & Conditions'); ?>
<main class="page narrow"><h1 class="mono">Terms &amp; Conditions</h1>
  <ol class="terms"><?php foreach(TERMS as $t){ ?><li><strong><?=e($t[0])?>:</strong> <?=e($t[1])?></li><?php } ?></ol>
  <a class="btn outline" href="reserve.php">RESERVE A ROOM</a></main>
<?php bottom();
