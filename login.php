<?php include 'head.php'; ?>
<section class="wrap narrow">
    <h1 data-t>Welcome to Bella Vista</h1>
    <p id="lerr" class="err"></p>
    <form id="lf" class="form">
        <h2 style="grid-column:1/-1" data-t>Log in</h2><label><span data-t>Email</span><input type="email" name="email" required></label><label><span data-t>Password</span><input type="password" name="pass" required></label><button class="btn" data-t>Log in</button>
    </form>
    <form id="rg" class="form">
        <h2 style="grid-column:1/-1" data-t>Create an account</h2><label><span data-t>Full name</span><input name="name" required></label><label><span data-t>Email</span><input type="email" name="email" required></label><label><span data-t>Mobile number</span><input name="phone" required></label><label><span data-t>Password</span><input type="password" name="pass" minlength="6" required></label><button class="btn" data-t>Create an account</button>
    </form>
</section>
<?php include 'foot.php'; ?>