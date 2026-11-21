<?php
require 'config.php';
$u=me(); if($u) go($u['role']==='admin' ? 'admin/index.php' : 'home.php');
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  check_csrf();
  $x=one("SELECT * FROM users WHERE email=?",[strtolower(trim($_POST['email']??''))]);
  if($x && password_verify($_POST['password']??'',$x['password'])){
    session_regenerate_id(true); $_SESSION['uid']=$x['id'];
    go($x['role']==='admin' ? 'admin/index.php' : 'home.php');   // same form, different destination
  }
  $err='Email or password is incorrect.';
}
top('Log in',['bare'=>true,'body'=>'auth']); ?>
<main class="auth-card">
  <?=img_slot('logo.png','Logo','logo')?>
  <h1>Welcome back</h1>
  <p>Log in to reserve rooms, view your rewards and manage your stays.</p>
  <?php if($err){ ?><p class="alert" role="alert"><?=e($err)?></p><?php } ?>
  <?php if(isset($_GET['registered'])){ ?><p class="notice">Account created. You can log in now.</p><?php } ?>
  <form method="post"><?=csrf_field()?>
    <label>Email address<input type="email" name="email" required autofocus></label>
    <label>Password<input type="password" name="password" required></label>
    <button class="btn solid">Log in</button>
  </form>
  <p class="alt">New to Bella Vista? <a href="register.php">Create an account</a></p>
</main>
<?php bottom(['bare'=>true]);
