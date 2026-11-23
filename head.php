<!doctype html>
<html lang="en" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Bella Vista Hotel &amp; Events Place</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@500;600;700&text=0123456789&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <header class="top"><a class="brand" href="index.php"><svg viewBox="0 0 48 48" width="38" height="38" aria-hidden="true">
                <circle cx="24" cy="24" r="23" fill="#0B5D4B" />
                <path d="M7 35 20 14l7 12 4-6 10 15z" fill="#D9BC7E" />
            </svg><span>Bella Vista</span></a>
        <nav><a href="index.php#about" data-t>About us</a><a href="rooms.php" data-t>Rooms</a><a href="reserve.php" data-t>Book now</a><a href="rewards.php" data-t>Rewards</a></nav>
        <div class="tools"><button id="lang" aria-label="Language">TL</button><button id="theme" aria-label="Theme">&#9790;</button><?php if (!defined('ADMIN')) echo '<button id="menu" aria-label="Menu">&#9776;</button>'; ?></div>
    </header>
    <?php if (!defined('ADMIN')): ?><aside id="drawer">
            <div id="dinfo" class="dinfo"></div><a href="reserve.php" data-t>Reserve a Room</a><a class="auth" hidden href="reservations.php" data-t>My Reservations</a><a class="auth" hidden href="rewards.php" data-t>My Points & Rewards</a><a class="auth" hidden href="rewards.php#vouchers" data-t>My Vouchers</a><a class="auth" hidden href="account.php" data-t>Account Profile</a><a href="index.php#terms" data-t>Terms & Conditions</a><button id="dlogout" hidden data-t>Log out</button>
        </aside>
        <div id="dov"></div><?php endif; ?>