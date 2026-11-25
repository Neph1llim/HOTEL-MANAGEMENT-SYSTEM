<?php require 'config.php';
include 'head.php';
$info = ['standard' => ['Good for 2 to 3 guests', "₱1,000 extra for a 3rd guest"], 'family' => ['Good for 4 to 5 guests', 'Covers up to 5 guests'], 'hall' => ['Good for up to 30 guests (maximum 40)', '₱500 per guest beyond 30, up to 40']]; ?>
<section class="wrap">
    <h1 data-t>Rooms and event hall</h1>
    <p class="mu" data-t>Photos shown here are placeholders. Add your own in assets/img using the file name shown on
        each card.</p>
    <div class="grid"><?php foreach ($pdo->query('SELECT * FROM rooms WHERE active=1 ORDER BY id')->fetchAll() as $r) {
        $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $r['name']));
        $n = htmlspecialchars($r['name']); ?>
            <article class="card">
                <div class="rimg"><span><?= $n ?></span><img src="assets/img/<?= $slug ?>.png" alt="<?= $n ?>"
                        onerror="this.remove()"></div>
                <h3><?= $n ?></h3>
                <p data-t><?= $info[$r['type']][0] ?></p>
                <p><b>₱<?= number_format($r['rate']) ?></b> <span data-t>per night</span></p>
                <p class="mu" data-t><?= $info[$r['type']][1] ?></p>
                <p class="mu"><small>assets/img/<?= $slug ?>.png</small></p><a class="btn" href="make-reservation.php?room=<?= (int) $r['id'] ?>"
                    data-t><?= $r['type'] == 'hall' ? 'Book the hall' : 'Book this room' ?></a>
            </article><?php } ?>
    </div>
</section>
<?php include 'foot.php'; ?>