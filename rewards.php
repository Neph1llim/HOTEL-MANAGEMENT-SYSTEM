<?php require 'config.php';
include 'head.php'; ?>
<section class="wrap narrow">
  <h1 data-t>My points and rewards</h1>
  <p id="rmsg" class="err"></p>
  <p class="pts"><b id="pts">0</b> <span data-t>points</span></p>
  <p class="mu" data-t>You earn <?= pctText(pointsPercent($pdo)) ?>% of what you spend after each completed stay. Each
    spin costs <?= SPIN_COST ?> points.</p>
  <div class="wheelbox"><i class="ptr"></i><canvas id="wheel" width="360" height="360"></canvas></div>
  <button class="btn" id="spin" disabled data-t>Spin for 100 points</button>
  <p id="win" class="win"></p>
</section>
<section class="wrap mgSection" id="minigames">
  <header class="mgIntro">
    <h2>Daily minigames</h2>
    <p class="mu">Play each game once a day to earn up to 5 points per game (10 a day). You need at least one completed
      stay.</p>
    <div class="mgToday"><span>Today’s minigame points</span><b><span id="mgSum">0</span> / 10</b><i class="mgDay"><i
          id="mgBar"></i></i></div>
  </header>
  <div class="mgGrid">
    <article class="mg" data-game="toss">
      <span class="mgTag">Game 1</span>
      <h3>Golden Token Toss</h3>
      <p class="mgHow">Press on the token, drag down and release to toss it into the moving hoop. 1 point per basket
        (max 5).</p>
      <div class="mgHud">
        <div class="mgChip"><small>Time</small><b><span class="mgT">18</span>s</b></div>
        <div class="mgChip"><small>Baskets</small><b class="mgS">0</b></div>
      </div>
      <i class="mgTime" aria-hidden="true"><i></i></i>
      <div class="mgStage"><canvas width="720" height="960" aria-label="Golden Token Toss game"></canvas></div>
      <div class="mgBtns"><button class="mgBtn mgPlay" disabled>Play</button><button class="mgBtn mgClaim" hidden>Claim
          points</button></div>
      <p class="mgMsg" aria-live="polite"></p>
    </article>
    <article class="mg" data-game="catch">
      <span class="mgTag">Game 2</span>
      <h3>Resort Harvest Catch</h3>
      <p class="mgHow">Move the basket (mouse, touch or ← → keys) to catch falling tokens. 1 point per 3 score (max 5).
      </p>
      <ul class="mgLegend">
        <li><i class="lgGold"></i>Gold +1</li>
        <li><i class="lgSilver"></i>Silver decoy −1</li>
        <li><i class="lgBurr"></i>Thorny burr −2</li>
      </ul>
      <div class="mgHud">
        <div class="mgChip"><small>Time</small><b><span class="mgT">18</span>s</b></div>
        <div class="mgChip"><small>Score</small><b class="mgS">0</b></div>
      </div>
      <i class="mgTime" aria-hidden="true"><i></i></i>
      <div class="mgStage"><canvas width="720" height="960" aria-label="Resort Harvest Catch game"></canvas></div>
      <div class="mgBtns"><button class="mgBtn mgPlay" disabled>Play</button><button class="mgBtn mgClaim" hidden>Claim
          points</button></div>
      <p class="mgMsg" aria-live="polite"></p>
    </article>
  </div>
</section>
<section class="wrap narrow">
  <h2 id="vouchers" data-t>Your vouchers</h2>
  <?php $vd = (int) $pdo->query("SELECT v FROM settings WHERE k='voucher_days'")->fetchColumn() ?: 30; ?>
  <p class="mu" data-t>New vouchers are valid for <?= $vd ?> days. Book before the use-by date.</p>
  <ul id="vl" class="list"></ul>
  <h2 data-t>What you can win</h2>
  <ul class="list"><?php foreach (wheelPrizes($pdo) as $p)
    echo '<li>' . htmlspecialchars($p['label']) . '<b>' . pctText($p['chance']) . '%</b></li>'; ?>
  </ul>
  <p class="mu" data-t>Free room vouchers cover the room rate for the room and the number of nights named in the reward.
    Extra guest fees still apply.</p>
</section>
<style>
  .wrap.mgSection {
    max-width: 1120px;
    padding-left: 1.25rem;
    padding-right: 1.25rem;
    margin-top: 2.5rem
  }

  .mgIntro {
    text-align: center;
    margin-bottom: 1.6rem
  }

  .mgIntro h2 {
    margin: 0 0 .3rem
  }

  .mgToday {
    display: inline-flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: .5rem 1rem;
    min-width: min(100%, 320px);
    margin-top: .7rem;
    padding: .8rem 1.3rem;
    color: #F5F2EB;
    background: linear-gradient(180deg, #123824, #0A2214);
    border: 1px solid rgba(212, 175, 55, .5);
    border-radius: 18px;
    box-shadow: 0 10px 28px rgba(10, 34, 20, .25)
  }

  .mgToday b {
    color: #D4AF37;
    font-size: 1.2rem;
    font-variant-numeric: tabular-nums
  }

  .mgDay {
    flex: 0 0 100%;
    height: 6px;
    overflow: hidden;
    border-radius: 99px;
    background: rgba(245, 242, 235, .15)
  }

  .mgDay i {
    display: block;
    width: 0;
    height: 100%;
    border-radius: 99px;
    background: linear-gradient(90deg, #B8922A, #F1DA8C);
    transition: width .6s ease
  }

  .mgGrid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr));
    gap: 1.6rem;
    align-items: start
  }

  .mg {
    position: relative;
    overflow: hidden;
    padding: 1.7rem 1.5rem 1.4rem;
    text-align: center;
    color: #F5F2EB;
    background: radial-gradient(120% 70% at 50% -10%, #1c4d32 0%, rgba(28, 77, 50, 0) 60%), linear-gradient(180deg, #123824, #0A2214 70%);
    border: 1px solid rgba(212, 175, 55, .5);
    border-radius: 22px;
    box-shadow: inset 0 1px 0 rgba(245, 242, 235, .08), 0 22px 50px rgba(5, 18, 10, .4), 0 0 44px rgba(212, 175, 55, .1)
  }

  .mg::before {
    content: "";
    position: absolute;
    inset: 0 0 auto;
    height: 3px;
    background: linear-gradient(90deg, transparent, #D4AF37, #F1DA8C, #D4AF37, transparent)
  }

  .mgTag {
    display: inline-block;
    padding: .15rem .8rem;
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .16em;
    text-transform: uppercase;
    color: #D4AF37;
    border: 1px solid rgba(212, 175, 55, .55);
    border-radius: 99px;
    background: rgba(212, 175, 55, .08)
  }

  .mg h3 {
    margin: .6rem 0 .35rem;
    font-size: 1.55rem;
    color: #D4AF37
  }

  .mgHow {
    max-width: 44ch;
    margin: 0 auto .8rem;
    font-size: .92rem;
    line-height: 1.5;
    color: rgba(245, 242, 235, .85)
  }

  .mgLegend {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: .4rem 1rem;
    margin: 0 0 .9rem;
    padding: 0;
    list-style: none;
    font-size: .82rem;
    color: rgba(245, 242, 235, .9)
  }

  .mgLegend i {
    display: inline-block;
    width: .8rem;
    height: .8rem;
    margin-right: .35rem;
    vertical-align: -.08rem;
    border-radius: 50%
  }

  .lgGold {
    background: radial-gradient(circle at 35% 30%, #FFF3B0, #C9971F);
    box-shadow: 0 0 8px rgba(212, 175, 55, .8)
  }

  .lgSilver {
    background: radial-gradient(circle at 35% 30%, #F5F2EB, #8f978f);
    outline: 1px dashed rgba(245, 242, 235, .6);
    outline-offset: 2px
  }

  .lgBurr {
    background: #B8452F;
    clip-path: polygon(50% 0, 62% 28%, 96% 30%, 70% 52%, 82% 88%, 50% 68%, 18% 88%, 30% 52%, 4% 30%, 38% 28%);
    border-radius: 0;
    width: .95rem;
    height: .95rem
  }

  .mgHud {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .7rem;
    margin-bottom: .6rem
  }

  .mgChip {
    padding: .45rem .6rem;
    border-radius: 14px;
    background: rgba(4, 14, 8, .55);
    border: 1px solid rgba(212, 175, 55, .3)
  }

  .mgChip small {
    display: block;
    font-size: .68rem;
    letter-spacing: .16em;
    text-transform: uppercase;
    color: rgba(245, 242, 235, .7)
  }

  .mgChip b {
    display: inline-block;
    font-size: 1.75rem;
    line-height: 1.15;
    color: #D4AF37;
    font-variant-numeric: tabular-nums
  }

  .mgChip.low b {
    color: #ff9d8a;
    animation: mgPulse .6s ease-in-out infinite alternate
  }

  .mgChip.bump b {
    animation: mgBump .3s ease-out
  }

  .mgTime {
    display: block;
    height: 5px;
    margin-bottom: .75rem;
    overflow: hidden;
    border-radius: 99px;
    background: rgba(245, 242, 235, .12)
  }

  .mgTime>i {
    display: block;
    height: 100%;
    transform-origin: left;
    background: linear-gradient(90deg, #B8922A, #F1DA8C)
  }

  .mgStage {
    padding: .6rem;
    border-radius: 18px;
    background: rgba(4, 14, 8, .6);
    border: 1px solid rgba(212, 175, 55, .3);
    box-shadow: inset 0 0 30px rgba(0, 0, 0, .45)
  }

  .mg canvas {
    display: block;
    width: 100%;
    max-width: 440px;
    aspect-ratio: 3/4;
    margin: 0 auto;
    touch-action: none;
    cursor: crosshair;
    border-radius: 12px
  }

  .mgBtns {
    display: flex;
    flex-wrap: wrap;
    gap: .7rem;
    justify-content: center;
    margin: 1rem 0 .3rem
  }

  .mgBtn {
    min-width: 9rem;
    padding: .8rem 1.6rem;
    font: inherit;
    font-weight: 700;
    letter-spacing: .02em;
    cursor: pointer;
    color: #0A2214;
    background: linear-gradient(135deg, #F1DA8C, #D4AF37);
    border: 0;
    border-radius: 12px;
    box-shadow: 0 6px 16px rgba(212, 175, 55, .3);
    transition: transform .15s, filter .2s
  }

  .mgBtn:hover:not(:disabled) {
    transform: translateY(-1px);
    filter: brightness(1.07)
  }

  .mgBtn:focus-visible {
    outline: 3px solid #F5F2EB;
    outline-offset: 3px
  }

  .mgBtn.mgClaim {
    background: #F5F2EB;
    box-shadow: 0 6px 16px rgba(245, 242, 235, .2)
  }

  .mgBtn:disabled {
    opacity: .45;
    cursor: not-allowed;
    box-shadow: none
  }

  .mgMsg {
    min-height: 1.4em;
    margin: .4rem 0 0;
    font-size: .92rem;
    line-height: 1.45;
    color: #F5F2EB
  }

  @keyframes mgPulse {
    from {
      transform: scale(1)
    }

    to {
      transform: scale(1.14)
    }
  }

  @keyframes mgBump {
    0% {
      transform: scale(1.35)
    }

    100% {
      transform: scale(1)
    }
  }

  @media (max-width: 520px) {
    .mg {
      padding: 1.4rem 1rem 1.2rem
    }

    .mgStage {
      padding: .4rem
    }
  }

  @media (prefers-reduced-motion: reduce) {

    .mgChip.low b,
    .mgChip.bump b {
      animation: none
    }

    .mgDay i,
    .mgBtn {
      transition: none
    }
  }

  #vl li.vch {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: .5rem 1rem;
    padding: .8rem 1rem;
    margin-bottom: .6rem;
    color: #F5F2EB;
    background: #0A2214;
    border: 1px solid #D4AF37;
    border-radius: 12px
  }

  #vl .vch small {
    display: block;
    font-size: .75rem;
    opacity: .8
  }

  #vl .vexp {
    text-align: right
  }

  #vl .vexp b {
    color: #D4AF37
  }

  #vl li.vch.soon {
    border-width: 2px
  }

  #vl .soon .vexp {
    padding: .3rem .7rem;
    color: #0A2214;
    background: #D4AF37;
    border-radius: 8px
  }

  #vl .soon .vexp b {
    color: #0A2214
  }

  #vl li.vch.out {
    color: #0A2214;
    background: #F5F2EB;
    border: 1px dashed #0A2214
  }

  #vl .out>span:first-child {
    text-decoration: line-through;
    opacity: .65
  }

  #vl .out .vexp b {
    color: #0A2214
  }
</style>
<script>
  (() => {
    const $ = s => document.querySelector(s), W = 360, H = 480, DUR = 18, RES = 2, FF = getComputedStyle(document.body).fontFamily;
    const RM = matchMedia('(prefers-reduced-motion: reduce)').matches, clock = () => RM ? 0 : performance.now() / 1000;
    const api = (a, d = {}) => fetch('api.php', { method: 'POST', body: new URLSearchParams({ a, ...d }) }).then(r => r.json());
    const GOLD = '#D4AF37', FOREST = '#0A2214', CREAM = '#F5F2EB', G = {};
    const rr = (c, x, y, w, h, r) => { c.beginPath(); c.moveTo(x + r, y); c.arcTo(x + w, y, x + w, y + h, r); c.arcTo(x + w, y + h, x, y + h, r); c.arcTo(x, y + h, x, y, r); c.arcTo(x, y, x + w, y, r); c.closePath() };
    const eob = x => 1 + 2.70158 * Math.pow(x - 1, 3) + 1.70158 * Math.pow(x - 1, 2);

    /* ---------- shared drawing: coins, hazards, resort backdrop ---------- */
    const coin = (c, x, y, r, a, b, ring, glow) => {
      c.save(); if (glow) { c.shadowColor = glow; c.shadowBlur = 14 }
      const g = c.createRadialGradient(x - r * .35, y - r * .4, r * .1, x, y, r); g.addColorStop(0, a); g.addColorStop(1, b);
      c.fillStyle = g; c.beginPath(); c.arc(x, y, r, 0, 7); c.fill(); c.restore();
      c.strokeStyle = ring; c.lineWidth = 2; c.beginPath(); c.arc(x, y, r * .7, 0, 7); c.stroke()
    };
    const goldCoin = (c, x, y, r) => {
      coin(c, x, y, r, '#FFF3B0', '#C9971F', '#8a6d12', 'rgba(212,175,55,.85)');
      c.fillStyle = '#8a6d12'; c.beginPath(); c.moveTo(x, y - r * .38); c.lineTo(x + r * .3, y); c.lineTo(x, y + r * .38); c.lineTo(x - r * .3, y); c.closePath(); c.fill()
    };
    const frond = (c, x, y, ang, len, col) => {
      c.save(); c.translate(x, y); c.rotate(ang); c.fillStyle = col; c.beginPath(); c.moveTo(0, 0);
      c.quadraticCurveTo(len * .5, -len * .26, len, 0); c.quadraticCurveTo(len * .5, len * .1, 0, 0); c.fill();
      c.strokeStyle = 'rgba(212,175,55,.2)'; c.lineWidth = 1; c.beginPath(); c.moveTo(0, 0); c.lineTo(len * .95, -1); c.stroke(); c.restore()
    };
    const scene = kind => {                                    /* drawn once, then copied every frame */
      const o = document.createElement('canvas'); o.width = W * RES; o.height = H * RES; const c = o.getContext('2d'); c.scale(RES, RES);
      const sky = c.createLinearGradient(0, 0, 0, H); sky.addColorStop(0, '#05140b'); sky.addColorStop(.5, '#0e3523'); sky.addColorStop(1, FOREST); c.fillStyle = sky; c.fillRect(0, 0, W, H);
      const mo = c.createRadialGradient(W * .8, 70, 3, W * .8, 70, 130); mo.addColorStop(0, 'rgba(245,242,235,.95)'); mo.addColorStop(.1, 'rgba(245,242,235,.4)'); mo.addColorStop(1, 'rgba(212,175,55,0)'); c.fillStyle = mo; c.fillRect(0, 0, W, 240);
      const ridge = (y, amp, col, s) => { c.fillStyle = col; c.beginPath(); c.moveTo(0, H); for (let x = 0; x <= W; x += 8) c.lineTo(x, y - Math.abs(Math.sin(x * .017 + s)) * amp - Math.sin(x * .05 + s * 2) * amp * .22); c.lineTo(W, H); c.closePath(); c.fill() };
      ridge(300, 80, '#0b2b1b', 1); ridge(350, 60, '#09231a', 3.4);
      const gy = kind == 'toss' ? H - 34 : H - 56, gg = c.createLinearGradient(0, gy, 0, H); gg.addColorStop(0, '#0f3a26'); gg.addColorStop(1, '#06160d');
      c.fillStyle = gg; c.fillRect(0, gy, W, H - gy); c.strokeStyle = 'rgba(212,175,55,.55)'; c.lineWidth = 1.5; c.beginPath(); c.moveTo(0, gy); c.lineTo(W, gy); c.stroke();
      if (kind == 'toss') {
        c.strokeStyle = 'rgba(212,175,55,.65)'; c.lineWidth = 3; c.beginPath(); c.moveTo(24, 58); c.lineTo(W - 24, 58); c.stroke();
        c.fillStyle = GOLD;[24, W - 24].forEach(x => { c.beginPath(); c.arc(x, 58, 5, 0, 7); c.fill() });
        const pad = c.createRadialGradient(W / 2, 436, 4, W / 2, 436, 60); pad.addColorStop(0, 'rgba(212,175,55,.35)'); pad.addColorStop(1, 'rgba(212,175,55,0)'); c.fillStyle = pad; c.fillRect(W / 2 - 70, 396, 140, 80);
        c.strokeStyle = 'rgba(212,175,55,.5)'; c.lineWidth = 1.5; c.beginPath(); c.ellipse(W / 2, 440, 34, 9, 0, 0, 7); c.stroke()
      } else {
        const sag = x => 26 + Math.sin(x / W * Math.PI * 3) * 8;
        c.strokeStyle = 'rgba(245,242,235,.25)'; c.lineWidth = 1.2; c.beginPath(); for (let x = 0; x <= W; x += 6) x ? c.lineTo(x, sag(x)) : c.moveTo(x, sag(x)); c.stroke();
        for (let i = 1; i < 12; i++) { const x = i * W / 12, y = sag(x) + 5, b = c.createRadialGradient(x, y, 1, x, y, 11); b.addColorStop(0, 'rgba(241,218,140,.9)'); b.addColorStop(1, 'rgba(241,218,140,0)'); c.fillStyle = b; c.fillRect(x - 11, y - 11, 22, 22); c.fillStyle = '#FFF3B0'; c.beginPath(); c.arc(x, y, 2.6, 0, 7); c.fill() }
        c.fillStyle = 'rgba(30,90,55,.85)'; for (let x = 4; x < W; x += 14) { c.beginPath(); c.moveTo(x, gy); c.lineTo(x + 3, gy - 8 - (x * 7 % 6)); c.lineTo(x + 6, gy); c.fill() }
      }
      for (let i = 0; i < 6; i++) { const a = .1 + i * .26, len = 140 - i * 8; frond(c, -8, -8, a, len, 'rgba(14,62,38,.95)'); frond(c, W + 8, -8, Math.PI - a, len, 'rgba(14,62,38,.95)') }
      const vg = c.createRadialGradient(W / 2, H / 2, H * .35, W / 2, H / 2, H * .8); vg.addColorStop(0, 'rgba(0,0,0,0)'); vg.addColorStop(1, 'rgba(0,0,0,.45)'); c.fillStyle = vg; c.fillRect(0, 0, W, H);
      return o
    };
    const SP = Array.from({ length: 14 }, (_, i) => ({ x: (i * 83 + 20) % W, y: 90 + (i * 47) % 280, p: i * 1.9 }));
    const sparkle = c => {                                       /* drifting fireflies */
      const t = clock(); for (const s of SP) {
        c.fillStyle = `rgba(241,218,140,${RM ? .3 : .15 + .4 * Math.abs(Math.sin(t * 1.3 + s.p))})`; c.beginPath();
        c.arc(s.x + (RM ? 0 : Math.sin(t * .6 + s.p) * 6), s.y + (RM ? 0 : Math.cos(t * .5 + s.p) * 5), 1.7, 0, 7); c.fill()
      }
    };

    /* ---------- Game 1: Golden Token Toss (same physics, rules and scoring as before) ---------- */
    const toss = (add, pop) => {
      const g = 1300, R = 13, HY = 140, HW = 100, X0 = W / 2, Y0 = 420, K = 7.5, bgc = scene('toss');
      let t, tok, drag, flash, fired, trail;
      const hx = () => W / 2 + Math.sin(t * (1.1 + t * .05)) * (70 + Math.min(t, DUR) * 2.5);
      const vel = d => { const dx = d.sx - d.cx, dy = d.sy - d.cy, l = Math.hypot(dx, dy) || 1, s = Math.min(l, 140) / l; return { vx: dx * s * K, vy: dy * s * K, l } };
      const respawn = () => { tok = { x: X0, y: Y0, vx: 0, vy: 0, fly: false, cd: false }; trail = [] };
      return {
        unit: 'baskets', pts: s => s,
        reset() { t = 0; drag = null; flash = 0; fired = false; respawn() },
        down(p) { if (!tok.fly) drag = { sx: p.x, sy: p.y, cx: p.x, cy: p.y } },
        move(p) { if (drag) { drag.cx = p.x; drag.cy = p.y } },
        up() { if (!drag) return; const v = vel(drag); drag = null; if (v.l >= 18 && v.vy < -150) { tok.vx = v.vx; tok.vy = v.vy; tok.fly = true; fired = true } },
        step(dt) {
          t += dt; flash = Math.max(0, flash - dt); if (!tok.fly) return;
          trail.push({ x: tok.x, y: tok.y }); if (trail.length > 10) trail.shift();
          const py = tok.y; tok.vy += g * dt; tok.x += tok.vx * dt; tok.y += tok.vy * dt; const h = hx();
          if (tok.vy > 0 && py < HY && tok.y >= HY && !tok.cd) {
            const d = Math.abs(tok.x - h);
            if (d < HW / 2 - 6) { tok.cd = true; add(1); flash = .45; pop(h, HY - 26, '+1', GOLD, 32) }
            else if (d < HW / 2 + R * .6) { tok.vy = -tok.vy * .45; tok.vx += (tok.x > h ? 1 : -1) * 90; pop(tok.x, HY - 14, 'Rim!', CREAM, 16) }
          }
          if (tok.y > H + 30 || tok.x < -30 || tok.x > W + 30) respawn()
        },
        draw(c) {
          c.drawImage(bgc, 0, 0, W, H); sparkle(c); const h = hx(), L = h - HW / 2, Rr = h + HW / 2;
          c.strokeStyle = 'rgba(212,175,55,.6)'; c.lineWidth = 2; c.beginPath(); c.moveTo(L - 12, 58); c.lineTo(L - 12, HY - 74); c.moveTo(Rr + 12, 58); c.lineTo(Rr + 12, HY - 74); c.stroke();
          c.save(); if (flash > 0) { c.shadowColor = GOLD; c.shadowBlur = 28 }
          rr(c, L - 12, HY - 74, HW + 24, 62, 8); c.fillStyle = 'rgba(245,242,235,.08)'; c.fill(); c.strokeStyle = CREAM; c.lineWidth = 3; c.stroke();
          c.strokeStyle = 'rgba(212,175,55,.85)'; c.lineWidth = 2; c.strokeRect(h - 24, HY - 42, 48, 28);
          const sw = RM ? 0 : Math.sin(t * 6) * (flash > 0 ? 6 : 1.5);
          c.lineWidth = 1.5; c.strokeStyle = 'rgba(245,242,235,.85)'; c.beginPath();
          for (let i = 0; i <= 6; i++) { const x = L + i * HW / 6; c.moveTo(x, HY); c.lineTo(h + (x - h) * .6 + sw, HY + 44) }
          for (let k = 1; k <= 3; k++) { const y = HY + k * 11, f = 1 - k * .1; c.moveTo(h - HW / 2 * f + sw * k / 4, y); c.lineTo(h + HW / 2 * f + sw * k / 4, y) } c.stroke();
          c.strokeStyle = GOLD; c.lineWidth = 5; c.lineCap = 'round'; c.beginPath(); c.moveTo(L, HY); c.lineTo(Rr, HY); c.stroke();
          c.fillStyle = CREAM;[L, Rr].forEach(x => { c.beginPath(); c.arc(x, HY, 4, 0, 7); c.fill() }); c.restore();
          if (flash > 0) { const p = 1 - flash / .45; c.strokeStyle = `rgba(241,218,140,${1 - p})`; c.lineWidth = 3; c.beginPath(); c.ellipse(h, HY, 30 + p * 60, 8 + p * 16, 0, 0, 7); c.stroke() }
          trail.forEach((p, i) => { c.fillStyle = `rgba(241,218,140,${i / trail.length * .35})`; c.beginPath(); c.arc(p.x, p.y, R * (.4 + i / trail.length * .5), 0, 7); c.fill() });
          if (drag) {                                             /* aim feedback: band, power ring, fading trajectory, power % */
            const v = vel(drag), pw = Math.min(1, v.l / 140), ok = v.vy < -150 && v.l >= 18;
            c.lineCap = 'round'; c.strokeStyle = ok ? 'rgba(212,175,55,.9)' : 'rgba(245,242,235,.35)'; c.lineWidth = 3; c.beginPath(); c.moveTo(tok.x, tok.y); c.lineTo(drag.cx, drag.cy); c.stroke();
            c.fillStyle = 'rgba(245,242,235,.25)'; c.beginPath(); c.arc(drag.cx, drag.cy, 12, 0, 7); c.fill();
            c.lineWidth = 4; c.strokeStyle = 'rgba(245,242,235,.2)'; c.beginPath(); c.arc(tok.x, tok.y, R + 9, 0, 7); c.stroke();
            c.strokeStyle = ok ? GOLD : '#cfd3cb'; c.beginPath(); c.arc(tok.x, tok.y, R + 9, -Math.PI / 2, -Math.PI / 2 + pw * Math.PI * 2); c.stroke();
            let x = tok.x, y = tok.y, vx = v.vx, vy = v.vy;
            for (let i = 0; i < 18; i++) { vy += g * .05; x += vx * .05; y += vy * .05; if (y > H) break; c.fillStyle = `rgba(245,242,235,${.85 - i * .045})`; c.beginPath(); c.arc(x, y, 3.2 - i * .06, 0, 7); c.fill() }
            c.fillStyle = ok ? GOLD : CREAM; c.font = `700 13px ${FF}`; c.textAlign = 'center'; c.fillText(Math.round(pw * 100) + '%', tok.x, tok.y + R + 28)
          }
          goldCoin(c, tok.x, tok.y, R);
          if (!fired && !drag) { c.fillStyle = 'rgba(245,242,235,.8)'; c.font = `600 13px ${FF}`; c.textAlign = 'center'; c.fillText('Pull down and release', X0, Y0 + 46) }
        }
      }
    };

    /* ---------- Game 2: Resort Harvest Catch (same spawn, speed, hitbox and scoring as before) ---------- */
    const harvest = (add, pop) => {
      const BW = 76, BY = H - 46, keys = {}, bgc = scene('catch');
      let bx, tx, items, sp, t, fl, fc, shk;
      const drawItem = (c, o) => {
        const x = o.x, y = o.y;
        if (o.k == 0) {                                          /* gold token: bright, glowing, with a sparkle */
          goldCoin(c, x, y, 14); const a = RM ? .7 : .4 + .5 * Math.abs(Math.sin(o.a * 2));
          c.strokeStyle = `rgba(255,255,255,${a})`; c.lineWidth = 1.5; c.beginPath(); c.moveTo(x + 9, y - 14); c.lineTo(x + 9, y - 6); c.moveTo(x + 5, y - 10); c.lineTo(x + 13, y - 10); c.stroke()
        } else if (o.k == 1) {                                   /* silver decoy: cool grey, dashed ring, minus sign */
          coin(c, x, y, 14, '#F5F2EB', '#8f978f', '#5f675f');
          c.save(); c.setLineDash([3, 3]); c.strokeStyle = 'rgba(245,242,235,.55)'; c.lineWidth = 1.5; c.beginPath(); c.arc(x, y, 17.5, 0, 7); c.stroke(); c.restore();
          c.fillStyle = '#4b524b'; c.fillRect(x - 5, y - 1.5, 10, 3)
        } else {                                                 /* thorny burr: crimson spikes, dark core, red warning glow */
          c.save(); c.translate(x, y); c.rotate(o.a); c.shadowColor = 'rgba(224,90,70,.9)'; c.shadowBlur = 12; c.fillStyle = '#B8452F'; c.beginPath();
          for (let i = 0; i < 16; i++) { const r = i % 2 ? 9 : 17, a = i * Math.PI / 8; c.lineTo(Math.cos(a) * r, Math.sin(a) * r) } c.closePath(); c.fill(); c.restore();
          c.fillStyle = '#4a150d'; c.beginPath(); c.arc(x, y, 6, 0, 7); c.fill(); c.fillStyle = '#ffb3a3'; c.beginPath(); c.arc(x - 2, y - 2, 1.6, 0, 7); c.fill()
        }
      };
      return {
        unit: 'score', pts: s => Math.floor(s / 3), keys,
        reset() { bx = tx = W / 2; items = []; sp = 0; t = 0; fl = 0; fc = GOLD; shk = 0; for (const k in keys) keys[k] = 0 },
        down(p) { tx = p.x }, move(p) { tx = p.x },
        step(dt) {
          t += dt; fl = Math.max(0, fl - dt); shk = Math.max(0, shk - dt);
          const kd = (keys.ArrowRight || keys.d ? 1 : 0) - (keys.ArrowLeft || keys.a ? 1 : 0); tx = Math.max(BW / 2, Math.min(W - BW / 2, tx + kd * 430 * dt));
          bx += (tx - bx) * Math.min(1, dt * 18);
          if ((sp -= dt) <= 0) {
            sp = Math.max(.38, .62 - t * .012); const r = Math.random();
            items.push({ x: 20 + Math.random() * (W - 40), y: -20, v: 150 + Math.random() * 70 + t * 7, k: r < .58 ? 0 : r < .74 ? 1 : 2, a: 0 })
          }
          for (let i = items.length - 1; i >= 0; i--) {
            const o = items[i]; o.y += o.v * dt; o.a += dt * 3;
            if (o.y + 14 >= BY && o.y - 14 <= BY + 14 && Math.abs(o.x - bx) < BW / 2 + 6) {
              items.splice(i, 1); add([1, -1, -2][o.k]); fl = .3; fc = o.k ? '#e0705a' : '#fff3b0'; if (o.k == 2 && !RM) shk = .3;
              pop(o.x, BY - 18, ['+1', '−1', '−2'][o.k], [GOLD, '#d9dcd6', '#ff8a73'][o.k], o.k == 0 ? 26 : 22)
            } else if (o.y > H + 30) items.splice(i, 1)
          }
        },
        draw(c) {
          c.drawImage(bgc, 0, 0, W, H); sparkle(c);
          c.save(); if (shk > 0) c.translate((Math.random() - .5) * 7 * shk / .3, (Math.random() - .5) * 7 * shk / .3);
          for (const o of items) drawItem(c, o);
          const k = fl > 0 ? Math.sin(fl / .3 * Math.PI) * .1 : 0, cy = BY + 32;
          c.translate(bx, cy); c.scale(1 + k, 1 - k); c.translate(-bx, -cy);
          c.save(); if (fl > 0) { c.shadowColor = fc; c.shadowBlur = 24 }
          const bg = c.createLinearGradient(0, BY, 0, BY + 32); bg.addColorStop(0, '#F1DA8C'); bg.addColorStop(1, '#B8922A'); c.fillStyle = bg;
          c.beginPath(); c.moveTo(bx - BW / 2, BY); c.lineTo(bx + BW / 2, BY); c.lineTo(bx + BW / 2 - 10, BY + 32); c.lineTo(bx - BW / 2 + 10, BY + 32); c.closePath(); c.fill(); c.restore();
          c.strokeStyle = 'rgba(10,34,20,.75)'; c.lineWidth = 1.5; c.beginPath();
          for (let i = 1; i < 5; i++) { c.moveTo(bx - BW / 2 + i * BW / 5, BY); c.lineTo(bx - BW / 2 + 10 + i * (BW - 20) / 5, BY + 32) }
          c.moveTo(bx - BW / 2 + 3, BY + 11); c.lineTo(bx + BW / 2 - 3, BY + 11); c.moveTo(bx - BW / 2 + 6, BY + 22); c.lineTo(bx + BW / 2 - 6, BY + 22); c.stroke();
          rr(c, bx - BW / 2 - 4, BY - 5, BW + 8, 8, 4); c.fillStyle = CREAM; c.fill(); c.strokeStyle = GOLD; c.lineWidth = 1.5; c.stroke();
          c.restore()
        }
      }
    };

    /* ---------- shared runner: timer, input, floating feedback, result card, claim ---------- */
    const mount = (key, make) => {
      const el = $(`.mg[data-game="${key}"]`), cv = el.querySelector('canvas'), c = cv.getContext('2d'), q = s => el.querySelector(s);
      cv.width = W * RES; cv.height = H * RES; c.setTransform(RES, 0, 0, RES, 0, 0);           /* crisp at any card width; game coordinates stay 360 x 480 */
      const ui = { t: q('.mgT'), s: q('.mgS'), bar: q('.mgTime > i'), play: q('.mgPlay'), claim: q('.mgClaim'), msg: q('.mgMsg') };
      ui.tChip = ui.t.closest('.mgChip'); ui.sChip = ui.s.closest('.mgChip');
      let st = 'idle', raf = 0, last = 0, left = DUR, score = 0, locked = true, endT = 0; const fx = [];
      const pop = (x, y, txt, col = GOLD, size = 22) => fx.push({ x, y, txt, col, size, t: 0 });
      const add = n => { score = Math.max(0, score + n); ui.s.textContent = score; ui.sChip.classList.remove('bump'); void ui.sChip.offsetWidth; ui.sChip.classList.add('bump') };
      const impl = make(add, pop), worth = () => Math.min(5, impl.pts(score));
      const stepFx = dt => { for (let i = fx.length - 1; i >= 0; i--) { fx[i].t += dt; fx[i].y -= 48 * dt; if (fx[i].t > .9) fx.splice(i, 1) } };
      const drawFx = () => {
        c.textAlign = 'center'; for (const f of fx) {
          c.save(); c.globalAlpha = Math.max(0, 1 - f.t / .9); c.fillStyle = f.col; c.shadowColor = 'rgba(0,0,0,.65)'; c.shadowBlur = 6;
          c.font = `800 ${f.size * (1 + Math.max(0, .2 - f.t) * 1.5)}px ${FF}`; c.fillText(f.txt, f.x, f.y); c.restore()
        }
      };
      const dim = a => { c.fillStyle = `rgba(6,20,12,${a})`; c.fillRect(0, 0, W, H) };
      const overlay = () => {
        c.textAlign = 'center';
        if (st == 'idle') {
          dim(.7); c.fillStyle = 'rgba(212,175,55,.18)'; c.beginPath(); c.arc(W / 2, H / 2 - 24, 42, 0, 7); c.fill();
          c.strokeStyle = GOLD; c.lineWidth = 3; c.stroke();
          if (!locked) { c.fillStyle = GOLD; c.beginPath(); c.moveTo(W / 2 - 11, H / 2 - 44); c.lineTo(W / 2 + 17, H / 2 - 24); c.lineTo(W / 2 - 11, H / 2 - 4); c.closePath(); c.fill() }
          else { c.fillStyle = GOLD; rr(c, W / 2 - 14, H / 2 - 26, 28, 22, 4); c.fill(); c.strokeStyle = GOLD; c.lineWidth = 3; c.beginPath(); c.arc(W / 2, H / 2 - 26, 9, Math.PI, 0); c.stroke() }
          c.fillStyle = CREAM; c.font = `700 22px ${FF}`; c.fillText(locked ? 'Not available' : 'Press Play', W / 2, H / 2 + 52);
          c.fillStyle = 'rgba(245,242,235,.7)'; c.font = `500 14px ${FF}`; c.fillText(locked ? 'See the message below' : DUR + ' seconds', W / 2, H / 2 + 76)
        } else if (st == 'done') {
          const e = endT, p = worth(); dim(.8 * e); c.save(); c.globalAlpha = e; c.translate(W / 2, H / 2 - 10); const s = .6 + .4 * eob(e); c.scale(s, s);
          c.fillStyle = CREAM; c.font = `700 14px ${FF}`; c.fillText('TIME’S UP', 0, -92);
          c.fillStyle = GOLD; c.font = `800 92px ${FF}`; c.fillText(String(score), 0, -8);
          c.fillStyle = CREAM; c.font = `600 16px ${FF}`; c.fillText(impl.unit, 0, 22);
          const chip = p >= 1 ? `+${p} point${p > 1 ? 's' : ''}` : 'Too low for points';
          c.font = `800 18px ${FF}`; const w = c.measureText(chip).width + 36; rr(c, -w / 2, 44, w, 38, 19);
          if (p >= 1) { c.fillStyle = GOLD; c.fill(); c.fillStyle = FOREST } else { c.strokeStyle = 'rgba(245,242,235,.6)'; c.lineWidth = 2; c.stroke(); c.fillStyle = CREAM }
          c.fillText(chip, 0, 69); c.fillStyle = 'rgba(245,242,235,.75)'; c.font = `500 14px ${FF}`; c.fillText(p >= 1 ? 'Claim them below' : 'Give it another go', 0, 108); c.restore()
        }
      };
      const paint = () => { impl.draw(c); drawFx(); overlay() };
      const pos = e => { const r = cv.getBoundingClientRect(); return { x: (e.clientX - r.left) * W / r.width, y: (e.clientY - r.top) * H / r.height } };
      const hud = () => { ui.t.textContent = Math.max(0, Math.ceil(left)); ui.bar.style.transform = `scaleX(${Math.max(0, left / DUR)})`; ui.tChip.classList.toggle('low', st == 'playing' && left <= 5) };
      const loop = ts => {
        const dt = Math.min(.05, (ts - last) / 1000); last = ts; left -= dt; hud();
        impl.step(dt); stepFx(dt); paint(); if (left <= 0) return finish(); raf = requestAnimationFrame(loop)
      };
      const finish = () => {
        st = 'done'; left = 0; hud(); cancelAnimationFrame(raf); const p = worth(), t0 = performance.now(); endT = RM ? 1 : 0;
        const anim = now => { endT = RM ? 1 : Math.min(1, (now - t0) / 450); stepFx(.016); paint(); if (endT < 1 || fx.length) raf = requestAnimationFrame(anim) }; raf = requestAnimationFrame(anim);
        ui.play.disabled = false; ui.play.textContent = 'Play again';
        if (p >= 1) { ui.claim.hidden = false; ui.claim.disabled = false; ui.claim.textContent = `Claim ${p} point${p > 1 ? 's' : ''}`; ui.msg.textContent = `Score ${score}: worth ${p} point${p > 1 ? 's' : ''} (max 5). Claim it or play again to beat it.` }
        else ui.msg.textContent = `Score ${score}: too low to earn points. Play again!`
      };
      const lock = (msg, on = true) => { locked = on; ui.play.disabled = on; ui.claim.hidden = true; ui.msg.textContent = msg; if (st != 'playing') { cancelAnimationFrame(raf); st = 'idle'; fx.length = 0; hud(); paint() } };
      ui.play.onclick = async () => {
        ui.play.disabled = true; ui.claim.hidden = true; ui.msg.textContent = '';
        const r = await api('mini_start', { game: key }); if (r.error) { ui.msg.textContent = r.error; ui.play.disabled = false; return }
        cancelAnimationFrame(raf); fx.length = 0; score = 0; left = DUR; ui.s.textContent = 0; ui.play.textContent = 'Play'; impl.reset(); st = 'playing'; hud(); pop(W / 2, H / 2, 'Go!', CREAM, 40);
        last = performance.now(); raf = requestAnimationFrame(loop)
      };
      ui.claim.onclick = async () => {
        ui.claim.disabled = true; const r = await api('mini_claim', { game: key, score });
        if (r.error) { ui.msg.textContent = r.error; ui.claim.disabled = false; if (/already claimed/i.test(r.error)) refresh(); return }
        const p = $('#pts'), sp = $('#spin'); if (p) p.textContent = r.total; if (sp) sp.disabled = r.total < 100; refresh()
      };
      cv.addEventListener('pointerdown', e => { if (st != 'playing') return; e.preventDefault(); cv.setPointerCapture(e.pointerId); impl.down && impl.down(pos(e)) });
      cv.addEventListener('pointermove', e => { if (st == 'playing') impl.move && impl.move(pos(e)) });
      ['pointerup', 'pointercancel'].forEach(n => cv.addEventListener(n, e => { if (st == 'playing') impl.up && impl.up(pos(e)) }));
      window.addEventListener('keydown', e => { if (st == 'playing' && impl.keys) { impl.keys[e.key.length == 1 ? e.key.toLowerCase() : e.key] = 1; if (e.key.startsWith('Arrow')) e.preventDefault() } });
      window.addEventListener('keyup', e => { if (impl.keys) impl.keys[e.key.length == 1 ? e.key.toLowerCase() : e.key] = 0 });
      impl.reset(); hud(); paint(); G[key] = { lock, el };
    };

    /* ---------- server state: login, completed stay, today's claims ---------- */
    async function refresh() {
      const s = await api('mini_status'), sum = $('#mgSum'), bar = $('#mgBar');
      if (s.error) { for (const k in G) G[k].lock('Log in to play.'); return }
      const day = s.claimed.toss + s.claimed.catch;
      if (sum) sum.textContent = day; if (bar) bar.style.width = Math.min(100, day * 10) + '%';
      for (const k in G) {
        if (!s.eligible) G[k].lock('Complete a stay to earn minigame points.');
        else if (s.claimed[k]) G[k].lock(`Claimed today: +${s.claimed[k]} point${s.claimed[k] > 1 ? 's' : ''}. Come back tomorrow!`);
        else G[k].lock('', false)
      }
    }
    mount('toss', toss); mount('catch', harvest); refresh();
  })();
</script>
<!-- ===== Spin celebration modal (scoped to rewards.php) ===== -->
<div id="pzModal" class="pzOv" hidden role="dialog" aria-modal="true" aria-labelledby="pzTitle">
  <canvas id="pzConfetti" aria-hidden="true"></canvas>
  <div class="pzCard">
    <div class="pzStar" aria-hidden="true">✦</div>
    <h2 id="pzTitle">Congratulations!</h2>
    <p class="pzSub">You won</p>
    <p class="pzPrize" id="pzPrize"></p>
    <button type="button" class="pzBtn" id="pzClose">Awesome!</button>
  </div>
</div>
<style>
  .pzOv {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: grid;
    place-items: center;
    padding: 1rem;
    background: rgba(5, 18, 10, .78);
    backdrop-filter: blur(3px);
    animation: pzFade .25s ease-out
  }

  .pzOv[hidden] {
    display: none
  }

  #pzConfetti {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none
  }

  .pzCard {
    position: relative;
    width: min(92vw, 400px);
    text-align: center;
    color: #F5F2EB;
    background: radial-gradient(120% 90% at 50% 0, #14402a, #0A2214 70%);
    border: 2px solid #D4AF37;
    border-radius: 20px;
    padding: 2rem 1.5rem 1.6rem;
    box-shadow: 0 0 0 6px rgba(212, 175, 55, .18), 0 24px 60px rgba(0, 0, 0, .55);
    animation: pzPop .55s cubic-bezier(.2, 1.4, .4, 1) both
  }

  .pzCard::before {
    content: "";
    position: absolute;
    inset: 8px;
    border: 1px solid rgba(245, 242, 235, .28);
    border-radius: 14px;
    pointer-events: none
  }

  .pzStar {
    font-size: 2.2rem;
    line-height: 1;
    color: #D4AF37;
    text-shadow: 0 0 18px rgba(212, 175, 55, .8)
  }

  .pzCard h2 {
    margin: .4rem 0 .2rem;
    font-size: clamp(1.7rem, 7vw, 2.2rem);
    color: #D4AF37
  }

  .pzSub {
    margin: 0;
    opacity: .85;
    letter-spacing: .08em;
    text-transform: uppercase;
    font-size: .85rem
  }

  .pzPrize {
    margin: .9rem 0 1.3rem;
    padding: .9rem 1rem;
    font-size: 1.35rem;
    font-weight: 700;
    line-height: 1.3;
    color: #F5F2EB;
    border: 1.5px solid #D4AF37;
    border-radius: 12px;
    background: rgba(212, 175, 55, .12)
  }

  .pzBtn {
    font: inherit;
    font-weight: 700;
    cursor: pointer;
    border: 0;
    border-radius: 12px;
    padding: .8rem 2.2rem;
    color: #0A2214;
    background: linear-gradient(135deg, #F1DA8C, #D4AF37)
  }

  .pzBtn:hover {
    filter: brightness(1.08)
  }

  .pzBtn:focus-visible {
    outline: 3px solid #F5F2EB;
    outline-offset: 3px
  }

  @keyframes pzPop {
    0% {
      opacity: 0;
      transform: scale(.55) translateY(20px)
    }

    70% {
      opacity: 1;
      transform: scale(1.06)
    }

    100% {
      transform: scale(1)
    }
  }

  @keyframes pzFade {
    from {
      opacity: 0
    }

    to {
      opacity: 1
    }
  }

  @media (prefers-reduced-motion: reduce) {

    .pzOv,
    .pzCard {
      animation: none
    }
  }
</style>
<script>
  (() => {
    const $ = s => document.querySelector(s), tr = s => (typeof t == 'function' ? (t(s) || s) : s);
    const modal = $('#pzModal'), cv = $('#pzConfetti'), win = $('#win');
    if (!modal || !win) return;
    const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
    let prize = '', last = '', raf = 0, opener = null;

    /* the server's exact prize name, read from the spin response (display only, nothing is changed) */
    const f0 = window.fetch;
    window.fetch = function (u, o) {
      const p = f0.apply(this, arguments);
      try {
        const b = o && o.body, hit = b instanceof FormData ? b.get('a') == 'spin' : /\bspin\b/.test(String(b || ''));
        if (hit) p.then(r => r.clone().json()).then(j => { if (j && j.prize) prize = j.prize }).catch(() => { })
      } catch (e) { }
      return p
    };

    /* ---------- confetti: gold and cream pieces falling behind the card ---------- */
    const COLORS = ['#D4AF37', '#F1DA8C', '#B8922A', '#F5F2EB', '#FFFFFF', '#E8D9A8'];
    function confetti() {
      cancelAnimationFrame(raf); if (reduce) return;
      const dpr = Math.min(2, devicePixelRatio || 1), W = innerWidth, H = innerHeight, c = cv.getContext('2d');
      cv.width = W * dpr; cv.height = H * dpr; c.setTransform(dpr, 0, 0, dpr, 0, 0);
      const n = W < 500 ? 110 : 190, parts = Array.from({ length: n }, () => ({
        x: Math.random() * W, y: -20 - Math.random() * H * .6, w: 6 + Math.random() * 7, h: 9 + Math.random() * 9,
        vy: 140 + Math.random() * 190, vx: -40 + Math.random() * 80, sw: Math.random() * 6, sa: 20 + Math.random() * 40,
        r: Math.random() * 6, vr: -5 + Math.random() * 10, col: COLORS[Math.random() * COLORS.length | 0], dot: Math.random() < .3
      }));
      let last = performance.now();
      const loop = now => {
        const dt = Math.min(.04, (now - last) / 1000); last = now; c.clearRect(0, 0, W, H); let alive = 0;
        for (const p of parts) {
          p.y += p.vy * dt; p.sw += dt * 3; p.x += p.vx * dt + Math.sin(p.sw) * p.sa * dt; p.r += p.vr * dt;
          if (p.y < H + 30) alive++; else continue;
          c.save(); c.translate(p.x, p.y); c.rotate(p.r); c.fillStyle = p.col; c.globalAlpha = .95;
          if (p.dot) { c.beginPath(); c.arc(0, 0, p.w / 2, 0, 7); c.fill() } else c.fillRect(-p.w / 2, -p.h / 2, p.w, p.h * Math.abs(Math.cos(p.sw)) + 2);
          c.restore()
        }
        if (alive) raf = requestAnimationFrame(loop); else c.clearRect(0, 0, W, H)
      };
      raf = requestAnimationFrame(loop)
    }

    /* ---------- open / close ---------- */
    function open(text) {
      $('#pzTitle').textContent = tr('Congratulations!'); $('#pzPrize').textContent = text;
      opener = document.activeElement; modal.hidden = false; document.body.style.overflow = 'hidden';
      $('#pzClose').focus(); confetti()
    }
    function close() {
      modal.hidden = true; document.body.style.overflow = ''; cancelAnimationFrame(raf);
      cv.getContext('2d').clearRect(0, 0, cv.width, cv.height); if (opener && opener.focus) opener.focus()
    }
    $('#pzClose').onclick = close;
    modal.addEventListener('click', e => { if (e.target == modal) close() });
    addEventListener('keydown', e => { if (!modal.hidden && e.key == 'Escape') close() });

    /* the wheel script writes the result into #win only after the wheel has stopped (and clears it when a spin starts) */
    new MutationObserver(() => {
      const txt = win.textContent.trim(); if (txt == last) return; last = txt;
      if (txt) { open(prize || txt); prize = '' }
    }).observe(win, { childList: true, characterData: true, subtree: true });
  })();
</script>
<?php include 'foot.php'; ?>