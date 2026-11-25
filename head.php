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
    <style>
        /* notification bell (kept here so style.css stays untouched) */
        .bell { position: relative; display: inline-flex; align-items: center; justify-content: center; vertical-align: middle; margin-left: .4rem; padding: .3rem .55rem; line-height: 1; color: var(--tx); text-decoration: none; border: 1px solid var(--ln); border-radius: 8px }
        .bell[hidden], .bell .bn[hidden] { display: none }
        .bell:hover, .bell:focus-visible { color: #D4AF37; border-color: #D4AF37 }
        .bell svg { display: block; width: 20px; height: 20px }
        .bell .bn { position: absolute; top: -8px; right: -8px; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 99px; background: #D4AF37; color: #0A2214; font: 700 .7rem/18px var(--bd, system-ui, sans-serif); text-align: center }
    </style>
</head>

<body<?= isset($bodyClass) ? ' class="' . $bodyClass . '"' : '' ?>>
    <header class="top"><a class="brand" href="index.php"><img src="assets/img/logo.png" alt="" width="52" height="46"><span>Bella Vista<small>Hotel &amp; Events Place</small></span></a>
        <nav><a href="index.php" data-t>Home</a><a href="index.php#about-us" data-t>About us</a><a href="policies.php" data-t>Policies</a><a href="rooms.php" data-t>Rooms</a><a href="reserve.php"
                data-t>Book now</a><a href="rewards.php" data-t>Rewards</a></nav>
        <div class="tools"><button id="lang" aria-label="Language">TL</button><button id="theme"
                aria-label="Theme">&#9790;</button><?php if (!defined('ADMIN')):
                    /* unread count for the bell (the script below keeps it fresh and shows the bell once a guest is logged in) */
                    $bn = 0;
                    $bin = false;
                    if (isset($pdo) && !empty($_SESSION['gid'])) {
                        try {
                            $bq = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE guest_id=? AND is_read=0');
                            $bq->execute([$_SESSION['gid']]);
                            $bn = (int) $bq->fetchColumn();
                            $bin = true;
                        } catch (Throwable $bx) {
                        }
                    } ?><a id="bell" class="bell" href="notifications.php" aria-label="Notifications"<?= $bin ? '' : ' hidden' ?>><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9a6 6 0 0 1 12 0c0 6 2.5 7.5 2.5 7.5h-17S6 15 6 9z"/><path d="M10 20a2 2 0 0 0 4 0"/></svg><span id="bellN" class="bn"<?= $bn ? '' : ' hidden' ?>><?= $bn > 99 ? '99+' : $bn ?></span></a><?php echo '<button id="menu" aria-label="Menu">&#9776;</button>';
                endif; ?>
        </div>
    </header>
    <?php if (!defined('ADMIN')): ?>
        <script>
            (() => {
                const b = document.getElementById('bell'), n = document.getElementById('bellN'); if (!b) return;
                const set = c => { c = +c || 0; n.textContent = c > 99 ? '99+' : c; n.hidden = !c; b.setAttribute('aria-label', c ? 'Notifications, ' + c + ' unread' : 'Notifications'); const d = document.getElementById('nbadge'); if (d && !c) d.hidden = true };
                window.bvBell = set;
                const poll = () => fetch('api.php', { method: 'POST', body: new URLSearchParams({ a: 'notes_count' }) }).then(r => r.json()).then(j => { if (j.error) { b.hidden = true; return } b.hidden = false; set(j.unread) }).catch(() => { });
                poll(); setInterval(poll, 60000); document.addEventListener('visibilitychange', () => { if (!document.hidden) poll() });
            })();
        </script>
    <?php endif; ?>
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