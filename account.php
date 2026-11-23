<?php include 'head.php'; ?>
<section class="wrap narrow">
    <h1 data-t>Account profile</h1>
    <p id="amsg" class="err"></p>
    <form id="acf" class="form"><label><span data-t>Full name</span><input name="name" required></label><label><span data-t>Mobile number</span><input name="phone" required></label><label><span data-t>Current password</span><input type="password" name="cur_pass"></label><label><span data-t>New password</span><input type="password" name="new_pass" minlength="6"></label><button class="btn" data-t>Save changes</button></form>
</section>
<?php include 'foot.php'; ?>