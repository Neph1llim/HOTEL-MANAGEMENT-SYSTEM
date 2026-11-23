const $ = s => document.querySelector(s), $$ = s => [...document.querySelectorAll(s)];
const peso = n => '₱' + Number(n).toLocaleString('en-PH');
const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const post = async (a, d = {}) => { const f = new FormData(); f.append('a', a); for (const k in d) f.append(k, d[k]); return (await fetch('api.php', { method: 'POST', body: f })).json() };
/* English text is the key; add more pairs here to translate more of the site */
const TL = {
  "Hall": "Hall", "Book the hall": "I-book ang hall", "Staff login": "Pag-log in ng staff", "Our rooms": "Aming mga kwarto", "Photos shown here are placeholders. Add your own in assets/img using the file name shown on each card.": "Mga placeholder lamang ang mga larawan dito. Magdagdag ng sarili ninyo sa assets/img gamit ang pangalan ng file sa bawat card.", "Good for 2 to 3 guests": "Para sa 2 hanggang 3 bisita", "Good for 4 to 5 guests": "Para sa 4 hanggang 5 bisita", "Good for up to 30 guests (maximum 40)": "Para sa hanggang 30 bisita (pinakamarami 40)", "per night": "bawat gabi", "Book this room": "I-book ang kwartong ito", "See room photos": "Tingnan ang mga larawan ng kwarto", "Welcome to Bella Vista": "Maligayang pagdating sa Bella Vista", "Log in": "Mag-log in", "Log in or register": "Mag-log in o magparehistro", "Create an account": "Gumawa ng account", "Password": "Password", "My reservations": "Aking mga reserbasyon", "Account profile": "Profile ng account", "Save changes": "I-save ang pagbabago", "Current password": "Kasalukuyang password", "New password": "Bagong password", "Pay now": "Magbayad na", "No reservations yet.": "Wala pang reserbasyon.", "Cancel reservation": "Kanselahin ang reserbasyon", "Cancel this reservation? Your downpayment will be refunded according to the hotel's policy.": "Kakanselahin ang reserbasyon na ito? Ibabalik ang downpayment ayon sa patakaran ng hotel.", "Cancel this reservation? Check-in is less than 3 days away, so the downpayment is non-refundable.": "Kakanselahin ang reserbasyon na ito? Wala pang 3 araw bago ang check-in, kaya hindi na maibabalik ang downpayment.", "Reservation cancelled. Free cancellation applied: please contact the hotel about your refund.": "Nakansela na ang reserbasyon. Libreng pagkansela: makipag-ugnayan sa hotel tungkol sa refund.", "Reservation cancelled. Per the Terms & Conditions, the downpayment is non-refundable.": "Nakansela na ang reserbasyon. Ayon sa Mga Tuntunin at Kondisyon, hindi na maibabalik ang downpayment.", "Reserve a Room": "Mag-reserba ng kwarto", "My Reservations": "Aking mga Reserbasyon", "My Points & Rewards": "Aking Puntos at Gantimpala", "My Vouchers": "Aking mga Voucher", "Account Profile": "Profile ng Account", "Terms & Conditions": "Mga Tuntunin at Kondisyon", "Log out": "Mag-log out", "Scan the QR code and pay the downpayment, then upload your proof of payment.": "I-scan ang QR code at bayaran ang downpayment, saka i-upload ang patunay ng bayad.", "Payment reference no.": "Reference no. ng bayad", "Amount paid": "Halagang binayad", "Proof of payment (screenshot)": "Patunay ng bayad (screenshot)", "Submit payment": "Isumite ang bayad", "Payment submitted": "Naisumite na ang bayad", "The hotel will verify your proof of payment and confirm your reservation.": "Susuriin ng hotel ang patunay ng bayad at kukumpirmahin ang reserbasyon.", "About us": "Tungkol sa amin", "Rooms": "Mga Kwarto", "Book now": "Mag-book na", "Rewards": "Gantimpala", "Foothills of the mountains, Philippines": "Paanan ng kabundukan, Pilipinas", "Open Monday to Sunday": "Bukas Lunes hanggang Linggo", "Check-in": "Check-in", "Check-out": "Check-out",
  "Rest at the foot of the mountains": "Magpahinga sa paanan ng kabundukan", "A quiet eco-chic retreat for families, travelers and celebrations.": "Tahimik na eco-chic na pahingahan para sa pamilya, manlalakbay at mga selebrasyon.",
  "Bella Vista Hotel & Events Place is a premier eco-chic boutique hotel and events destination at the scenic foothills of the mountains. It offers a tranquil, elegant and exclusive retreat for families, tourists and corporate clients, away from the hustle and bustle of the city.": "Ang Bella Vista Hotel & Events Place ay isang premyadong eco-chic boutique hotel at lugar para sa mga okasyon sa magandang paanan ng kabundukan. Tahimik, elegante at eksklusibong pahingahan ito para sa pamilya, turista at kliyenteng korporasyon, malayo sa ingay ng lungsod.",
  "Our history": "Aming kasaysayan", "Established in 2021, Bella Vista began as an exclusive family-owned resthouse. Growing demand for intimate celebrations and restful getaways turned it into a boutique hotel and events venue, known for warm Filipino hospitality, picturesque views and top-tier service.": "Itinatag noong 2021, nagsimula ang Bella Vista bilang pribadong resthouse ng isang pamilya. Dahil sa dumaraming nais ng payapang bakasyon at maliliit na selebrasyon, naging boutique hotel at events venue ito, na kilala sa magiliw na pagtanggap ng Pilipino, magagandang tanawin at de-kalidad na serbisyo.",
  "Rooms and event hall": "Mga kwarto at event hall", "Standard Rooms": "Standard na Kwarto", "Family Rooms": "Pampamilyang Kwarto", "Event Hall": "Event Hall", "2 to 3 guests · ₱3,500 per night": "2 hanggang 3 bisita · ₱3,500 bawat gabi", "4 to 5 guests · ₱6,000 per night": "4 hanggang 5 bisita · ₱6,000 bawat gabi", "Up to 30 guests · ₱15,000 per night": "Hanggang 30 bisita · ₱15,000 bawat gabi",
  "₱1,000 extra for a 3rd guest": "Dagdag na ₱1,000 para sa ika-3 bisita", "Covers up to 5 guests": "Sakop ang hanggang 5 bisita", "₱500 per guest beyond 30, up to 40": "₱500 bawat bisitang lampas 30, hanggang 40",
  "Gallery": "Galerya", "Book your stay": "I-book ang inyong pananatili", "Guests": "Bilang ng bisita", "Full name": "Buong pangalan", "Email": "Email", "Mobile number": "Numero ng telepono", "Voucher": "Voucher", "None": "Wala", "Check availability": "Tingnan ang availability",
  "night(s)": "gabi", "Room": "Kwarto", "Extra guest fee": "Dagdag na bayad sa bisita", "Discount": "Diskwento", "Total": "Kabuuan", "Downpayment": "Downpayment", "Reserve": "I-reserve", "Invalid dates": "Hindi wastong mga petsa", "Check-in cannot be in the past": "Hindi maaaring nakaraan ang check-in", "Check-out must be at least 1 night after check-in (check-in 2:00 PM, check-out 12:00 NN)": "Dapat hindi bababa sa 1 gabi pagkatapos ng check-in ang check-out (check-in 2:00 PM, check-out 12:00 NN)", "Voucher not valid": "Hindi wasto ang voucher", "Voucher does not apply to this room": "Hindi magagamit ang voucher sa kwartong ito", "Not available for these dates": "Hindi available sa mga petsang ito", "Reservation received": "Natanggap na ang reserbasyon", "Reference": "Reference", "We will confirm after payment.": "Kukumpirmahin namin pagkatapos ng bayad.",
  "Terms and conditions": "Mga tuntunin at kondisyon", "Downpayment: A downpayment secures your reservation. The balance is paid at check-in.": "Downpayment: Kinukumpirma ng downpayment ang inyong reserbasyon. Babayaran ang balanse sa check-in.",
  "Cancellation: Cancellations less than 3 days before check-in are non-refundable.": "Pagkansela: Hindi na maibabalik ang bayad kung kakanselahin nang wala pang 3 araw bago ang check-in.",
  "Damage: Guests are billed for any damage or loss to hotel property, furniture or fixtures.": "Pinsala: Sisingilin ang bisita sa anumang pinsala o pagkawala ng gamit, kasangkapan o fixtures ng hotel.",
  "No smoking: Smoking inside guest rooms is prohibited. Violations are fined ₱2,000.": "Bawal manigarilyo: Bawal ang paninigarilyo sa loob ng mga kwarto. May multang ₱2,000 ang lalabag.",
  "Quiet hours: Keep noise low after 10:00 PM around the accommodation areas.": "Tahimik na oras: Hinaan ang ingay pagkatapos ng 10:00 PM sa paligid ng mga tuluyan.",
  "I agree to the terms and conditions": "Sumasang-ayon ako sa mga tuntunin at kondisyon", "Confirm reservation": "Kumpirmahin ang reserbasyon", "Cancel": "Kanselahin", "Please accept the Terms & Conditions": "Pakitanggap ang mga Tuntunin at Kondisyon",
  "My points and rewards": "Aking puntos at gantimpala", "Show my points": "Ipakita ang puntos ko", "points": "puntos", "You earn 1% of what you spend after each completed stay. Each spin costs 100 points.": "Kumikita ka ng 1% ng ginastos mo pagkatapos ng bawat natapos na pananatili. 100 puntos bawat ikot.", "Spin for 100 points": "Umikot para sa 100 puntos", "Your vouchers": "Mga voucher mo", "What you can win": "Maaari mong mapanalunan", "You won": "Nanalo ka ng",
  "Free room vouchers cover the room rate for up to 2 nights. Extra guest fees still apply.": "Sakop ng libreng voucher ang presyo ng kwarto hanggang 2 gabi. May bayad pa rin ang dagdag na bisita.",
  "2 to 3 guests": "2 hanggang 3 bisita", "4 to 5 guests": "4 hanggang 5 bisita", "Up to 30 guests": "Hanggang 30 bisita", "Notifications": "Mga abiso", "Paid": "Bayad na", "Balance due at check-in": "Balanse na babayaran sa check-in", "Paid in full": "Bayad na lahat", "Balance unpaid": "Hindi pa bayad na balanse", "Availability calendar": "Kalendaryo ng availability", "All rooms (rooms free)": "Lahat ng kwarto (bilang ng libre)", "Free": "Libre", "Booked": "Okupado", "Past": "Lipas na", "Click a free date to set your check-in, then another date for check-out.": "Pindutin ang libreng petsa para sa check-in, saka ang isa pa para sa check-out.", "Please pay the downpayment and upload your proof within 24 hours, or the room will be released.": "Bayaran ang downpayment at i-upload ang patunay sa loob ng 24 oras, o ilalabas ang kwarto.", "Cancelled automatically: no payment received within 24 hours.": "Awtomatikong nakansela: walang natanggap na bayad sa loob ng 24 oras.", "Refund pending: the hotel will contact you.": "Nakabinbin ang refund: makikipag-ugnayan ang hotel.", "Refunded": "Naibalik na ang bayad", "Downpayment kept (non-refundable).": "Hindi na maibabalik ang downpayment.", "Your payment is being verified. You can cancel once it is reviewed.": "Sinusuri pa ang bayad ninyo. Maaari kayong magkansela kapag natapos ang pagsusuri.", "Reservation cancelled.": "Nakansela na ang reserbasyon."
};
let lang = localStorage.lang || 'en';
const t = s => {
  if (lang != 'tl') return s; let m = /^Maximum capacity is (\d+) guests$/.exec(s); if (m) return `Hanggang ${m[1]} bisita lamang`;
  if (m = /^Pay before (.+) or the room will be released\.$/.exec(s)) return `Magbayad bago ang ${m[1]} o ilalabas ang kwarto.`;
  if (m = /^Reservation cancelled\. Your refund of (.+) is now pending; the hotel will contact you\.$/.exec(s)) return `Nakansela na ang reserbasyon. Nakabinbin ang refund na ${m[1]}; makikipag-ugnayan ang hotel.`;
  return TL[s] || s
};
const ymd = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
function applyLang() { document.documentElement.lang = lang; $$('[data-t]').forEach(e => { e.dataset.en ??= e.textContent.trim(); e.textContent = t(e.dataset.en) }); if ($('#lang')) $('#lang').textContent = lang == 'en' ? 'TL' : 'EN' }
function applyTheme(m) { document.documentElement.dataset.theme = m; localStorage.theme = m; $('#theme').innerHTML = m == 'dark' ? '&#9728;' : '&#9790;' }
applyTheme(localStorage.theme || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')); applyLang();
$('#theme').onclick = () => applyTheme(document.documentElement.dataset.theme == 'dark' ? 'light' : 'dark');
if ($('#lang')) $('#lang').onclick = () => { lang = lang == 'en' ? 'tl' : 'en'; localStorage.lang = lang; applyLang() };

/* ---- guest session, drawer, payment form ---- */
let ME = null;
const ready = post('lookup').then(r => {
  ME = r.error ? null : r; if (!$('#dinfo')) return;
  $('#dinfo').innerHTML = ME ? `<b>${esc(ME.guest.name)}</b><small>${esc(ME.guest.email)}</small>` : `<a href="login.php">${t('Log in or register')}</a>`;
  $('#dlogout').hidden = !ME; $$('.auth').forEach(x => x.hidden = !ME);
  if (ME && ME.unread) { $('#nbadge').textContent = ME.unread; $('#nbadge').hidden = false }
});
if ($('#menu')) { const d = $('#drawer'); $('#menu').onclick = () => d.classList.toggle('open'); $('#dov').onclick = () => d.classList.remove('open'); $$('#drawer a').forEach(x => x.addEventListener('click', () => d.classList.remove('open'))); $('#dlogout').onclick = async () => { await post('glogout'); location = 'index.php' } }
const payForm = (ref, down) => `<img class="qr" src="assets/img/payment-qr.png" alt="Add the hotel payment QR at assets/img/payment-qr.png"><p class="mu" data-t>Scan the QR code and pay the downpayment, then upload your proof of payment.</p>
 <form class="pf form" data-ref="${ref}"><label><span data-t>Payment reference no.</span><input name="ref_no" required></label><label><span data-t>Amount paid</span><input type="number" name="amount" step="0.01" value="${down}" required></label><label><span data-t>Proof of payment (screenshot)</span><input type="file" name="proof" accept="image/*" required></label><button class="btn" data-t>Submit payment</button></form><p class="err"></p>`;
document.addEventListener('submit', async e => {
  const f = e.target; if (!f.classList.contains('pf')) return; e.preventDefault();
  const d = new FormData(f); d.append('a', 'pay'); d.append('ref', f.dataset.ref); const r = await (await fetch('api.php', { method: 'POST', body: d })).json();
  if (r.error) { f.nextElementSibling.textContent = r.error; return }
  f.closest('.pay').innerHTML = `<h3 data-t>Payment submitted</h3><p class="mu" data-t>The hotel will verify your proof of payment and confirm your reservation.</p>`; applyLang()
});
if ($('#lf')) {
  const go = async f => { const r = await post(f.id == 'lf' ? 'glogin' : 'register', Object.fromEntries(new FormData(f))); if (r.error) $('#lerr').textContent = r.error; else location = 'reserve.php' };
  $('#lf').onsubmit = e => { e.preventDefault(); go(e.target) }; $('#rg').onsubmit = e => { e.preventDefault(); go(e.target) }
}
if ($('#acf')) {
  const f = $('#acf'); ready.then(() => { if (!ME) return location = 'login.php'; f.name.value = ME.guest.name; f.phone.value = ME.guest.phone });
  f.onsubmit = async e => { e.preventDefault(); const r = await post('profile', Object.fromEntries(new FormData(f))); $('#amsg').textContent = r.error || 'Saved'; $('#amsg').className = r.error ? 'err' : 'mu' }
}
if ($('#bklist')) {
  const money = (k, v) => `<span data-t>${k}</span> ${peso(v)}`;
  const stateLine = x => {
    if (x.status == 'pending' && x.expires) return `<p class="warn">${esc(t('Pay before ' + x.expires + ' or the room will be released.'))}</p>`;
    if (x.status == 'approved') return `<p>${money('Paid', x.paid)} · <b>${+x.balance > 0 ? `<span data-t>Balance due at check-in</span> ${peso(x.balance)}` : `<span data-t>Paid in full</span>`}</b></p>`;
    if (x.status == 'completed') return `<p>${money('Paid', x.paid)}${+x.balance > 0 ? ` · <b class="err"><span data-t>Balance unpaid</span> ${peso(x.balance)}</b>` : ` · <span data-t>Paid in full</span>`}</p>`;
    if (x.status == 'cancelled') {
      let h = x.cancel_reason == 'expired' ? '<p class="mu" data-t>Cancelled automatically: no payment received within 24 hours.</p>' : '';
      if (x.refund_status == 'pending') h += `<p class="warn">${peso(x.refund_amount)} · <span data-t>Refund pending: the hotel will contact you.</span></p>`;
      else if (x.refund_status == 'refunded') h += `<p class="ok"><span data-t>Refunded</span> ${peso(x.refund_amount)}</p>`;
      else if (+x.paid > 0) h += '<p class="mu" data-t>Downpayment kept (non-refundable).</p>';
      return h
    }
    return ''
  };
  const bkCard = x => `<article class="card pay"><h3>${esc(x.room)} · ${x.ref}</h3><p>${x.checkin} → ${x.checkout} · ${x.pax} <span data-t>Guests</span></p><p><span data-t>Total</span> ${peso(x.total)} · <span data-t>Downpayment</span> ${peso(x.downpayment)}</p><p><span class="s ${x.status}">${x.status}</span> ${x.pay ? `<span class="s ${x.pay == 'verified' ? 'approved' : x.pay == 'rejected' ? 'cancelled' : 'pending'}">payment ${x.pay}</span>` : ''}</p>${stateLine(x)}${x.status == 'pending' && x.pay != 'pending' ? `<details><summary data-t>Pay now</summary>${payForm(x.ref, x.downpayment)}</details>` : ''}${x.can_cancel ? `<p><button class="btn sm ghost" data-cancel="${x.ref}" data-refund="${x.refundable ? 1 : 0}" data-t>Cancel reservation</button></p>` : (['pending', 'approved'].includes(x.status) && x.pay == 'pending' ? '<p class="mu"><small data-t>Your payment is being verified. You can cancel once it is reviewed.</small></p>' : '')}</article>`;
  const ago = s => { const d = new Date(s.replace(' ', 'T')), m = Math.round((Date.now() - d) / 60000); return m < 1 ? 'just now' : m < 60 ? m + ' min ago' : m < 1440 ? Math.floor(m / 60) + ' h ago' : d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric' }) };
  const loadNotes = async () => {
    const r = await post('notes'); if (r.error || !r.notes.length) { $('#notes').innerHTML = ''; return }
    $('#notes').innerHTML = `<h2 data-t>Notifications</h2><ul class="list notes">` + r.notes.map(n => `<li class="${+n.is_read ? '' : 'unread'}"><span>${esc(n.msg)}</span><small class="mu">${ago(n.created_at)}</small></li>`).join('') + '</ul>'; applyLang();
    if (r.notes.some(n => !+n.is_read)) { post('notes_read'); if ($('#nbadge')) $('#nbadge').hidden = true }
  };
  const loadBk = () => post('mybookings').then(r => {
    if (r.error) return location = 'login.php';
    $('#bklist').innerHTML = r.res.map(bkCard).join('') || `<p class="mu" data-t>No reservations yet.</p>`; applyLang()
  });
  loadNotes(); loadBk();
  $('#bklist').addEventListener('click', async e => {
    const b = e.target.closest('[data-cancel]'); if (!b) return;
    if (!confirm(t(b.dataset.refund == '1' ? "Cancel this reservation? Your downpayment will be refunded according to the hotel's policy." : 'Cancel this reservation? Check-in is less than 3 days away, so the downpayment is non-refundable.'))) return;
    b.disabled = true; const r = await post('cancel_booking', { ref: b.dataset.cancel });
    if (r.error) { b.disabled = false; alert(r.error); return }
    await Promise.all([loadBk(), loadNotes()]); alert(t(r.refund > 0 ? `Reservation cancelled. Your refund of ${peso(r.refund)} is now pending; the hotel will contact you.` : r.refundable ? 'Reservation cancelled.' : 'Reservation cancelled. Per the Terms & Conditions, the downpayment is non-refundable.'))
  });
}

/* ---- booking ---- */
const bf = $('#bf');
if (bf) {
  const fd = () => Object.fromEntries(new FormData(bf)), box = $('#results'); let sel = null;
  bf.ci.min = bf.co.min = ymd(new Date());
  bf.ci.onchange = () => { bf.co.min = bf.ci.value };
  ready.then(() => { if (ME) ME.vouchers.forEach(v => bf.voucher.add(new Option(v.prize, v.id))) });
  const slug = n => n.toLowerCase().replace(/[^a-z0-9]+/g, '-');
  bf.onsubmit = async e => {
    e.preventDefault(); const r = await post('search', fd());
    if (r.error) { box.innerHTML = `<p class="err" data-t>${esc(r.error)}</p>`; applyLang(); return }
    box.innerHTML = r.rooms.map(q => {
      const ok = !q.error && q.free, n = esc(q.room.name); return `<article class="card"><div class="rimg"><span>${n}</span><img src="assets/img/${slug(q.room.name)}.jpg" alt="${n}" onerror="this.remove()"></div><h3>${n}</h3>` + (ok ?
        `<p>${q.nights} <span data-t>night(s)</span> · <span data-t>${q.room.type == 'hall' ? 'Hall' : 'Room'}</span> ${peso(q.sub)}</p><p><span data-t>Extra guest fee</span> ${peso(q.extra)}</p><p><span data-t>Discount</span> −${peso(q.disc)}</p><p><b><span data-t>Total</span> ${peso(q.total)}</b></p><p class="mu"><span data-t>Downpayment</span> ${peso(q.down)}</p><button class="btn" data-room="${q.room.id}" data-t>Reserve</button>`
        : `<p class="err" data-t>${esc(q.error || 'Not available for these dates')}</p>`) + '</article>'
    }).join(''); applyLang()
  };
  box.onclick = e => { const b = e.target.closest('[data-room]'); if (!b) return; if (!ME) { location = 'login.php'; return } sel = b.dataset.room; $('#agree').checked = false; $('#tc').showModal() };
  $('#cancelTc').onclick = () => $('#tc').close();
  $('#confirm').onclick = async () => {
    if (!$('#agree').checked) return alert(t('Please accept the Terms & Conditions'));
    const r = await post('book', { ...fd(), room: sel, agree: 1 }); $('#tc').close();
    if (r.error) { box.innerHTML = `<p class="err">${esc(r.error)}</p>`; return }
    box.innerHTML = `<div class="card pay"><h3 data-t>Reservation received</h3><p><span data-t>Reference</span>: <b>${r.ref}</b></p><p><span data-t>Total</span> ${peso(r.total)} · <b><span data-t>Downpayment</span> ${peso(r.down)}</b></p><p class="warn" data-t>Please pay the downpayment and upload your proof within 24 hours, or the room will be released.</p>${payForm(r.ref, r.down)}</div>`; applyLang(); loadCal()
  };
  const qs = new URLSearchParams(location.search);['ci', 'co', 'pax'].forEach(k => { if (qs.get(k)) bf[k].value = qs.get(k) }); if (qs.get('ci') && qs.get('co')) bf.requestSubmit();

  /* ---- availability calendar: each night is free or booked, for one room or for all rooms ---- */
  let calMonth = ymd(new Date()).slice(0, 7), calData = null, calRoom = '';
  const MON = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  const drawCal = () => {
    if (!calData) return; const [Y, M] = calMonth.split('-').map(Number), first = new Date(Y, M - 1, 1), days = new Date(Y, M, 0).getDate(), rooms = calData.rooms.filter(r => !calRoom || r.id == calRoom);
    $('#cmon').textContent = `${MON[M - 1]} ${Y}`;
    const sel = $('#croom'); if (sel.options.length < 2) { calData.rooms.forEach(r => sel.add(new Option(r.name, r.id))); sel.options[0].textContent = t('All rooms (rooms free)') }
    let h = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map(d => `<b class="dow">${d}</b>`).join('') + '<i></i>'.repeat(first.getDay());
    for (let d = 1; d <= days; d++) {
      const day = `${calMonth}-${String(d).padStart(2, '0')}`, past = day < calData.today;
      const taken = new Set(calData.bookings.filter(b => b.checkin <= day && b.checkout > day).map(b => +b.room_id)), free = rooms.filter(r => !taken.has(+r.id)).length;
      const st = past ? 'past' : free == 0 ? 'full' : free < rooms.length ? 'part' : 'open', lbl = past ? '' : !calRoom ? `${free}/${rooms.length}` : free ? t('Free') : t('Booked');
      h += `<button type="button" class="day ${st}${day == bf.ci.value || day == bf.co.value ? ' pick' : ''}" data-day="${day}"${past ? ' disabled' : ''}><span>${d}</span><small>${lbl}</small></button>`
    }
    $('#cal').innerHTML = h
  };
  const loadCal = async () => { calData = await post('calendar', { month: calMonth }); drawCal() };
  $('#cprev').onclick = () => { const [y, m] = calMonth.split('-').map(Number); calMonth = ymd(new Date(y, m - 2, 1)).slice(0, 7); loadCal() };
  $('#cnext').onclick = () => { const [y, m] = calMonth.split('-').map(Number); calMonth = ymd(new Date(y, m, 1)).slice(0, 7); loadCal() };
  $('#croom').onchange = e => { calRoom = e.target.value; drawCal() };
  $('#cal').onclick = e => {
    const b = e.target.closest('[data-day]'); if (!b) return; const d = b.dataset.day;
    if (!bf.ci.value || bf.co.value || d <= bf.ci.value) { bf.ci.value = d; bf.co.value = '' } else bf.co.value = d; bf.co.min = bf.ci.value; drawCal();
    if (bf.ci.value && bf.co.value) bf.scrollIntoView({ behavior: 'smooth', block: 'center' })
  };
  [bf.ci, bf.co].forEach(i => i.addEventListener('change', drawCal));
  $('#lang').addEventListener('click', drawCal); loadCal();
}
const qb = $('#qb');
if (qb) { qb.ci.min = qb.co.min = ymd(new Date()); qb.ci.onchange = () => { qb.co.min = qb.ci.value } }

/* ---- rewards wheel (the server picks the prize; this only animates it) ---- */
if ($('#wheel')) {
  const PR = ['20% off', '50% off', '75% + meals', 'Free Standard', 'Free Family', 'VIP'], COL = ['#0B5D4B', '#2E8B74', '#0B5D4B', '#2E8B74', '#0B5D4B', '#C8A96A'], N = 6, A = 2 * Math.PI / N; let rot = 0;
  const draw = () => {
    const c = $('#wheel').getContext('2d'); c.clearRect(0, 0, 360, 360); c.save(); c.translate(180, 180); c.rotate(rot);
    PR.forEach((p, i) => {
      const m = i * A - Math.PI / 2; c.beginPath(); c.moveTo(0, 0); c.arc(0, 0, 170, m - A / 2, m + A / 2); c.fillStyle = COL[i]; c.fill(); c.strokeStyle = '#F7F5EF'; c.lineWidth = 2; c.stroke();
      c.save(); c.translate(Math.cos(m) * 105, Math.sin(m) * 105); c.rotate(-rot); c.fillStyle = i == 5 ? '#12231D' : '#fff'; c.font = '600 13px Inter,sans-serif'; c.textAlign = 'center'; c.fillText(p, 0, 4); c.restore()
    }); c.restore()
  };
  draw();
  const show = r => { $('#pts').textContent = r.guest.points; $('#spin').disabled = r.guest.points < 100; $('#vl').innerHTML = r.vouchers.map(v => `<li>${esc(v.prize)}</li>`).join('') || `<li class="mu">—</li>` + (r.guest.vip ? `<li>VIP</li>` : '') };
  const load = async () => { const r = await post('lookup'); $('#rmsg').innerHTML = r.error ? `<a href="login.php">${t('Log in or register')}</a>` : ''; if (!r.error) show(r); return r };
  load();
  $('#spin').onclick = async () => {
    $('#spin').disabled = true; $('#win').textContent = ''; const r = await post('spin');
    if (r.error) { $('#rmsg').textContent = r.error; return }
    const start = rot, end = start + 12 * Math.PI + ((-r.index * A - start) % (2 * Math.PI) + 2 * Math.PI) % (2 * Math.PI), t0 = performance.now();
    const step = n => {
      const k = Math.min((n - t0) / 4500, 1); rot = start + (end - start) * (1 - Math.pow(1 - k, 3)); draw();
      if (k < 1) requestAnimationFrame(step); else { $('#win').textContent = t('You won') + ' ' + r.prize; load() }
    }; requestAnimationFrame(step)
  };
}
