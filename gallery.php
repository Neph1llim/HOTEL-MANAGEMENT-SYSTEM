<?php
require 'config.php';
include 'head.php';

/*
 * Gallery images are mapped to verified files in assets/img/.
 * Exact duplicate photographs are intentionally shown only once.
 */
$galleryImages = [
    [
        'file' => 'assets/img/room-a.png',
        'category' => 'standard',
        'categoryLabel' => 'Standard Rooms',
        'title' => 'Standard Room',
        'description' => 'A standard hotel room with a comfortable bed, simple furnishings, a cozy atmosphere, and natural lighting from the windows.',
        'alt' => 'Standard guest room with twin beds and warm wood furnishings',
    ],
    [
        'file' => 'assets/img/fr1.png',
        'category' => 'family',
        'categoryLabel' => 'Family Rooms',
        'title' => 'Family Room with Twin Beds',
        'description' => 'A spacious-looking guest room arranged with twin beds, framed wall art, and a seating area by the window.',
        'alt' => 'Family room with twin beds and a window-side seating area',
    ],
    [
        'file' => 'assets/img/event-hall.png',
        'category' => 'event',
        'categoryLabel' => 'Event Hall',
        'title' => 'Event Hall Setup',
        'description' => 'An event space arranged with round dining tables, chairs, and a decorated ceiling.',
        'alt' => 'Event hall arranged with round tables and chairs',
    ],
    [
        'file' => 'assets/img/Dine-In-Area.png',
        'category' => 'dining',
        'categoryLabel' => 'Dining Areas',
        'title' => 'Indoor Dining Area',
        'description' => 'A dining room with tables, chairs, warm lighting, and greenery throughout the interior.',
        'alt' => 'Indoor dining area with tables and warm lighting',
    ],
    [
        'file' => 'assets/img/pool.png',
        'category' => 'amenities',
        'categoryLabel' => 'Pool & Leisure',
        'title' => 'Swimming Pool',
        'description' => 'An outdoor swimming pool bordered by loungers, greenery, and the hotel grounds.',
        'alt' => 'Outdoor swimming pool surrounded by greenery',
    ],
    [
        'file' => 'assets/img/entrance-event-hall.png',
        'category' => 'exterior',
        'categoryLabel' => 'Hotel & Grounds',
        'title' => 'Event Hall Entrance',
        'description' => 'An elegant event hall entrance with refined décor and a spacious layout, creating a welcoming first impression for guests.',
        'alt' => 'Event hall entrance framed by tropical landscaping',
    ],
    [
        'file' => 'assets/img/Hotel.png',
        'category' => 'exterior',
        'categoryLabel' => 'Hotel & Grounds',
        'title' => 'Hotel Exterior',
        'description' => 'An exterior view of the hotel building, with landscaped grounds and an illuminated pool area.',
        'alt' => 'Hotel exterior with landscaped grounds and pool area',
    ],
    [
        'file' => 'assets/img/Hotel-Lounge.png',
        'category' => 'facilities',
        'categoryLabel' => 'Hotel Interiors',
        'title' => 'Hotel Facility',
        'description' => 'A welcoming hotel lounge with comfortable seating, warm lighting, and a relaxed atmosphere for guests to unwind.',
        'alt' => 'Hotel facility interior',
    ],
    [
        'file' => 'assets/img/about.png',
        'category' => 'facilities',
        'categoryLabel' => 'Hotel Interiors',
        'title' => 'Hotel Interior',
        'description' => 'A bright and orderly hallway leading to the guest rooms, offering guests a pleasant and comfortable passage throughout the hotel.',
        'alt' => 'Hotel interior with seating, timber details, and plants',
    ],
];

?>
<main class="gallery-page">
    <section class="gallery-hero" aria-labelledby="gallery-title">
        <div class="gallery-hero__inner">
            <p class="gallery-eyebrow">Bella Vista Hotel &amp; Events Place</p>
            <h1 id="gallery-title">Our Gallery</h1>
            <p>Take a closer look at our rooms, gathering spaces, and hotel surroundings. We look forward to welcoming you to Bella Vista.</p>
        </div>
    </section>

    <section class="gallery-content" aria-label="Hotel photo gallery">
        <div class="gallery-intro">
            <p class="gallery-kicker">A closer look</p>
            <h2>Explore Bella Vista</h2>
            <p>Browse the spaces and scenes that make up the Bella Vista experience.</p>
        </div>

        <p class="gallery-count"><?= count($galleryImages) ?> photographs</p>

        <div class="gallery-grid" id="gallery-grid">
            <?php foreach ($galleryImages as $index => $image): ?>
                <article class="gallery-card" data-category="<?= htmlspecialchars($image['category'], ENT_QUOTES, 'UTF-8') ?>">
                    <button class="gallery-card__open" type="button" data-gallery-index="<?= $index ?>"
                        aria-label="View <?= htmlspecialchars($image['title'], ENT_QUOTES, 'UTF-8') ?>">
                        <span class="gallery-card__photo">
                            <img src="<?= htmlspecialchars($image['file'], ENT_QUOTES, 'UTF-8') ?>"
                                alt="<?= htmlspecialchars($image['alt'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                            <span class="gallery-card__view" aria-hidden="true">View photo <span>↗</span></span>
                        </span>
                    </button>
                    <div class="gallery-card__details">
                        <p class="gallery-card__category"><?= htmlspecialchars($image['categoryLabel'], ENT_QUOTES, 'UTF-8') ?></p>
                        <h3><?= htmlspecialchars($image['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= htmlspecialchars($image['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<div class="gallery-lightbox" id="gallery-lightbox" role="dialog" aria-modal="true" aria-labelledby="lightbox-title" aria-describedby="lightbox-description" hidden>
    <button class="gallery-lightbox__backdrop" type="button" data-lightbox-close aria-label="Close image preview"></button>
    <div class="gallery-lightbox__panel" role="document">
        <div class="gallery-lightbox__top">
            <p class="gallery-lightbox__counter" id="lightbox-counter" aria-live="polite">1 of 9</p>
            <button class="gallery-lightbox__close" type="button" data-lightbox-close aria-label="Close image preview">×</button>
        </div>
        <div class="gallery-lightbox__stage">
            <button class="gallery-lightbox__nav gallery-lightbox__prev" type="button" aria-label="Previous image">‹</button>
            <img id="lightbox-image" src="" alt="">
            <button class="gallery-lightbox__nav gallery-lightbox__next" type="button" aria-label="Next image">›</button>
        </div>
        <div class="gallery-lightbox__caption">
            <p class="gallery-card__category" id="lightbox-category"></p>
            <h2 id="lightbox-title"></h2>
            <p id="lightbox-description"></p>
        </div>
    </div>
</div>

<style>
.gallery-page { color:var(--tx); }
.gallery-hero { position:relative; overflow:hidden; padding:clamp(4rem,9vw,7.5rem) 1.5rem; text-align:center; color:#F7F4EC; background:linear-gradient(120deg,rgba(10,34,20,.96),rgba(17,51,32,.84)),url("assets/img/Hotel.png") center 48%/cover; }
.gallery-hero::after { content:""; position:absolute; inset:0; pointer-events:none; background:linear-gradient(0deg,rgba(10,34,20,.2),transparent 55%); }
.gallery-hero__inner { position:relative; z-index:1; max-width:760px; margin:0 auto; }
.gallery-eyebrow,.gallery-kicker { margin:0 0 .65rem; font-size:.75rem; font-weight:700; letter-spacing:.17em; text-transform:uppercase; color:var(--acc-tx); }
.gallery-hero .gallery-eyebrow { color:#E8C868; }
.gallery-hero h1 { margin:0 0 .75rem; font-size:clamp(2.8rem,7vw,5rem); letter-spacing:-.035em; }
.gallery-hero p:last-child { max-width:620px; margin:0 auto; color:#E3EAE4; font-size:clamp(1rem,2vw,1.15rem); }
.gallery-content { max-width:1440px; width:calc(100% - 3rem); margin:0 auto; padding:clamp(3rem,6vw,5rem) 0; }
.gallery-intro { margin-bottom:1.6rem; }
.gallery-intro h2 { margin:0 0 .55rem; font-size:clamp(1.8rem,3vw,2.5rem); }
.gallery-intro > p:last-child { margin:0; color:var(--mu); }
.gallery-count { margin:0 0 1rem; color:var(--mu); font-size:.85rem; }
.gallery-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1.35rem; align-items:stretch; }
.gallery-card { min-width:0; overflow:hidden; border:1px solid var(--ln); border-radius:15px; background:var(--sf); box-shadow:0 4px 16px rgba(10,34,20,.045); transition:transform .22s ease,box-shadow .22s ease; }
.gallery-card:hover { transform:translateY(-3px); box-shadow:0 12px 28px rgba(10,34,20,.11); }
.gallery-card__open { display:block; width:100%; padding:0; border:0; background:none; text-align:left; cursor:zoom-in; }
.gallery-card__photo { position:relative; display:block; overflow:hidden; aspect-ratio:4/3; background:linear-gradient(135deg,var(--emerald),var(--gold-lo)); }
.gallery-card__photo img { display:block; width:100%; height:100%; object-fit:cover; transition:transform .45s var(--ease); }
.gallery-card:hover .gallery-card__photo img { transform:scale(1.035); }
.gallery-card__view { position:absolute; right:.8rem; bottom:.8rem; padding:.42rem .7rem; border-radius:999px; background:rgba(10,34,20,.82); color:#fff; font-size:.78rem; opacity:0; transform:translateY(5px); transition:opacity .2s,transform .2s; }
.gallery-card:hover .gallery-card__view,.gallery-card__open:focus-visible .gallery-card__view { opacity:1; transform:none; }
.gallery-card__details { padding:1rem 1.1rem 1.2rem; }
.gallery-card__category { margin:0 0 .35rem; color:var(--acc-tx); font-size:.72rem; font-weight:700; letter-spacing:.09em; text-transform:uppercase; }
.gallery-card h3 { margin:0 0 .45rem; font-size:1.3rem; }
.gallery-card__details > p:last-child { margin:0; color:var(--mu); font-size:.92rem; line-height:1.6; }
.gallery-lightbox[hidden] { display:none; }
.gallery-lightbox { position:fixed; z-index:1000; inset:0; display:grid; place-items:center; padding:clamp(.5rem,2vw,1.5rem); }
.gallery-lightbox__backdrop { position:absolute; inset:0; width:100%; height:100%; border:0; background:rgba(4,15,9,.92); cursor:default; }
.gallery-lightbox__panel { position:relative; z-index:1; display:flex; flex-direction:column; width:min(1100px,100%); max-height:96dvh; overflow:auto; border:1px solid rgba(255,255,255,.16); border-radius:16px; background:var(--sf); color:var(--tx); box-shadow:0 25px 80px #0005; }
.gallery-lightbox__panel { opacity:0; transform:translateY(12px) scale(.97); transition:opacity .28s ease,transform .32s cubic-bezier(.2,.75,.25,1); }
.gallery-lightbox.is-open .gallery-lightbox__panel { opacity:1; transform:translateY(0) scale(1); }
.gallery-lightbox__stage img { opacity:1; }
@keyframes gallery-image-fade {
    from { opacity:.35; }
    to { opacity:1; }
}
.gallery-lightbox__stage img.is-entering { animation:gallery-image-fade .18s ease-out both; }
.gallery-lightbox__top { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:.7rem 1rem; }
.gallery-lightbox__counter { margin:0; color:var(--mu); font-size:.85rem; font-variant-numeric:tabular-nums; }
.gallery-lightbox__close { width:40px; height:40px; border:1px solid var(--ln); border-radius:50%; background:transparent; color:var(--tx); font-size:1.7rem; line-height:1; cursor:pointer; }
.gallery-lightbox__stage { position:relative; display:grid; place-items:center; min-height:180px; background:#08160E; }
.gallery-lightbox__stage img { display:block; width:100%; max-height:67vh; object-fit:contain; }
.gallery-lightbox__nav { position:absolute; top:50%; z-index:2; display:grid; place-items:center; width:44px; height:54px; transform:translateY(-50%); border:1px solid #ffffff45; border-radius:10px; background:#07150dc9; color:white; font-size:2.2rem; line-height:1; cursor:pointer; }
.gallery-lightbox__prev { left:clamp(.4rem,2vw,1rem); }
.gallery-lightbox__next { right:clamp(.4rem,2vw,1rem); }
.gallery-lightbox__caption { padding:1rem 1.3rem 1.35rem; }
.gallery-lightbox__caption h2 { margin:0 0 .45rem; font-size:clamp(1.4rem,3vw,2rem); }
.gallery-lightbox__caption > p:last-child { margin:0; color:var(--mu); }
body.gallery-lightbox-open { overflow:hidden; }
@media(max-width:900px) { .gallery-grid { grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; } .gallery-content { width:calc(100% - 2rem); } }
@media(max-width:560px) { .gallery-grid { grid-template-columns:minmax(0,1fr); } .gallery-content { width:calc(100% - 1.25rem); } .gallery-card__details { padding:1rem; } .gallery-lightbox__panel { max-height:98dvh; border-radius:12px; } .gallery-lightbox__stage img { max-height:55vh; } .gallery-lightbox__nav { width:38px; height:46px; } }
@media(prefers-reduced-motion:reduce) {
    .gallery-card,.gallery-card__photo img,.gallery-card__view,
    .gallery-lightbox__panel { transition:none; }
    .gallery-lightbox__stage img.is-entering { animation:none; }
}
</style>

<script>
(() => {
    const cards = [...document.querySelectorAll('.gallery-card')];
    const lightbox = document.getElementById('gallery-lightbox');
    const previewImage = document.getElementById('lightbox-image');
    const previewTitle = document.getElementById('lightbox-title');
    const previewDescription = document.getElementById('lightbox-description');
    const previewCategory = document.getElementById('lightbox-category');
    const counter = document.getElementById('lightbox-counter');
    const previous = lightbox.querySelector('.gallery-lightbox__prev');
    const next = lightbox.querySelector('.gallery-lightbox__next');
    const visibleIndexes = cards.map((_, index) => index);
    let currentIndex = 0;
    let returnFocus = null;

    const imageData = cards.map(card => {
        const image = card.querySelector('img');
        return {
            src: image.getAttribute('src'),
            alt: image.alt,
            title: card.querySelector('h3').textContent,
            description: card.querySelector('.gallery-card__details > p:last-child').textContent,
            category: card.querySelector('.gallery-card__category').textContent,
            filter: card.dataset.category
        };
    });

    function showImage(index) {
        currentIndex = (index + visibleIndexes.length) % visibleIndexes.length;
        const data = imageData[visibleIndexes[currentIndex]];
        previewImage.classList.remove('is-entering');
        previewImage.alt = data.alt;
        previewImage.src = data.src;
        // Restart the entrance animation for every image selection.
        void previewImage.offsetWidth;
        previewImage.classList.add('is-entering');
        previewTitle.textContent = data.title;
        previewDescription.textContent = data.description;
        previewCategory.textContent = data.category;
        counter.textContent = `${currentIndex + 1} of ${visibleIndexes.length}`;
        previous.disabled = visibleIndexes.length < 2;
        next.disabled = visibleIndexes.length < 2;
    }

    function openLightbox(index) {
        if (!visibleIndexes.length) return;
        returnFocus = document.activeElement;
        currentIndex = visibleIndexes.indexOf(index);
        if (currentIndex < 0) currentIndex = 0;
        lightbox.hidden = false;
        document.body.classList.add('gallery-lightbox-open');
        showImage(currentIndex);
        requestAnimationFrame(() => lightbox.classList.add('is-open'));
        lightbox.querySelector('.gallery-lightbox__close').focus();
    }

    function closeLightbox() {
        lightbox.classList.remove('is-open');
        lightbox.hidden = true;
        document.body.classList.remove('gallery-lightbox-open');
        previewImage.removeAttribute('src');
        previewImage.classList.remove('is-entering');
        if (returnFocus) returnFocus.focus();
    }

    cards.forEach((card, index) => card.querySelector('.gallery-card__open').addEventListener('click', () => openLightbox(index)));
    previous.addEventListener('click', () => showImage(currentIndex - 1));
    next.addEventListener('click', () => showImage(currentIndex + 1));
    lightbox.querySelectorAll('[data-lightbox-close]').forEach(button => button.addEventListener('click', closeLightbox));

    document.addEventListener('keydown', event => {
        if (lightbox.hidden) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            closeLightbox();
        } else if (event.key === 'ArrowLeft') {
            event.preventDefault();
            showImage(currentIndex - 1);
        } else if (event.key === 'ArrowRight') {
            event.preventDefault();
            showImage(currentIndex + 1);
        }
    });

})();
</script>

<?php include 'foot.php'; ?>
