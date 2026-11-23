<?php define('ADMIN', 1); ?><!doctype html>
<html lang="en" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Bella Vista | Admin</title>
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
        <aside class="side"><a class="brand" href="admin.php"><svg viewBox="0 0 48 48" width="34" height="34">
                    <circle cx="24" cy="24" r="23" fill="#0B5D4B" />
                    <path d="M7 35 20 14l7 12 4-6 10 15z" fill="#D9BC7E" />
                </svg><span>Bella Vista<small>Admin</small></span></a>
            <nav><a href="#s-overview">Overview</a><a href="#s-payments">Payments</a><a href="#s-res">Reservations</a><a
                    href="#s-walkin">Walk-in booking</a><a href="#s-rooms">Rooms &amp; rates</a><a
                    href="#s-reports">Reports</a></nav>
            <div class="sidefoot"><button id="theme" aria-label="Theme">&#9790;</button><a href="index.php">View
                    website</a><button id="logout">Log out</button></div>
        </aside>
        <main class="mainadm">
            <header class="tb">
                <h1>Dashboard</h1><span id="today" class="mu"></span>
            </header>
            <section id="s-overview">
                <div id="stats" class="kpis"></div>
            </section>
            <section id="s-payments">
                <h2>Payments to verify</h2>
                <div class="panel tw">
                    <table id="pays"></table>
                </div>
            </section>
            <section id="s-res">
                <div class="sh">
                    <h2>Reservations</h2><select id="rfilter">
                        <option value="">All statuses</option>
                        <option>pending</option>
                        <option>approved</option>
                        <option>completed</option>
                        <option>cancelled</option>
                    </select>
                </div>
                <div class="panel tw">
                    <table id="res"></table>
                </div>
            </section>
            <section id="s-walkin">
                <h2>Walk-in booking</h2>
                <p class="mu">For a guest at the front desk. The booking is approved straight away and the downpayment
                    is recorded as cash received.</p>
                <form id="wf" class="panel wform"><label>Guest name<input name="name" required
                            maxlength="80"></label><label>Mobile number<input name="phone" required
                            maxlength="30"></label><label>Email (optional)<input type="email" name="email"
                            maxlength="100"></label>
                    <label>Room<select name="room" required></select></label><label>Check-in<input type="date" name="ci"
                            required></label><label>Check-out<input type="date" name="co"
                            required></label><label>Guests<input type="number" name="pax" min="1" value="2"
                            required></label>
                    <label>Cash received (₱)<input type="number" name="amount" min="1" step="0.01"
                            placeholder="Downpayment"></label><button class="btn">Book and approve</button>
                    <p id="wq" class="wq" hidden></p>
                    <p id="wmsg" class="wmsg"></p>
                </form>
            </section>
            <section id="s-rooms">
                <h2>Rooms &amp; rates</h2>
                <p class="mu">Edit a row and press Save. A new rate applies to future bookings only; existing
                    reservations keep the price they were made at. Switch a room off to stop new online bookings (for
                    maintenance, say).</p>
                <div class="panel tw">
                    <table id="rooms"></table>
                </div>
                <p id="rmsg" class="err"></p>
                <form id="raf" class="panel wform"><b class="wfh">Add a room</b><label>Name<input name="name" required
                            maxlength="30"></label><label>Type<select name="type">
                            <option value="standard">Standard</option>
                            <option value="family">Family</option>
                            <option value="hall">Event hall</option>
                        </select></label><label>Rate (₱/night)<input type="number" name="rate" min="1"
                            required></label><label>Standard capacity<input type="number" name="base_cap" min="1"
                            value="2" required></label><label>Maximum capacity<input type="number" name="max_cap"
                            min="1" value="3" required></label><button class="btn">Add room</button></form>
            </section>
            <section id="s-reports">
                <h2>Printable report</h2>
                <form id="rpf" class="panel wform" action="report.php" method="get" target="_blank"><label>From<input
                            type="date" name="from" required></label><label>To<input type="date" name="to"
                            required></label>
                    <label>Date refers to<select name="by">
                            <option value="checkin">Check-in date</option>
                            <option value="booked">Date booked</option>
                        </select></label><button class="btn">Open report</button>
                </form>
                <h2>Booked value by room type</h2>
                <div id="bytype" class="panel"></div>
            </section>
        </main>
    </div>
    <dialog id="rfd">
        <form id="rff" class="rff">
            <h3>Refund</h3>
            <p id="rfinfo" class="mu"></p>
            <label>Status<select name="st">
                    <option value="pending">Refund pending</option>
                    <option value="refunded">Refunded</option>
                    <option value="none">No refund (hotel keeps it)</option>
                </select></label>
            <label>Amount (₱)<input type="number" name="amount" min="0" step="0.01"></label><label>Note (e.g. GCash
                reference)<input name="note" maxlength="120"></label>
            <p id="rferr" class="err"></p>
            <div class="row"><button class="btn">Save</button><button type="button" class="btn ghost"
                    id="rfx">Close</button></div>
        </form>
    </dialog>
    <script src="assets/app.js"></script>
    <script src="assets/admin.js"></script>
</body>

</html>