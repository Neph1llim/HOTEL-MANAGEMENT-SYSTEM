<?php
/* Shared admin layout: login gate, sidebar and page header.
   Each admin page sets $page (nav key), $title and optionally $sub, then requires this file
   and finishes with admin-bottom.php. */
define('ADMIN', 1);
$page  = $page  ?? 'overview';
$title = $title ?? 'Dashboard';
$sub   = $sub   ?? '';

function adm_icon($paths)
{
    return '<svg viewBox="0 0 24 24" aria-hidden="true">' . $paths . '</svg>';
}

$nav = [
    'overview'     => ['admin.php', 'Overview', '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>'],
    'payments'     => ['admin-payments.php', 'Payments', '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>'],
    'reservations' => ['admin-reservations.php', 'Reservations', '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>'],
    'walkin'       => ['admin-walkin.php', 'Walk-in booking', '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/>'],
    'rooms'        => ['admin-rooms.php', 'Rooms &amp; Rates', '<path d="M2 20v-8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v8M2 16h20M6 10V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v4"/>'],
    'reports'      => ['admin-reports.php', 'Reports', '<path d="M3 3v18h18"/><path d="M7 15v-4M12 15V7M17 15v-6"/>'],
];
?><!doctype html>
<html lang="en" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Bella Vista | Admin - <?= htmlspecialchars($title) ?></title>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@500;600;700&text=0123456789&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="admbody">
    <div id="af-wrap" class="login" hidden>
        <form id="af" class="logcard">
            <div class="brand"><svg viewBox="0 0 48 48" width="38" height="38">
                    <circle cx="24" cy="24" r="23" fill="#0B5D4B" />
                    <path d="M7 35 20 14l7 12 4-6 10 15z" fill="#D9BC7E" />
                </svg><span>Bella Vista</span></div>
            <h2 style="margin:0">Staff sign in</h2><label>Username<input name="user" required
                    autofocus></label><label>Password<input type="password" name="pass" required></label><button
                class="btn">Log in</button>
            <p id="aerr" class="err"></p><a href="index.php" class="mu">Back to website</a>
        </form>
    </div>
    <div id="dash" class="adm" hidden>
        <aside class="side">
            <div class="sidehead">
                <a class="brand" href="admin.php"><svg viewBox="0 0 48 48" width="32" height="32">
                        <circle cx="24" cy="24" r="23" fill="#0B5D4B" />
                        <path d="M7 35 20 14l7 12 4-6 10 15z" fill="#D9BC7E" />
                    </svg><span>Bella Vista<small>Admin</small></span></a>
                <button id="theme" class="thbtn" type="button" aria-label="Theme" title="Dark / Light mode">&#9790;</button>
            </div>
            <nav aria-label="Admin sections">
                <?php foreach ($nav as $key => $n): ?>
                    <a href="<?= $n[0] ?>" <?= $key === $page ? 'class="on" aria-current="page"' : '' ?>><?= adm_icon($n[2]) ?><span><?= $n[1] ?></span></a>
                <?php endforeach; ?>
            </nav>
            <div class="sidefoot">
                <a href="index.php"><?= adm_icon('<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/>') ?><span>View website</span></a>
                <button id="logout" type="button"><?= adm_icon('<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>') ?><span>Log out</span></button>
            </div>
        </aside>
        <main class="mainadm">
            <header class="tb">
                <div>
                    <h1><?= htmlspecialchars($title) ?></h1>
                    <?php if ($sub !== ''): ?><p class="mu tbsub"><?= $sub ?></p><?php endif; ?>
                </div>
                <?php if ($page === 'overview'): ?>
                    <div id="mnav" class="mnav" role="group" aria-label="Month">
                        <button id="mprev" class="mb" type="button" aria-label="Previous month" title="Previous month"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg></button>
                        <b id="mlabel" aria-live="polite"></b>
                        <button id="mnext" class="mb" type="button" aria-label="Next month" title="Next month"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg></button>
                        <button id="mnow" class="btn sm ghost" type="button" hidden>This month</button>
                    </div>
                <?php else: ?>
                    <span id="today" class="mu"></span>
                <?php endif; ?>
            </header>
