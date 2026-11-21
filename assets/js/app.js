(() => {
const $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => [...r.querySelectorAll(s)];
const B = document.body.dataset.base || '', CSRF = document.body.dataset.csrf;
const peso = n => '₱' + Number(n).toLocaleString('en-PH', {minimumFractionDigits: 0, maximumFractionDigits: 2});

// image slots: if the photo is missing, show the labelled placeholder
$$('.slot img').forEach(i => { const bad = () => i.remove(); i.addEventListener('error', bad); if (i.complete && i.naturalWidth === 0) bad(); });

// side menu
const dr = $('#drawer'), sc = $('#scrim'), mb = $('#menuBtn');
if (mb) { const t = o => { dr.classList.toggle('open', o); sc.classList.toggle('open', o); mb.setAttribute('aria-expanded', o); };
  mb.onclick = () => t(!dr.classList.contains('open')); sc.onclick = () => t(false); document.addEventListener('keydown', e => e.key === 'Escape' && t(false)); }

// dialogs and confirms
$$('[data-modal]').forEach(b => b.addEventListener('click', () => $('#' + b.dataset.modal).showModal()));
$$('[data-close]').forEach(b => b.addEventListener('click', () => b.closest('dialog').close()));
$$('[data-confirm]').forEach(el => (el.tagName === 'FORM' ? el : el).addEventListener(el.tagName === 'FORM' ? 'submit' : 'click', e => { if (!confirm(el.dataset.confirm)) e.preventDefault(); }));
const ar = $('[data-refresh]'); if (ar) setTimeout(() => location.reload(), ar.dataset.refresh * 1000);

// ---------- booking page ----------
const f = $('#book');
if (f) {
  const rooms = $$('.room-opt', f), vopts = $$('.v-opt[data-applies]', f), g = f.elements.guests;
  const type = () => f.querySelector('input[name=type]:checked').value, room = () => f.querySelector('input[name=room]:checked');
  const ci = f.elements.checkin, co = f.elements.checkout;
  function syncType() {
    rooms.forEach(r => { r.hidden = r.dataset.type !== type(); });
    const cur = room(); if (!cur || cur.closest('.room-opt').hidden) { const first = rooms.find(r => !r.hidden && !r.classList.contains('taken')); if (first) first.querySelector('input').checked = true; else if (cur) cur.checked = false; }
    vopts.forEach(v => { const ok = v.dataset.applies === 'any' || v.dataset.applies === type(); v.classList.toggle('off', !ok); v.querySelector('input').disabled = !ok; if (!ok && v.querySelector('input').checked) f.querySelector('input[name=vpick][value="0"]').checked = true; });
    const r = room(); if (r) { const m = +r.closest('.room-opt').dataset.max; g.max = m; if (+g.value > m) g.value = m; }
    quote();
  }
  async function avail() {
    if (!ci.value || !co.value || co.value <= ci.value) { $$('.avail', f).forEach(a => a.textContent = 'Select dates to check availability'); return; }
    co.min = ci.value;
    try { const d = await (await fetch(`${B}api/availability.php?in=${ci.value}&out=${co.value}`)).json(); if (!d.ok) return;
      rooms.forEach(r => { const inp = r.querySelector('input'), free = d.rooms[inp.value], a = $('.avail', r);
        r.classList.toggle('taken', !free); inp.disabled = !free; a.textContent = free ? 'Available for your dates' : 'Not available for your dates'; a.classList.toggle('yes', free);
        if (!free && inp.checked) { inp.checked = false; } });
      if (!room()) { const first = rooms.find(r => !r.hidden && !r.classList.contains('taken')); if (first) first.querySelector('input').checked = true; }
    } catch (e) {}
    quote();
  }
  async function quote() {
    const body = $('#sumBody'), r = room(), vp = f.querySelector('input[name=vpick]:checked');
    f.elements.voucher.value = vp ? vp.value : 0;
    if (!r || !ci.value || !co.value) { body.textContent = 'Choose a room and dates to see your total.'; return; }
    try { const d = await (await fetch(`${B}api/quote.php?room=${r.value}&in=${ci.value}&out=${co.value}&guests=${g.value}&voucher=${f.elements.voucher.value}`)).json();
      if (!d.ok) { body.textContent = d.error; return; }
      body.innerHTML = `<dl><div><dt>Room (${d.nights} night${d.nights > 1 ? 's' : ''})</dt><dd>${peso(d.base)}</dd></div>` +
        (d.extra ? `<div><dt>Extra guest fee</dt><dd>${peso(d.extra)}</dd></div>` : '') +
        (d.voucher_discount ? `<div><dt>Voucher: ${d.voucher_label}</dt><dd>−${peso(d.voucher_discount)}</dd></div>` : '') +
        (d.vip_discount ? `<div><dt>VIP 20% discount</dt><dd>−${peso(d.vip_discount)}</dd></div>` : '') +
        `<div class="tot"><dt>Total</dt><dd>${peso(d.total)}</dd></div><div class="due"><dt>Pay now (50% downpayment)</dt><dd>${peso(d.down)}</dd></div><div><dt>Balance at check-in</dt><dd>${peso(d.balance)}</dd></div></dl>` +
        (d.available ? '' : '<p class="alert">This room is not available for those dates.</p>');
    } catch (e) { body.textContent = 'Could not calculate the price. Check that XAMPP is running.'; }
  }
  $('#voucherBtn').onclick = () => { const l = $('#voucherList'); l.hidden = !l.hidden; };
  if (+f.dataset.voucher) $('#voucherList').hidden = false;
  f.addEventListener('change', e => { if (e.target.name === 'type') syncType(); else if (e.target.name === 'checkin') { if (co.value <= ci.value) co.value = ''; avail(); } else if (e.target.name === 'checkout') avail(); else quote(); });
  g.addEventListener('input', quote);
  syncType(); avail(); setInterval(avail, 10000);          // live availability
  // GCash QR: admin changes show here without reloading
  setInterval(async () => { try { const d = await (await fetch(B + 'api/qr.php')).json(); const img = $('#qrImg'); if (d.ok && img.dataset.v !== d.v) { img.src = d.path + '?t=' + Date.now(); img.dataset.v = d.v; img.hidden = false; $('#qrNone') && ($('#qrNone').hidden = true); $('#qrDate').textContent = 'QR for ' + d.date; } } catch (e) {} }, 5000);
}

// ---------- reward wheel ----------
const cv = $('#wheel');
if (cv) {
  const labels = ['20% off', '50% off', '75% off + meals', 'Free Standard', 'Free Family', 'VIP'], cols = ['#6b8a5a', '#c98a3a', '#3f6d8c', '#8c4f6b', '#b4553a', '#c9a227'];
  const c = cv.getContext('2d'), R = 180, a = Math.PI * 2 / 6;
  labels.forEach((l, i) => { c.beginPath(); c.moveTo(R, R); c.arc(R, R, R, -Math.PI / 2 + i * a, -Math.PI / 2 + (i + 1) * a); c.fillStyle = cols[i]; c.fill(); c.strokeStyle = '#fff'; c.lineWidth = 3; c.stroke();
    c.save(); c.translate(R, R); c.rotate(-Math.PI / 2 + (i + .5) * a); c.textAlign = 'right'; c.fillStyle = '#fff'; c.font = 'bold 16px "Courier New",monospace'; c.fillText(l, R - 16, 5); c.restore(); });
  let rot = 0, busy = false; const btn = $('#spinBtn'), msg = $('#spinMsg');
  btn.onclick = async () => {
    if (busy) return; busy = true; btn.disabled = true; msg.hidden = true;
    const d = await (await fetch(B + 'api/spin.php', {method: 'POST', headers: {'X-CSRF': CSRF}})).json();
    if (!d.ok) { msg.className = 'alert'; msg.textContent = d.error; msg.hidden = false; busy = false; return; }
    const jitter = (Math.random() - .5) * 40, target = 360 - (d.index * 60 + 30 + jitter);
    rot += 360 * 5 + ((target - rot % 360) + 360) % 360;
    cv.style.transition = 'transform 5s cubic-bezier(.17,.67,.12,1)'; cv.style.transform = `rotate(${rot}deg)`;
    setTimeout(() => { $('#pts').textContent = d.points.toLocaleString(); msg.className = 'notice'; msg.textContent = d.message + ' Find it in My Vouchers.'; msg.hidden = false; busy = false; btn.disabled = d.points < 100; }, 5200);
  };
}
})();
