<?php
require 'config.php';
if(me()) go('home.php');
$err=[]; $v=['name'=>'','email'=>'','phone'=>''];
if($_SERVER['REQUEST_METHOD']==='POST'){
  check_csrf();
  $v=['name'=>trim($_POST['name']??''),'email'=>strtolower(trim($_POST['email']??'')),'phone'=>preg_replace('/[\s-]/','',$_POST['phone']??'')];
  $pw=$_POST['password']??'';
  if(strlen($v['name'])<2) $err[]='Enter your full name.';
  if(!filter_var($v['email'],FILTER_VALIDATE_EMAIL)) $err[]='Enter a valid email address.';
  if(!preg_match('/^(09\d{9}|\+639\d{9})$/',$v['phone'])) $err[]='Enter a Philippine mobile number, like 09171234567.';
  if(strlen($pw)<8) $err[]='Password must be at least 8 characters.';
  if($pw!==($_POST['confirm']??'')) $err[]='Passwords do not match.';
  if(!$err && one("SELECT id FROM users WHERE email=?",[$v['email']])) $err[]='That email is already registered.';
  if(!$err){ q("INSERT INTO users(name,email,phone,password) VALUES(?,?,?,?)",[$v['name'],$v['email'],$v['phone'],password_hash($pw,PASSWORD_DEFAULT)]); go('index.php?registered=1'); }
}
top('Register',['bare'=>true,'body'=>'auth']); ?>
<main class="auth-card">
  <?=img_slot('logo.png','Logo','logo')?>
  <h1>Create your account</h1>
  <?php foreach($err as $m){ ?><p class="alert" role="alert"><?=e($m)?></p><?php } ?>
  <form method="post"><?=csrf_field()?>
    <label>Full name<input name="name" value="<?=e($v['name'])?>" required></label>
    <label>Email address<input type="email" name="email" value="<?=e($v['email'])?>" required></label>
    <label>Mobile number<input name="phone" inputmode="tel" value="<?=e($v['phone'])?>" required></label>
    <label>Password<input type="password" name="password" minlength="8" required></label>
    <label>Confirm password<input type="password" name="confirm" minlength="8" required></label>
    <button class="btn solid">Create account</button>
  </form>
  <p class="alt">Already have an account? <a href="index.php">Log in</a></p>
</main>
<?php bottom(['bare'=>true]);
