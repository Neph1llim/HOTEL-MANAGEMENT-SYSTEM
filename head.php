<!doctype html>
<html lang="en" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#0A2214">
    <script>
        document.documentElement.classList.add('js');
        try { document.documentElement.dataset.theme = localStorage.theme || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'); } catch (e) { }
    </script>
    <title>Bella Vista Hotel &amp; Events Place</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&family=Manrope:wght@500;600;700&family=Cinzel:wght@500;600&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@500;600;700&text=0123456789&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="apple-touch-icon" href="assets/img/apple-touch-icon.png">
    <link rel="stylesheet" href="assets/style.css">
</head>

<body<?= isset($bodyClass) ? ' class="' . $bodyClass . '"' : '' ?>>
    <header class="top"><a class="brand" href="index.php"><img src="assets/img/logo.png" alt="" width="52" height="46"><span>Bella Vista<small>Hotel &amp; Events Place</small></span></a>
        <nav><a href="index.php" data-t>Home</a><a href="gallery.php" data-t>Gallery</a><a href="policies.php" data-t>Policies</a><a href="rooms.php" data-t>Rooms</a><a href="reserve.php"
                data-t>Book now</a><a href="rewards.php" data-t>Rewards</a></nav>
        <div class="tools"><button id="lang" aria-label="Language">TL</button><button id="theme"
                aria-label="Theme">&#9790;</button><?php if (!defined('ADMIN'))
                    echo '<button id="menu" aria-label="Menu">&#9776;</button>'; ?>
        </div>
    </header>
    <?php if (!defined('ADMIN')): ?>
        <aside id="drawer">
            <div id="dinfo" class="dinfo"></div><a href="reserve.php" data-t>Reserve a Room</a><a class="auth" hidden
                href="reservations.php"><span data-t>My Reservations</span><i id="nbadge" class="badge" hidden></i></a><a
                class="auth" hidden href="rewards.php" data-t>My Points & Rewards</a><a class="auth" hidden
                href="rewards.php#vouchers" data-t>My Vouchers</a><a class="auth" hidden href="account.php" data-t>Account
                Profile</a><a href="policies.php" data-t>Terms & Conditions</a><button id="dlogout" hidden data-t>Log
                out</button>
        </aside>
        <div id="dov"></div><?php endif; ?>