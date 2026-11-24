<?php require 'config.php';
$bodyClass = 'flow';
include 'head.php'; ?>
<section class="wrap profilewrap">
    <h1 class="bigtitle" data-t>My profile.</h1>
    <form id="acf" class="card profilecard" novalidate>
        <h2 data-t>Customer profile</h2>
        <label class="full"><span data-t>Full name</span><input name="name" autocomplete="name" required></label>
        <label><span data-t>Email address</span><input type="email" name="email" autocomplete="email" readonly aria-describedby="emailnote"></label>
        <label><span data-t>Phone number</span><input type="tel" name="phone" autocomplete="tel" required></label>
        <p id="emailnote" class="mu full"><small data-t>Your email is your login, so it can’t be changed here.</small></p>
        <div class="full pwsec">
            <h3 data-t>Change password (optional)</h3>
            <div class="pwgrid">
                <label><span data-t>Current password</span><input type="password" name="cur_pass" autocomplete="current-password"></label>
                <label><span data-t>New password</span><input type="password" name="new_pass" minlength="6" autocomplete="new-password"></label>
            </div>
        </div>
        <p id="amsg" class="full err" aria-live="polite"></p>
        <button class="btn full" data-t>Save profile changes</button>
    </form>
</section>
<?php include 'foot.php'; ?>
