<?php
require 'config.php';
include 'head.php';

$info = [
    'standard' => ['Good for 2 to 3 guests', '₱1,000 extra for a 3rd guest'],
    'family' => ['Good for 4 to 5 guests', 'Covers up to 5 guests'],
    'hall' => ['Good for up to 30 guests (maximum 40)', '₱500 per guest beyond 30, up to 40'],
];

// Explicitly map the database room names to the image files that actually exist.
// Keep the database-driven room IDs, rates, capacity, and reservation links intact.
$imageFiles = [
    'room a' => 'room-a.png',
    'room b' => 'room-bbb.png',
    'room c' => 'room-a.png',
    'fr1' => 'fr1.png',
    'fr2' => 'fr2-copy.png',
    'fr3' => 'fr3.png',
    'event hall' => 'event-hall.png',
];

$rooms = $pdo->query('SELECT * FROM rooms WHERE active=1 ORDER BY id')->fetchAll();
$roomsByType = ['standard' => [], 'family' => [], 'hall' => []];
foreach ($rooms as $room) {
    if (isset($roomsByType[$room['type']])) {
        $roomsByType[$room['type']][] = $room;
    }
}

$sections = [
    'standard' => [
        'title' => 'Standard Rooms',
        'description' => 'Relax in a comfortable, thoughtfully appointed room—ideal for couples, friends, or a small family.',
        'class' => 'room-grid',
    ],
    'family' => [
        'title' => 'Family Rooms',
        'description' => 'Enjoy more space and comfort together, with room for family stays and shared moments.',
        'class' => 'room-grid',
    ],
    'hall' => [
        'title' => 'Event Hall',
        'description' => 'A spacious venue for celebrations, gatherings, and special occasions, with capacity for up to 40 guests.',
        'class' => 'hall-grid',
    ],
];

$renderRoom = static function (array $room) use ($info, $imageFiles): void {
    $name = (string) $room['name'];
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $type = (string) $room['type'];
    $imageKey = strtolower(trim($name));
    $image = $imageFiles[$imageKey] ?? '';
    $imagePath = $image !== '' ? 'assets/img/' . $image : '';
    $details = $info[$type] ?? ['', ''];
    ?>
    <article class="card room-card<?= $type === 'hall' ? ' room-card--hall' : '' ?>">
        <div class="room-card__image">
            <?php if ($imagePath !== ''): ?>
                <img src="<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>" alt="<?= $safeName ?>" loading="lazy"
                    onerror="this.hidden=true; this.parentElement.classList.add('room-card__image--missing')">
            <?php else: ?>
                <span class="room-card__image-fallback"><?= $safeName ?></span>
            <?php endif; ?>
        </div>
        <div class="room-card__content">
            <h3><?= $safeName ?></h3>
            <p class="room-card__capacity" data-t><?= htmlspecialchars($details[0], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="room-card__rate"><strong>₱<?= number_format((float) $room['rate']) ?></strong> <span data-t><?= $type === 'hall' ? 'per event' : 'per night' ?></span></p>
            <p class="room-card__note" data-t><?= htmlspecialchars($details[1], ENT_QUOTES, 'UTF-8') ?></p>
            <a class="btn room-card__button" href="make-reservation.php?room=<?= (int) $room['id'] ?>" data-t><?= $type === 'hall' ? 'Book the hall' : 'Book this room' ?></a>
        </div>
    </article>
    <?php
};
?>
<style>
    /* Scoped to the room listing so other pages keep their existing styles. */
    .rooms-page { max-width: 1440px; width: calc(100% - 2rem); padding-top: clamp(2rem, 5vw, 4rem); padding-bottom: clamp(3rem, 6vw, 5rem); }
    .rooms-page__intro { max-width: 680px; margin: 0 auto clamp(2rem, 4vw, 3rem); text-align: center; }
    .rooms-page__intro h1 { margin-bottom: .65rem; }
    .rooms-page__intro p { margin: 0; color: var(--mu); }
    .room-section + .room-section { margin-top: clamp(3rem, 6vw, 5rem); }
    .room-section__heading { margin-bottom: 1.5rem; }
    .room-section__heading h2 { margin: 0 0 .55rem; font-size: clamp(1.8rem, 3vw, 2.4rem); }
    .room-section__heading p { width: 100%; max-width: none; margin: 0; color: var(--mu); }
    .room-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: clamp(.85rem, 1.6vw, 1.25rem); align-items: stretch; }
    .hall-grid { display: grid; grid-template-columns: minmax(0, 1fr); }
    .room-card { display: flex; flex-direction: column; min-width: 0; height: 100%; overflow: hidden; padding: 0; }
    .room-card__image { position: relative; aspect-ratio: 3 / 2; overflow: hidden; background: linear-gradient(135deg, var(--emerald), var(--gold-lo)); }
    .room-card__image img { display: block; width: 100%; height: 100%; object-fit: cover; object-position: center; }
    .room-card__image--missing { display: grid; place-items: center; }
    .room-card__image-fallback { padding: 1rem; color: var(--on); font: 600 1.5rem var(--hd); }
    .room-card__content { display: flex; flex: 1; flex-direction: column; align-items: flex-start; padding: clamp(1rem, 2vw, 1.4rem); }
    .room-card__content h3 { margin: 0 0 .45rem; }
    .room-card__capacity, .room-card__note { margin: .15rem 0; color: var(--mu); }
    .room-card__rate { margin: .75rem 0 .25rem; }
    .room-card__rate strong { color: var(--tx); font-size: 1.2rem; }
    .room-card__rate span { color: var(--mu); font-size: .9rem; }
    .room-card__note { margin-bottom: 1.25rem; }
    .room-card__button { margin-top: auto; }
    .room-card--hall { display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr); }
    .room-card--hall .room-card__image { aspect-ratio: auto; min-height: 360px; }
    .room-card--hall .room-card__content { padding: clamp(1.5rem, 4vw, 3rem); justify-content: center; }
    @media (max-width: 800px) {
        .room-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .room-card--hall { grid-template-columns: minmax(0, 1fr); }
        .room-card--hall .room-card__image { aspect-ratio: 16 / 9; min-height: 0; }
    }
    @media (max-width: 560px) {
        .room-grid { grid-template-columns: minmax(0, 1fr); }
        .room-card__image { aspect-ratio: 16 / 10; }
        .room-card__button { width: 100%; text-align: center; }
    }
</style>
<section class="wrap rooms-page">
    <header class="rooms-page__intro">
        <h1 data-t>Rooms and event hall</h1>
        <p data-t>Find the right space for a restful stay, a family getaway, or your next special gathering.</p>
    </header>

    <?php foreach ($sections as $type => $section): ?>
        <section class="room-section" aria-labelledby="rooms-<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>">
            <div class="room-section__heading">
                <h2 id="rooms-<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>" data-t><?= htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p data-t><?= htmlspecialchars($section['description'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="<?= $section['class'] ?>">
                <?php foreach ($roomsByType[$type] as $room) { $renderRoom($room); } ?>
            </div>
        </section>
    <?php endforeach; ?>
</section>
<?php include 'foot.php'; ?>
