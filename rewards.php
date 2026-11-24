<?php require 'config.php';
include 'head.php'; ?>
<section class="wrap narrow">
    <h1 data-t>My points and rewards</h1>
    <p id="rmsg" class="err"></p>
    <p class="pts"><b id="pts">0</b> <span data-t>points</span></p>
    <p class="mu" data-t>You earn 1% of what you spend after each completed stay. Each spin costs 100 points.</p>
    <div class="wheelbox"><i class="ptr"></i><canvas id="wheel" width="360" height="360"></canvas></div>
    <button class="btn" id="spin" disabled data-t>Spin for 100 points</button>
    <p id="win" class="win"></p>
<h2>Daily minigames</h2>
<p class="mu">Play each game once a day to earn up to 5 points per game (10 a day). You need at least one completed stay.</p>
<p class="mgToday">Today’s minigame points: <b id="mgSum">0</b> / 10</p>
<div class="mgGrid">
    <article class="mg" data-game="toss">
        <h3>Golden Token Toss</h3>
        <p class="mgHow">Press on the token, drag down and release to toss it into the moving hoop. 1 point per basket (max 5).</p>
        <div class="mgHud"><span>Time <b class="mgT">18</b>s</span><span>Baskets <b class="mgS">0</b></span></div>
        <canvas width="360" height="480" aria-label="Golden Token Toss game"></canvas>
        <div class="mgBtns"><button class="mgBtn mgPlay" disabled>Play</button><button class="mgBtn mgClaim" hidden>Claim points</button></div>
        <p class="mgMsg" aria-live="polite"></p>
    </article>
    <article class="mg" data-game="catch">
        <h3>Resort Harvest Catch</h3>
        <p class="mgHow">Move the basket (mouse, touch or ← → keys). Gold +1, silver decoys −1, thorny burrs −2. 1 point per 3 score (max 5).</p>
        <div class="mgHud"><span>Time <b class="mgT">18</b>s</span><span>Score <b class="mgS">0</b></span></div>
        <canvas width="360" height="480" aria-label="Resort Harvest Catch game"></canvas>
        <div class="mgBtns"><button class="mgBtn mgPlay" disabled>Play</button><button class="mgBtn mgClaim" hidden>Claim points</button></div>
        <p class="mgMsg" aria-live="polite"></p>
    </article>
</div>
    <h2 id="vouchers" data-t>Your vouchers</h2>
    <ul id="vl" class="list"></ul>
    <h2 data-t>What you can win</h2>
    <ul class="list"><?php foreach (PRIZES as $p)
        echo '<li>' . htmlspecialchars($p[0]) . '<b>' . ($p[3] / 100) . '%</b></li>'; ?>
    </ul>
    <p class="mu" data-t>Free room vouchers cover the room rate for up to 2 nights. Extra guest fees still apply.</p>
</section>
<style>
    .mgToday { font-weight: 600 }
    .mgGrid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.2rem; margin: 1rem 0 2rem }
    .mg { background: #0A2214; color: #F5F2EB; border: 1.5px solid #D4AF37; border-radius: 14px; padding: 1.1rem; text-align: center }
    .mg h3 { margin: 0 0 .3rem; color: #D4AF37 }
    .mgHow { font-size: .9rem; opacity: .85; margin: 0 0 .6rem }
    .mgHud { display: flex; justify-content: space-between; font-weight: 600; margin-bottom: .5rem }
    .mgHud b { color: #D4AF37 }
    .mg canvas { display: block; width: 100%; max-width: 360px; aspect-ratio: 3/4; margin: 0 auto; touch-action: none; cursor: crosshair; border-radius: 10px; border: 1px solid rgba(212, 175, 55, .45) }
    .mgBtns { display: flex; flex-wrap: wrap; gap: .6rem; justify-content: center; margin: .8rem 0 .3rem }
    .mgBtn { font: inherit; font-weight: 600; border: 0; border-radius: 10px; padding: .65rem 1.3rem; cursor: pointer; background: #D4AF37; color: #0A2214 }
    .mgBtn.mgClaim { background: #F5F2EB }
    .mgBtn:disabled { opacity: .45; cursor: not-allowed }
    .mgMsg { min-height: 1.4em; margin: .3rem 0 0; font-size: .9rem }
</style>
<script>
(() => {
  const $ = s => document.querySelector(s), W = 360, H = 480, DUR = 18, FF = getComputedStyle(document.body).fontFamily;
  const api = (a, d = {}) => fetch('api.php', { method: 'POST', body: new URLSearchParams({ a, ...d }) }).then(r => r.json());
  const GOLD = '#D4AF37', FOREST = '#0A2214', CREAM = '#F5F2EB', box = {}, G = {};
  const coin = (c, x, y, r, fill, ring) => { c.fillStyle = fill; c.beginPath(); c.arc(x, y, r, 0, 7); c.fill(); c.strokeStyle = ring; c.lineWidth = 2; c.beginPath(); c.arc(x, y, r * .68, 0, 7); c.stroke() };
  const bg = c => { const g = c.createRadialGradient(W / 2, 0, 20, W / 2, H / 2, H); g.addColorStop(0, '#14402a'); g.addColorStop(1, FOREST); c.fillStyle = g; c.fillRect(0, 0, W, H) };

  /* ---------- Game 1: Golden Token Toss ---------- */
  const toss = add => {
    const g = 1300, R = 13, HY = 140, HW = 100, X0 = W / 2, Y0 = 420, K = 7.5;
    let t, tok, drag, wait, flash;
    const hx = () => W / 2 + Math.sin(t * (1.1 + t * .05)) * (70 + Math.min(t, DUR) * 2.5);
    const vel = d => { const dx = d.sx - d.cx, dy = d.sy - d.cy, l = Math.hypot(dx, dy) || 1, s = Math.min(l, 140) / l; return { vx: dx * s * K, vy: dy * s * K, l } };
    const respawn = () => { tok = { x: X0, y: Y0, vx: 0, vy: 0, fly: false, cd: false } };
    return {
      pts: s => s,
      reset() { t = 0; drag = null; wait = 0; flash = 0; respawn() },
      down(p) { if (!tok.fly) drag = { sx: p.x, sy: p.y, cx: p.x, cy: p.y } },
      move(p) { if (drag) { drag.cx = p.x; drag.cy = p.y } },
      up() { if (!drag) return; const v = vel(drag); drag = null; if (v.l >= 18 && v.vy < -150) { tok.vx = v.vx; tok.vy = v.vy; tok.fly = true } },
      step(dt) {
        t += dt; flash = Math.max(0, flash - dt); if (!tok.fly) return;
        const py = tok.y; tok.vy += g * dt; tok.x += tok.vx * dt; tok.y += tok.vy * dt; const h = hx();
        if (tok.vy > 0 && py < HY && tok.y >= HY && !tok.cd) {
          const d = Math.abs(tok.x - h);
          if (d < HW / 2 - 6) { tok.cd = true; add(1); flash = .35 }
          else if (d < HW / 2 + R * .6) { tok.vy = -tok.vy * .45; tok.vx += (tok.x > h ? 1 : -1) * 90 }
        }
        if (tok.y > H + 30 || tok.x < -30 || tok.x > W + 30) respawn()
      },
      draw(c) {
        bg(c); const h = hx(); c.save(); if (flash > 0) { c.shadowColor = GOLD; c.shadowBlur = 26 }
        c.strokeStyle = CREAM; c.lineWidth = 3; c.strokeRect(h - HW / 2 - 12, HY - 74, HW + 24, 62);
        c.strokeStyle = GOLD; c.lineWidth = 5; c.beginPath(); c.moveTo(h - HW / 2, HY); c.lineTo(h + HW / 2, HY); c.stroke();
        c.lineWidth = 1.5; c.strokeStyle = CREAM; c.beginPath();
        for (let i = 0; i <= 6; i++) { const x = h - HW / 2 + i * HW / 6; c.moveTo(x, HY); c.lineTo(h + (x - h) * .6, HY + 44) } c.stroke(); c.restore();
        if (drag) { const v = vel(drag); c.fillStyle = 'rgba(245,242,235,.8)'; let x = tok.x, y = tok.y, vx = v.vx, vy = v.vy;
          for (let i = 0; i < 16; i++) { vy += g * .05; x += vx * .05; y += vy * .05; if (y > H) break; c.beginPath(); c.arc(x, y, 3, 0, 7); c.fill() } }
        coin(c, tok.x, tok.y, R, GOLD, '#8a6d12')
      }
    }
  };

  /* ---------- Game 2: Resort Harvest Catch ---------- */
  const harvest = add => {
    const BW = 76, BY = H - 46, keys = {};
    let bx, tx, items, sp, t, fl, fc;
    return {
      pts: s => Math.floor(s / 3), keys,
      reset() { bx = tx = W / 2; items = []; sp = 0; t = 0; fl = 0; fc = GOLD; for (const k in keys) keys[k] = 0 },
      down(p) { tx = p.x }, move(p) { tx = p.x },
      step(dt) {
        t += dt; fl = Math.max(0, fl - dt);
        const kd = (keys.ArrowRight || keys.d ? 1 : 0) - (keys.ArrowLeft || keys.a ? 1 : 0); tx = Math.max(BW / 2, Math.min(W - BW / 2, tx + kd * 430 * dt));
        bx += (tx - bx) * Math.min(1, dt * 18);
        if ((sp -= dt) <= 0) { sp = Math.max(.38, .62 - t * .012); const r = Math.random();
          items.push({ x: 20 + Math.random() * (W - 40), y: -20, v: 150 + Math.random() * 70 + t * 7, k: r < .58 ? 0 : r < .74 ? 1 : 2, a: 0 }) }
        for (let i = items.length - 1; i >= 0; i--) {
          const o = items[i]; o.y += o.v * dt; o.a += dt * 3;
          if (o.y + 14 >= BY && o.y - 14 <= BY + 14 && Math.abs(o.x - bx) < BW / 2 + 6) { items.splice(i, 1); add([1, -1, -2][o.k]); fl = .25; fc = o.k ? '#c0503c' : '#fff3b0' }
          else if (o.y > H + 30) items.splice(i, 1)
        }
      },
      draw(c) {
        bg(c);
        for (const o of items) {
          if (o.k == 0) coin(c, o.x, o.y, 14, GOLD, '#8a6d12');
          else if (o.k == 1) coin(c, o.x, o.y, 14, '#d9dcd6', '#8b918a');
          else { c.save(); c.translate(o.x, o.y); c.rotate(o.a); c.fillStyle = '#A64B3C'; c.beginPath();
            for (let i = 0; i < 16; i++) { const r = i % 2 ? 9 : 17, a = i * Math.PI / 8; c.lineTo(Math.cos(a) * r, Math.sin(a) * r) } c.closePath(); c.fill(); c.restore() }
        }
        c.save(); if (fl > 0) { c.shadowColor = fc; c.shadowBlur = 22 }
        c.fillStyle = GOLD; c.beginPath(); c.moveTo(bx - BW / 2, BY); c.lineTo(bx + BW / 2, BY); c.lineTo(bx + BW / 2 - 10, BY + 32); c.lineTo(bx - BW / 2 + 10, BY + 32); c.closePath(); c.fill(); c.restore();
        c.strokeStyle = FOREST; c.lineWidth = 1.5; c.beginPath();
        for (let i = 1; i < 5; i++) { c.moveTo(bx - BW / 2 + i * BW / 5, BY); c.lineTo(bx - BW / 2 + 10 + i * (BW - 20) / 5, BY + 32) } c.moveTo(bx - BW / 2 + 3, BY + 14); c.lineTo(bx + BW / 2 - 3, BY + 14); c.stroke();
        c.fillStyle = CREAM; c.fillRect(bx - BW / 2 - 4, BY - 4, BW + 8, 6)
      }
    }
  };

  /* ---------- shared runner: timer, input, claim ---------- */
  const mount = (key, make) => {
    const el = $(`.mg[data-game="${key}"]`), cv = el.querySelector('canvas'), c = cv.getContext('2d'), q = s => el.querySelector(s);
    const ui = { t: q('.mgT'), s: q('.mgS'), play: q('.mgPlay'), claim: q('.mgClaim'), msg: q('.mgMsg') };
    let st = 'idle', raf = 0, last = 0, left = DUR, score = 0, locked = true;
    const add = n => { score = Math.max(0, score + n); ui.s.textContent = score };
    const impl = make(add), worth = () => Math.min(5, impl.pts(score));
    const overlay = txt => { c.fillStyle = 'rgba(10,34,20,.72)'; c.fillRect(0, 0, W, H); c.fillStyle = GOLD; c.textAlign = 'center'; c.font = `600 24px ${FF}`; c.fillText(txt, W / 2, H / 2) };
    const paint = () => { impl.draw(c); if (st == 'idle') overlay(locked ? 'Not available' : 'Press Play'); if (st == 'done') overlay(`Time! Score ${score}`) };
    const pos = e => { const r = cv.getBoundingClientRect(); return { x: (e.clientX - r.left) * W / r.width, y: (e.clientY - r.top) * H / r.height } };
    const loop = ts => {
      const dt = Math.min(.05, (ts - last) / 1000); last = ts; left -= dt; ui.t.textContent = Math.max(0, Math.ceil(left));
      impl.step(dt); paint(); if (left <= 0) return finish(); raf = requestAnimationFrame(loop)
    };
    const finish = () => {
      st = 'done'; cancelAnimationFrame(raf); paint(); const p = worth(); ui.play.disabled = false; ui.play.textContent = 'Play again';
      if (p >= 1) { ui.claim.hidden = false; ui.claim.disabled = false; ui.claim.textContent = `Claim ${p} point${p > 1 ? 's' : ''}`; ui.msg.textContent = `Score ${score}: worth ${p} point${p > 1 ? 's' : ''} (max 5). Claim it or play again to beat it.` }
      else ui.msg.textContent = `Score ${score}: too low to earn points. Play again!`
    };
    const lock = (msg, on = true) => { locked = on; ui.play.disabled = on; ui.claim.hidden = true; ui.msg.textContent = msg; if (st != 'playing') { st = 'idle'; paint() } };
    ui.play.onclick = async () => {
      ui.play.disabled = true; ui.claim.hidden = true; ui.msg.textContent = '';
      const r = await api('mini_start', { game: key }); if (r.error) { ui.msg.textContent = r.error; ui.play.disabled = false; return }
      score = 0; left = DUR; ui.s.textContent = 0; ui.t.textContent = DUR; ui.play.textContent = 'Play'; impl.reset(); st = 'playing'; last = performance.now(); raf = requestAnimationFrame(loop)
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
    impl.reset(); paint(); G[key] = { lock, el };
  };

  /* ---------- server state: login, completed stay, today's claims ---------- */
  async function refresh() {
    const s = await api('mini_status'), sum = $('#mgSum');
    if (s.error) { for (const k in G) G[k].lock('Log in to play.'); return }
    if (sum) sum.textContent = s.claimed.toss + s.claimed.catch;
    for (const k in G) {
      if (!s.eligible) G[k].lock('Complete a stay to earn minigame points.');
      else if (s.claimed[k]) G[k].lock(`Claimed today: +${s.claimed[k]} point${s.claimed[k] > 1 ? 's' : ''}. Come back tomorrow!`);
      else G[k].lock('', false)
    }
  }
  mount('toss', toss); mount('catch', harvest); refresh();
})();
</script>
<?php include 'foot.php'; ?>