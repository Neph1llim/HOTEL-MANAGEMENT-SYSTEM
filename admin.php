<?php define('ADMIN',1); ?><!doctype html><html lang="en" data-theme="light"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Bella Vista | Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Lora:wght@500;600;700&text=0123456789&display=swap" rel="stylesheet"><link rel="stylesheet" href="assets/style.css"></head>
<body class="admbody">
<div id="af-wrap" class="login" hidden><form id="af" class="logcard"><div class="brand"><svg viewBox="0 0 48 48" width="38" height="38"><circle cx="24" cy="24" r="23" fill="#0B5D4B"/><path d="M7 35 20 14l7 12 4-6 10 15z" fill="#D9BC7E"/></svg><span>Bella Vista</span></div>
<h2 style="margin:0">Staff sign in</h2><label>Username<input name="user" required autofocus></label><label>Password<input type="password" name="pass" required></label><button class="btn">Log in</button><p id="aerr" class="err"></p><a href="index.php" class="mu">Back to website</a></form></div>
<div id="dash" class="adm" hidden>
<aside class="side"><a class="brand" href="admin.php"><svg viewBox="0 0 48 48" width="34" height="34"><circle cx="24" cy="24" r="23" fill="#0B5D4B"/><path d="M7 35 20 14l7 12 4-6 10 15z" fill="#D9BC7E"/></svg><span>Bella Vista<small>Admin</small></span></a>
<nav><a href="#s-overview">Overview</a><a href="#s-payments">Payments</a><a href="#s-res">Reservations</a><a href="#s-rooms">Room status</a><a href="#s-reports">Revenue report</a></nav>
<div class="sidefoot"><button id="theme" aria-label="Theme">&#9790;</button><a href="index.php">View website</a><button id="logout">Log out</button></div></aside>
<main class="mainadm"><header class="tb"><h1>Dashboard</h1><span id="today" class="mu"></span></header>
<section id="s-overview"><div id="stats" class="kpis"></div></section>
<section id="s-payments"><h2>Payments to verify</h2><div class="panel tw"><table id="pays"></table></div></section>
<section id="s-res"><div class="sh"><h2>Reservations</h2><select id="rfilter"><option value="">All statuses</option><option>pending</option><option>approved</option><option>completed</option><option>cancelled</option></select></div><div class="panel tw"><table id="res"></table></div></section>
<section id="s-rooms"><h2>Room and event hall status (today)</h2><div class="panel tw"><table id="rooms"></table></div></section>
<section id="s-reports"><h2>Revenue by room type</h2><div id="bytype" class="panel"></div></section></main></div>
<script src="assets/app.js"></script></body></html>
