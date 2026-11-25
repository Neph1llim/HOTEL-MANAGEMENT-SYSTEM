const $ = s => document.querySelector(s), $$ = s => [...document.querySelectorAll(s)];
const peso = n => '₱' + Number(n).toLocaleString('en-PH');
const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const post = async (a, d = {}) => { const f = new FormData(); f.append('a', a); for (const k in d) f.append(k, d[k]); return (await fetch('api.php', { method: 'POST', body: f })).json() };
/* English text is the key; add more pairs here to translate more of the site */
const TL = {
  "Hall": "Hall", "Make a reservation": "Gumawa ng reserbasyon", "Policies": "Mga patakaran", "Home": "Home", "About Bella Vista": "Tungkol sa Bella Vista", "See our rooms": "Tingnan ang aming mga kwarto", "Book your stay": "Mag-book ng inyong pananatili", "A quiet retreat at the foot of the mountains": "Tahimik na pahingahan sa paanan ng kabundukan", "From family resthouse to boutique hotel": "Mula resthouse ng pamilya hanggang boutique hotel", "Please read these terms before you reserve. By booking with Bella Vista you agree to them.": "Basahin ang mga tuntuning ito bago magpareserba. Sa pag-book sa Bella Vista, sumasang-ayon kayo sa mga ito.", "Check-in and check-out": "Check-in at check-out", "Downpayment": "Downpayment", "Cancellation": "Pagkansela", "Damage": "Pinsala", "No smoking": "Bawal manigarilyo", "Quiet hours": "Tahimik na oras", "A downpayment secures your reservation. The balance is paid at check-in.": "Kinukumpirma ng downpayment ang inyong reserbasyon. Babayaran ang balanse sa check-in.", "Cancellations less than 3 days before check-in are non-refundable.": "Hindi na maibabalik ang bayad kung kakanselahin nang wala pang 3 araw bago ang check-in.", "Guests are billed for any damage or loss to hotel property, furniture or fixtures.": "Sisingilin ang bisita sa anumang pinsala o pagkawala ng gamit, kasangkapan o fixtures ng hotel.", "Smoking inside guest rooms is prohibited. Violations are fined ₱2,000.": "Bawal ang paninigarilyo sa loob ng mga kwarto. May multang ₱2,000 ang lalabag.", "Keep noise low after 10:00 PM around the accommodation areas.": "Hinaan ang ingay pagkatapos ng 10:00 PM sa paligid ng mga tuluyan.", "Downpayment, cancellation and house rules are in our": "Nasa aming mga patakaran ang downpayment, pagkansela at house rules:", "My profile.": "Aking profile.", "Customer profile": "Profile ng customer", "Email address": "Email address", "Phone number": "Numero ng telepono", "Your email is your login, so it can’t be changed here.": "Ang email ninyo ang inyong login, kaya hindi ito mapapalitan dito.", "Change password (optional)": "Palitan ang password (opsyonal)", "Save profile changes": "I-save ang pagbabago sa profile", "Profile saved": "Na-save ang profile", "Let’s plan your Bella Vista experience.": "Planuhin natin ang inyong karanasan sa Bella Vista.", "Secure your reservation request.": "Siguraduhin ang inyong kahilingan sa reserbasyon.", "1 Reservation": "1 Reserbasyon", "2 Payment": "2 Bayad", "Reservation information": "Impormasyon ng reserbasyon", "Accommodation / Room": "Tuluyan / Kwarto", "Number of guests": "Bilang ng bisita", "Continue to payment": "Magpatuloy sa bayad", "Your reservation summary": "Buod ng inyong reserbasyon", "Additional guests": "Dagdag na bisita", "Estimated total": "Tinatayang kabuuan", "50% downpayment": "50% downpayment", "Balance due upon check-in": "Balanse na babayaran sa check-in", "I agree to the": "Sumasang-ayon ako sa", "terms and conditions": "mga tuntunin at kondisyon", "I agree": "Sumasang-ayon ako", "Close": "Isara", "Submit your 50% downpayment proof": "Isumite ang patunay ng 50% downpayment", "Reference number / GCash transaction reference": "Reference number / GCash transaction reference", "Proof of payment (PNG or JPG/JPEG, max 3 MB)": "Patunay ng bayad (PNG o JPG/JPEG, hanggang 3 MB)", "Only PNG and JPG/JPEG images are allowed": "PNG at JPG/JPEG lamang ang pinapayagan", "Upload a PNG or JPG/JPEG image under 3 MB": "Mag-upload ng PNG o JPG/JPEG na wala pang 3 MB", "Enter the payment reference number and amount paid": "Ilagay ang reference number at halagang binayad", "View my reservations": "Tingnan ang aking mga reserbasyon", "Have a voucher? (optional)": "May voucher? (opsyonal)", "← Back to Check availability": "← Bumalik sa Check availability", "Your Reservation Summary": "Buod ng Inyong Reserbasyon", "Your vouchers": "Mga voucher ninyo", "Voucher code": "Code ng voucher", "Voucher applied": "Nagamit ang voucher", "Unavailable": "Hindi available", "Book / Select Room": "I-book / Piliin ang Kwarto", "No rooms are available for these dates. Please try other dates.": "Walang available na kwarto sa mga petsang ito. Subukan ang ibang petsa.", "Results update automatically as you change the dates, guests or voucher. Fully booked rooms are hidden.": "Awtomatikong nag-a-update ang resulta kapag binago ang petsa, bisita o voucher. Nakatago ang mga kwartong puno na.", "Press and hold on your check-in date, then drag to your check-out date and release.": "Pindutin at hawakan ang petsa ng check-in, i-drag sa petsa ng check-out, saka bitawan.", "Book the hall": "I-book ang hall", "Staff login": "Pag-log in ng staff", "Our rooms": "Aming mga kwarto", "Photos shown here are placeholders. Add your own in assets/img using the file name shown on each card.": "Mga placeholder lamang ang mga larawan dito. Magdagdag ng sarili ninyo sa assets/img gamit ang pangalan ng file sa bawat card.", "Good for 2 to 3 guests": "Para sa 2 hanggang 3 bisita", "Good for 4 to 5 guests": "Para sa 4 hanggang 5 bisita", "Good for up to 30 guests (maximum 40)": "Para sa hanggang 30 bisita (pinakamarami 40)", "per night": "bawat gabi", "Book this room": "I-book ang kwartong ito", "See room photos": "Tingnan ang mga larawan ng kwarto", "Welcome to Bella Vista": "Maligayang pagdating sa Bella Vista", "Log in": "Mag-log in", "Log in or register": "Mag-log in o magparehistro", "Create an account": "Gumawa ng account", "Password": "Password", "My reservations": "Aking mga reserbasyon", "Account profile": "Profile ng account", "Save changes": "I-save ang pagbabago", "Current password": "Kasalukuyang password", "New password": "Bagong password", "Pay now": "Magbayad na", "No reservations yet.": "Wala pang reserbasyon.", "Cancel reservation": "Kanselahin ang reserbasyon", "Cancel this reservation? Your downpayment will be refunded according to the hotel's policy.": "Kakanselahin ang reserbasyon na ito? Ibabalik ang downpayment ayon sa patakaran ng hotel.", "Cancel this reservation? Check-in is less than 3 days away, so the downpayment is non-refundable.": "Kakanselahin ang reserbasyon na ito? Wala pang 3 araw bago ang check-in, kaya hindi na maibabalik ang downpayment.", "Reservation cancelled. Free cancellation applied: please contact the hotel about your refund.": "Nakansela na ang reserbasyon. Libreng pagkansela: makipag-ugnayan sa hotel tungkol sa refund.", "Reservation cancelled. Per the Terms & Conditions, the downpayment is non-refundable.": "Nakansela na ang reserbasyon. Ayon sa Mga Tuntunin at Kondisyon, hindi na maibabalik ang downpayment.", "Reserve a Room": "Mag-reserba ng kwarto", "My Reservations": "Aking mga Reserbasyon", "My Points & Rewards": "Aking Puntos at Gantimpala", "My Vouchers": "Aking mga Voucher", "Account Profile": "Profile ng Account", "Terms & Conditions": "Mga Tuntunin at Kondisyon", "Log out": "Mag-log out", "Scan the QR code and pay the downpayment, then upload your proof of payment.": "I-scan ang QR code at bayaran ang downpayment, saka i-upload ang patunay ng bayad.", "Payment reference no.": "Reference no. ng bayad", "Amount paid": "Halagang binayad", "Proof of payment (screenshot)": "Patunay ng bayad (screenshot)", "Submit payment": "Isumite ang bayad", "Payment submitted": "Naisumite na ang bayad", "The hotel will verify your proof of payment and confirm your reservation.": "Susuriin ng hotel ang patunay ng bayad at kukumpirmahin ang reserbasyon.", "About us": "Tungkol sa amin", "Rooms": "Mga Kwarto", "Book now": "Mag-book na", "Rewards": "Gantimpala", "Foothills of the mountains, Philippines": "Paanan ng kabundukan, Pilipinas", "Open Monday to Sunday": "Bukas Lunes hanggang Linggo", "Check-in": "Check-in", "Check-out": "Check-out",
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
  "Free room vouchers cover the room rate for the room and the number of nights named in the reward. Extra guest fees still apply.": "Sakop ng libreng voucher ang presyo ng kwarto para sa kwarto at bilang ng gabing nakasaad sa pangalan ng gantimpala. May bayad pa rin ang dagdag na bisita.",
  "2 to 3 guests": "2 hanggang 3 bisita", "4 to 5 guests": "4 hanggang 5 bisita", "Up to 30 guests": "Hanggang 30 bisita", "Notifications": "Mga abiso", "Paid": "Bayad na", "Balance due at check-in": "Balanse na babayaran sa check-in", "Paid in full": "Bayad na lahat", "Balance unpaid": "Hindi pa bayad na balanse", "Availability calendar": "Kalendaryo ng availability", "All rooms (rooms free)": "Lahat ng kwarto (bilang ng libre)", "Free": "Libre", "Booked": "Okupado", "Past": "Lipas na", "Click a free date to set your check-in, then another date for check-out.": "Pindutin ang libreng petsa para sa check-in, saka ang isa pa para sa check-out.", "Please pay the downpayment and upload your proof within 24 hours, or the room will be released.": "Bayaran ang downpayment at i-upload ang patunay sa loob ng 24 oras, o ilalabas ang kwarto.", "Cancelled automatically: no payment received within 24 hours.": "Awtomatikong nakansela: walang natanggap na bayad sa loob ng 24 oras.", "Refund pending: the hotel will contact you.": "Nakabinbin ang refund: makikipag-ugnayan ang hotel.", "Refunded": "Naibalik na ang bayad", "Downpayment kept (non-refundable).": "Hindi na maibabalik ang downpayment.", "Your payment is being verified. You can cancel once it is reviewed.": "Sinusuri pa ang bayad ninyo. Maaari kayong magkansela kapag natapos ang pagsusuri.", "Reservation cancelled.": "Nakansela na ang reserbasyon."
};
let lang = localStorage.lang || 'en';
const t = s => {
  if (lang != 'tl') return s; let m = /^Maximum capacity is (\d+) guests$/.exec(s); if (m) return `Hanggang ${m[1]} bisita lamang`;
  if (m = /^You earn ([\d.]+)% of what you spend after each completed stay\. Each spin costs (\d+) points\.$/.exec(s)) return `Kumikita ka ng ${m[1]}% ng ginastos mo pagkatapos ng bawat natapos na pananatili. ${m[2]} puntos bawat ikot.`;
  if (m = /^Pay before (.+) or the room will be released\.$/.exec(s)) return `Magbayad bago ang ${m[1]} o ilalabas ang kwarto.`;
  if (m = /^Reservation cancelled\. Your refund of (.+) is now pending; the hotel will contact you\.$/.exec(s)) return `Nakansela na ang reserbasyon. Nakabinbin ang refund na ${m[1]}; makikipag-ugnayan ang hotel.`;
  return TL[s] || s
};
const ymd = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
function applyLang() { document.documentElement.lang = lang; $$('[data-t]').forEach(e => { e.dataset.en ??= e.textContent.trim(); e.textContent = t(e.dataset.en) }); if ($('#lang')) $('#lang').textContent = lang == 'en' ? 'TL' : 'EN' }
function applyTheme(m) { document.documentElement.dataset.theme = m; localStorage.theme = m; const b = $('#theme'); if (b.classList.contains('tgl')) b.setAttribute('aria-checked', m == 'dark'); else b.innerHTML = m == 'dark' ? '&#9728;' : '&#9790;' }
applyTheme(localStorage.theme || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')); applyLang();
$('#theme').onclick = () => { const r = document.documentElement; r.classList.add('tswap'); applyTheme(r.dataset.theme == 'dark' ? 'light' : 'dark'); setTimeout(() => r.classList.remove('tswap'), 600) };
if ($('#lang')) $('#lang').onclick = () => { lang = lang == 'en' ? 'tl' : 'en'; localStorage.lang = lang; applyLang() };

/* ---- guest session, drawer, payment form ---- */
let ME = null;
const ready = post('lookup').then(r => {
  ME = r.error ? null : r; if (!$('#dinfo')) return;
  $('#dinfo').innerHTML = ME ? `<b>${esc(ME.guest.name)}${+ME.guest.vip ? ' <span class="vipbadge">VIP</span>' : ''}</b><small>${esc(ME.guest.email)}</small>` : `<a href="${loginUrl()}">${t('Log in or register')}</a>`;
  $('#dlogout').hidden = !ME; $$('.auth').forEach(x => x.hidden = !ME);
  if (ME && ME.unread) { $('#nbadge').textContent = ME.unread; $('#nbadge').hidden = false }
});
if ($('#menu')) { const d = $('#drawer'); $('#menu').onclick = () => d.classList.toggle('open'); $('#dov').onclick = () => d.classList.remove('open'); $$('#drawer a').forEach(x => x.addEventListener('click', () => d.classList.remove('open'))); $('#dlogout').onclick = async () => { await post('glogout'); location = 'index.php' } }
/* proof of payment: PNG or JPG/JPEG only, up to 3 MB (the server checks again) */
const proofErr = f => !f ? 'Upload a PNG or JPG/JPEG image under 3 MB' : (!/\.(png|jpe?g)$/i.test(f.name) || !['image/png', 'image/jpeg'].includes(f.type)) ? 'Only PNG and JPG/JPEG images are allowed' : f.size > 3000000 ? 'Upload a PNG or JPG/JPEG image under 3 MB' : '';
document.addEventListener('change', e => {
  const i = e.target; if (i.name != 'proof' || !i.closest('.pf')) return; const m = proofErr(i.files[0]), out = i.closest('.pf').nextElementSibling;
  if (m && i.files[0]) { i.value = ''; out.textContent = t(m) } else out.textContent = ''
});
document.addEventListener('submit', async e => {
  const f = e.target; if (!f.classList.contains('pf')) return; e.preventDefault(); const out = f.nextElementSibling;
  if (!f.ref_no.value.trim() || !(+f.amount.value > 0)) { out.textContent = t('Enter the payment reference number and amount paid'); return }
  const m = proofErr(f.proof.files[0]); if (m) { out.textContent = t(m); return }
  const d = new FormData(f); d.append('a', 'pay'); d.append('ref', f.dataset.ref); const r = await (await fetch('api.php', { method: 'POST', body: d })).json();
  if (r.error) { out.textContent = r.error; return }
  f.closest('.pay').innerHTML = `<div class="callout ok"><b data-t>Payment submitted</b><br><span data-t>The hotel will verify your proof of payment and confirm your reservation.</span></div><p><a class="btn" href="reservations.php" data-t>View my reservations</a></p>`; applyLang(); scrollTo({ top: 0, behavior: 'smooth' })
});
if ($('#lf')) {
  const go = async f => { const r = await post(f.id == 'lf' ? 'glogin' : 'register', Object.fromEntries(new FormData(f))); if (r.error) $('#lerr').textContent = r.error; else { location = backTarget() } };
  $('#lf').onsubmit = e => { e.preventDefault(); go(e.target) }; $('#rg').onsubmit = e => { e.preventDefault(); go(e.target) }
}
if ($('#acf')) {
  const f = $('#acf'); ready.then(() => { if (!ME) return location = loginUrl(); f.name.value = ME.guest.name; f.email.value = ME.guest.email || ''; f.phone.value = ME.guest.phone });
  f.onsubmit = async e => {
    e.preventDefault(); const d = Object.fromEntries(new FormData(f)); delete d.email; $('#amsg').className = 'full mu';
    if (!d.name.trim() || d.phone.trim().length < 7) { $('#amsg').className = 'full err'; $('#amsg').textContent = t('Enter a valid name and mobile number'); return }
    const r = await post('profile', d);
    $('#amsg').className = 'full ' + (r.error ? 'err' : 'ok'); $('#amsg').textContent = r.error || t('Profile saved');
    if (!r.error) { f.cur_pass.value = f.new_pass.value = '' }
  }
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
  const bkCard = x => `<article class="card pay"><h3>${esc(x.room)} · ${x.ref}</h3><p>${x.checkin} → ${x.checkout} · ${x.pax} <span data-t>Guests</span></p><p><span data-t>Total</span> ${peso(x.total)} · <span data-t>Downpayment</span> ${peso(x.downpayment)}</p><p><span class="s ${x.status}">${x.status}</span> ${x.pay ? `<span class="s ${x.pay == 'verified' ? 'approved' : x.pay == 'rejected' ? 'cancelled' : 'pending'}">payment ${x.pay}</span>` : ''}</p>${stateLine(x)}${x.status == 'pending' && x.pay != 'pending' ? `<p><a class="btn sm" href="payment.php?ref=${x.ref}" data-t>Pay now</a></p>` : ''}${x.can_cancel ? `<p><button class="btn sm ghost" data-cancel="${x.ref}" data-refund="${x.refundable ? 1 : 0}" data-t>Cancel reservation</button></p>` : (['pending', 'approved'].includes(x.status) && x.pay == 'pending' ? '<p class="mu"><small data-t>Your payment is being verified. You can cancel once it is reviewed.</small></p>' : '')}</article>`;
  const ago = s => { const d = new Date(s.replace(' ', 'T')), m = Math.round((Date.now() - d) / 60000); return m < 1 ? 'just now' : m < 60 ? m + ' min ago' : m < 1440 ? Math.floor(m / 60) + ' h ago' : d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric' }) };
  const loadNotes = async () => {
    const r = await post('notes'); if (r.error || !r.notes.length) { $('#notes').innerHTML = ''; return }
    $('#notes').innerHTML = `<h2 data-t>Notifications</h2><ul class="list notes">` + r.notes.map(n => `<li class="${+n.is_read ? '' : 'unread'}"><span>${esc(n.msg)}</span><small class="mu">${ago(n.created_at)}</small></li>`).join('') + '</ul>'; applyLang();
    if (r.notes.some(n => !+n.is_read)) { post('notes_read'); if ($('#nbadge')) $('#nbadge').hidden = true }
  };
  const loadBk = () => post('mybookings').then(r => {
    if (r.error) return location = loginUrl();
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

/* ---- Room image mapping shared by the booking and checkout summary screens ---- */
const roomImageFile = name => {
  const key = String(name || '').trim().toLowerCase();
  const images = {
    'room a': 'room-a.png', 'room b': 'room-bbb.png', 'room c': 'room-a.png',
    'fr1': 'fr1.png', 'fr2': 'fr2-copy.png', 'fr3': 'fr3.png',
    'event hall': 'event-hall.png'
  };
  return images[key] || '';
};
const roomImagePath = (room, type = '') => {
  const file = roomImageFile(room) || (String(type).toLowerCase() === 'hall' ? 'event-hall.png' : 'room-a.png');
  return 'assets/img/' + file;
};


/* ---- check availability: live results, full rooms hidden, drag-to-select calendar ---- */
const bf = $('#bf');
if (bf) {
  const fd = () => Object.fromEntries(new FormData(bf)), box = $('#results');
  bf.ci.min = bf.co.min = ymd(new Date());
  const addDays = (d, n) => { const x = new Date(d + 'T00:00:00'); x.setDate(x.getDate() + n); return ymd(x) };
  let calMonth = ymd(new Date()).slice(0, 7), calData = null, calRoom = '', freeIds = null, seq = 0;
  ready.then(() => { if (ME) ME.vouchers.filter(v => v.kind != 'free_food').forEach(v => bf.voucher.add(new Option(`${v.prize} (${v.code})`, v.id))); search() });
  const link = (id, q) => { const u = new URLSearchParams({ room: id, ci: bf.ci.value, co: bf.co.value, pax: bf.pax.value }); if (bf.voucher.value) u.set('voucher', bf.voucher.value); return 'make-reservation.php?' + u };
  const syncRoomList = () => {
    const sel = $('#croom'); if (!calData) return; const keep = calRoom;
    sel.length = 1; sel.options[0].textContent = t('All rooms (rooms free)');
    calData.rooms.forEach(r => sel.add(new Option(r.name, r.id)));
    if (keep && ![...sel.options].some(o => o.value == keep)) calRoom = ''; sel.value = calRoom
  };
  async function search() {
    const my = ++seq;
    if (!bf.ci.value || !bf.co.value || bf.co.value <= bf.ci.value || !bf.pax.value) { freeIds = null; box.innerHTML = ''; syncRoomList(); drawCal(); return }
    const r = await post('search', fd()); if (my != seq) return;
    if (r.error) { freeIds = null; box.innerHTML = `<p class="err" data-t>${esc(r.error)}</p>`; applyLang(); syncRoomList(); drawCal(); return }
    const open = r.rooms.filter(q => q.free && (!calRoom || q.room.id == calRoom));   /* full rooms hidden; the Room dropdown picks which room(s) show */
    freeIds = new Set(open.map(q => +q.room.id));
    box.innerHTML = open.length ? open.map(q => {
      const ok = !q.error, n = esc(q.room.name), img = roomImagePath(q.room.name, q.room.type); return `<article class="card"><div class="rimg"><span>${n}</span><img src="${img}" alt="${n}" loading="lazy" onerror="this.remove()"></div><h3>${n}</h3>` + (ok ?
        `<p>${q.nights} <span data-t>night(s)</span> · <span data-t>${q.room.type == 'hall' ? 'Hall' : 'Room'}</span> ${peso(q.sub)}</p><p><span data-t>Extra guest fee</span> ${peso(q.extra)}</p><p><span data-t>Discount</span> −${peso(q.disc)}</p><p><b><span data-t>Total</span> ${peso(q.total)}</b></p><p class="mu"><span data-t>Downpayment</span> ${peso(q.down)}</p><a class="btn" href="${link(q.room.id, q)}" data-t>Book / Select Room</a>`
        : `<p class="err" data-t>${esc(q.error)}</p><a class="btn ghost" href="${link(q.room.id, q)}" data-t>Book / Select Room</a>`) + '</article>'
    }).join('') : `<p class="mu" data-t>No rooms are available for these dates. Please try other dates.</p>`;
    applyLang(); syncRoomList(); drawCal()
  }
  let tm; const live = () => { clearTimeout(tm); tm = setTimeout(search, 200) };
  bf.onsubmit = e => { e.preventDefault(); search() };
  bf.ci.addEventListener('change', () => { bf.co.min = bf.ci.value; if (bf.co.value && bf.co.value <= bf.ci.value) bf.co.value = addDays(bf.ci.value, 1) });
  ['ci', 'co', 'pax', 'voucher'].forEach(k => { bf[k].addEventListener('change', live); bf[k].addEventListener('input', live) });
  const qs = new URLSearchParams(location.search);['ci', 'co', 'pax'].forEach(k => { if (qs.get(k)) bf[k].value = qs.get(k) });

  /* ---- availability calendar: each night is free or booked, for one room or for all rooms ---- */
  const MON = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  let drag = null; /* {a: anchor day, b: current day} while the pointer is held down */
  const range = () => drag ? [drag.a < drag.b ? drag.a : drag.b, drag.a < drag.b ? drag.b : drag.a] : [bf.ci.value, bf.co.value];
  function drawCal() {
    if (!calData) return; const [Y, M] = calMonth.split('-').map(Number), first = new Date(Y, M - 1, 1), days = new Date(Y, M, 0).getDate(), rooms = calData.rooms.filter(r => !calRoom || r.id == calRoom);
    $('#cmon').textContent = `${MON[M - 1]} ${Y}`; const [lo, hi] = range();
    let h = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map(d => `<b class="dow">${d}</b>`).join('') + '<i></i>'.repeat(first.getDay());
    for (let d = 1; d <= days; d++) {
      const day = `${calMonth}-${String(d).padStart(2, '0')}`, past = day < calData.today;
      const taken = new Set(calData.bookings.filter(b => b.checkin <= day && b.checkout > day).map(b => +b.room_id)), free = rooms.filter(r => !taken.has(+r.id)).length;
      const st = past ? 'past' : free == 0 ? 'full' : free < rooms.length ? 'part' : 'open', lbl = past ? '' : !calRoom ? `${free}/${rooms.length}` : free ? t('Free') : t('Booked');
      const inR = lo && hi && day >= lo && day <= hi;
      h += `<button type="button" class="day ${st}${day == lo || day == hi ? ' pick' : ''}${inR ? ' inr' : ''}" data-day="${day}"${past ? ' disabled' : ''}><span>${d}</span><small>${lbl}</small></button>`
    }
    $('#cal').innerHTML = h;
    $('#csel').textContent = lo && hi && hi > lo ? `${lo} → ${hi} (${Math.round((new Date(hi) - new Date(lo)) / 864e5)} ${t('night(s)')})` : ''
  }
  const loadCal = async () => { calData = await post('calendar', { month: calMonth }); syncRoomList(); drawCal() };
  $('#cprev').onclick = () => { const [y, m] = calMonth.split('-').map(Number); calMonth = ymd(new Date(y, m - 2, 1)).slice(0, 7); loadCal() };
  $('#cnext').onclick = () => { const [y, m] = calMonth.split('-').map(Number); calMonth = ymd(new Date(y, m, 1)).slice(0, 7); loadCal() };
  $('#croom').onchange = e => { calRoom = e.target.value; drawCal(); search() };
  /* press on the check-in date, hold and drag to the check-out date, release */
  const dayAt = (x, y) => { const el = document.elementFromPoint(x, y), b = el && el.closest('[data-day]'); return b && !b.disabled ? b.dataset.day : null };
  const cal = $('#cal');
  cal.addEventListener('pointerdown', e => {
    if (e.button > 0) return; const b = e.target.closest('[data-day]'); if (!b || b.disabled) return;
    e.preventDefault(); drag = { a: b.dataset.day, b: b.dataset.day }; cal.setPointerCapture(e.pointerId); drawCal()
  });
  cal.addEventListener('pointermove', e => { if (!drag) return; const d = dayAt(e.clientX, e.clientY); if (d && d != drag.b) { drag.b = d; drawCal() } });
  const finish = e => {
    if (!drag) return; const d = e.type == 'pointerup' ? dayAt(e.clientX, e.clientY) : null; if (d) drag.b = d;
    let [lo, hi] = range(); drag = null;
    if (e.type == 'pointercancel') { drawCal(); return }
    if (hi == lo) hi = addDays(lo, 1);                /* a plain tap books one night */
    bf.ci.value = lo; bf.co.value = hi; bf.co.min = lo; drawCal(); search(); ($('#rhint') || bf).scrollIntoView({ behavior: 'smooth', block: 'start' })
  };
  cal.addEventListener('pointerup', finish); cal.addEventListener('pointercancel', finish);
  cal.addEventListener('dragstart', e => e.preventDefault());
  [bf.ci, bf.co].forEach(i => i.addEventListener('change', drawCal));
  $('#lang').addEventListener('click', () => { drawCal(); syncRoomList() }); loadCal();
}

/* ---- reservation page (step 1): room, dates, guests, live summary, terms, then on to the payment page ---- */
const mr = $('#mr');
if (mr) {
  const rooms = JSON.parse(mr.dataset.rooms), qs = new URLSearchParams(location.search), sum = $('#sum');
  const addDays = (d, n) => { const x = new Date(d + 'T00:00:00'); x.setDate(x.getDate() + n); return ymd(x) };
  const byId = id => rooms.find(r => r.id == id);
  rooms.forEach(r => mr.room.add(new Option(`${r.name} · ${peso(r.rate)}`, r.id)));
  mr.room.value = byId(qs.get('room')) ? qs.get('room') : rooms[0]?.id;
  /* defaults: tonight, 1 night, the room's standard capacity */
  const today = ymd(new Date()), cap = () => byId(mr.room.value) || { base_cap: 2, max_cap: 40 };
  mr.ci.min = mr.co.min = today;
  mr.ci.value = qs.get('ci') >= today ? qs.get('ci') : today;
  mr.co.value = qs.get('co') > mr.ci.value ? qs.get('co') : addDays(mr.ci.value, 1);
  mr.pax.value = qs.get('pax') || cap().base_cap; mr.pax.max = cap().max_cap; mr.co.min = addDays(mr.ci.value, 1);
  if (qs.get('voucher')) { mr.voucher.value = /^\d+$/.test(qs.get('voucher')) ? 'VCH-' + qs.get('voucher') : qs.get('voucher'); $('#vbox').open = true }
  ready.then(() => { if (ME) ME.vouchers.filter(v => v.kind != 'free_food').forEach(v => mr.vsel.add(new Option(`${v.prize} (${v.code})`, v.code))); syncV(); refresh() });
  const syncV = () => { const c = mr.voucher.value.trim().toUpperCase(); mr.vsel.value = [...mr.vsel.options].some(o => o.value == c) ? c : '' };
  const row = (k, v, cls = '') => `<div class="srow ${cls}"><span data-t>${k}</span><b>${v}</b></div>`;
  let seq = 0, ok = false, why = '';
  const payload = () => { const d = Object.fromEntries(new FormData(mr)); delete d.vsel; delete d.agree; return d };
  async function refresh() {
    const my = ++seq, d = payload(); const [q, s] = await Promise.all([post('quote', d), post('search', d)]); if (my != seq) return;
    /* rooms that are full for these dates are flagged in the dropdown */
    if (!s.error) s.rooms.forEach(x => { const o = [...mr.room.options].find(o => o.value == x.room.id); if (o) { const base = `${x.room.name} · ${peso(x.room.rate)}`; o.disabled = !x.free && x.room.id != mr.room.value; o.textContent = x.free ? base : `${base} (${t('Unavailable')})` } });
    const r = byId(mr.room.value), code = mr.voucher.value.trim(), im = $('#sumimg');
    im.hidden = false; im.innerHTML = `<span>${esc(r.name)}</span><img src="${roomImagePath(r.name, r.type)}" alt="${esc(r.name)}" loading="lazy" onerror="this.remove()">`; 
    ok = false; why = ''; $('#smsg').textContent = '';
    if (code) { $('#vmsg').className = q.voucher_ok ? 'ok' : 'err'; $('#vmsg').textContent = q.voucher_ok ? t('Voucher applied') + ': ' + (ME?.vouchers.find(v => v.code == code.toUpperCase() || v.id == code)?.prize || code) : t(q.error && /oucher/.test(q.error) ? q.error : 'Voucher not valid') } else { $('#vmsg').textContent = '' }
    if (q.nights === undefined) { sum.innerHTML = row('Accommodation / Room', esc(r.name)); why = q.error || 'Invalid dates'; $('#smsg').textContent = t(why); applyLang(); return }
    const balance = Math.max(0, q.total - q.down);
    sum.innerHTML = row('Accommodation / Room', esc(r.name)) + row(`Room rate (${q.nights} night(s))`, peso(q.sub)) + row('Additional guests', peso(q.extra)) + (+q.disc > 0 ? row('Discount', '−' + peso(q.disc)) : '') +
      row('Estimated total', peso(q.total), 'tot') + row('50% downpayment', peso(q.down), 'dp') + row('Balance due upon check-in', peso(balance), 'mu');
    why = q.error && !(code && /oucher/.test(q.error)) ? q.error : (!q.free ? 'Not available for these dates' : (code && !q.voucher_ok ? 'Voucher not valid' : ''));
    $('#smsg').textContent = t(why); ok = !why; applyLang()
  }
  let tm; const live = () => { clearTimeout(tm); tm = setTimeout(refresh, 120) };
  mr.room.addEventListener('change', () => { mr.pax.value = cap().base_cap; mr.pax.max = cap().max_cap; refresh() });
  mr.ci.addEventListener('change', () => { mr.co.min = addDays(mr.ci.value, 1); if (mr.co.value <= mr.ci.value) mr.co.value = addDays(mr.ci.value, 1); refresh() });
  mr.co.addEventListener('change', refresh); mr.pax.addEventListener('input', live);
  mr.voucher.addEventListener('input', () => { syncV(); live() });                 /* total updates as the code is typed */
  mr.vsel.addEventListener('change', () => { mr.voucher.value = mr.vsel.value; refresh() });
  $('#lang').addEventListener('click', refresh);
  $('#tcLink').onclick = e => { e.preventDefault(); $('#tc').showModal() };
  $('#cancelTc').onclick = () => $('#tc').close();
  $('#tcAgree').onclick = () => { $('#agree').checked = true; $('#tc').close() };
  mr.addEventListener('submit', async e => {
    e.preventDefault();
    if (!$('#agree').checked) { alert(t('Please accept the Terms & Conditions')); $('#agree').focus(); return }   /* stays on this page */
    if (!ME) { location = 'login.php?next=' + encodeURIComponent('make-reservation.php' + location.search); return }
    await refresh(); if (!ok) { alert(t(why || 'Invalid dates')); return }
    const b = $('#reserveBtn'); b.disabled = true; const r = await post('book', { ...payload(), agree: 1 });
    if (r.error) { b.disabled = false; $('#smsg').textContent = r.error; alert(r.error); refresh(); return }
    location = 'payment.php?ref=' + encodeURIComponent(r.ref)
  });
}
const qb = $('#qb');
if (qb) { qb.ci.min = qb.co.min = ymd(new Date()); qb.ci.onchange = () => { qb.co.min = qb.ci.value } }

/* ---- rewards wheel (the server picks the prize; this only animates it). The slices come from Admin > Rewards via the "wheel" action. ---- */
if ($('#wheel')) {
  let PR = [], rot = 0;
  const COL = ['#0B5D4B', '#2E8B74'], GOLD = '#C8A96A', EDGE = '#1F7A63';
  /* alternate two greens; VIP is gold; with an odd number of slices the last one gets a third green so it never matches the first */
  const colour = i => PR[i].kind == 'vip' ? GOLD : (i == PR.length - 1 && PR.length % 2 && i > 0 ? EDGE : COL[i % 2]);
  const lines = s => {
    const out = []; let cur = '';
    s.split(/\s+/).forEach(w => { if (cur && (cur + ' ' + w).length > 13) { out.push(cur); cur = w } else cur = cur ? cur + ' ' + w : w }); if (cur) out.push(cur);
    const l = out.slice(0, 3); if (out.length > 3) l[2] += '…'; return l
  };
  const draw = () => {
    const c = $('#wheel').getContext('2d'); c.clearRect(0, 0, 360, 360); if (!PR.length) return;
    const A = 2 * Math.PI / PR.length; c.save(); c.translate(180, 180); c.rotate(rot);
    PR.forEach((p, i) => {
      const m = i * A - Math.PI / 2; c.beginPath(); c.moveTo(0, 0); c.arc(0, 0, 170, m - A / 2, m + A / 2); c.fillStyle = colour(i); c.fill(); c.strokeStyle = '#F7F5EF'; c.lineWidth = 2; c.stroke();
      c.save(); c.translate(Math.cos(m) * 105, Math.sin(m) * 105); c.rotate(-rot); c.fillStyle = p.kind == 'vip' ? '#12231D' : '#fff'; c.font = `600 ${PR.length > 6 ? 11 : 13}px Inter,sans-serif`; c.textAlign = 'center';
      const ls = lines(p.label); ls.forEach((x, n) => c.fillText(x, 0, 4 + (n - (ls.length - 1) / 2) * 14)); c.restore()
    }); c.restore()
  };
  post('wheel').then(r => { PR = r.prizes || []; draw() });
  const show = r => { $('#pts').textContent = r.guest.points; $('#spin').disabled = r.guest.points < 100; $('#vl').innerHTML = r.vouchers.map(v => `<li>${esc(v.prize)}</li>`).join('') || `<li class="mu">—</li>` + (r.guest.vip ? `<li>VIP</li>` : '') };
  const load = async () => { const r = await post('lookup'); $('#rmsg').innerHTML = r.error ? `<a href="${loginUrl()}">${t('Log in or register')}</a>` : ''; if (!r.error) show(r); return r };
  load();
  $('#spin').onclick = async () => {
    $('#spin').disabled = true; $('#win').textContent = ''; const r = await post('spin');
    if (r.error) { $('#rmsg').textContent = r.error; return }
    PR = r.prizes; draw(); /* the server's list is the one the prize index refers to, in case the rewards were edited meanwhile */
    const A = 2 * Math.PI / PR.length, start = rot, end = start + 12 * Math.PI + ((-r.index * A - start) % (2 * Math.PI) + 2 * Math.PI) % (2 * Math.PI), t0 = performance.now();
    const step = n => {
      const k = Math.min((n - t0) / 4500, 1); rot = start + (end - start) * (1 - Math.pow(1 - k, 3)); draw();
      if (k < 1) requestAnimationFrame(step); else { $('#win').textContent = t('You won') + ' ' + r.prize; load() }
    }; requestAnimationFrame(step)
  };
}

/* ---- header: full-width bar at the top -> floating glass pill when scrolled; hides on scroll down, returns on scroll up ---- */
(() => {
  const bar = document.querySelector('header.top'); if (!bar) return;
  const root = document.documentElement; let last = scrollY, tick = false;
  /* the bar is fixed, so remember its full-width height and let the page start below it */
  const measure = () => {
    const sc = bar.classList.contains('scrolled'), hd = bar.classList.contains('hide'), tr = bar.style.transition;
    bar.style.transition = 'none'; bar.classList.remove('scrolled', 'hide');
    root.style.setProperty('--navh', bar.offsetHeight + 'px');
    if (sc) bar.classList.add('scrolled'); if (hd) bar.classList.add('hide');
    void bar.offsetHeight; bar.style.transition = tr
  };
  const show = () => bar.classList.remove('hide');
  const update = () => {
    tick = false; const y = Math.max(0, scrollY), d = y - last;
    bar.classList.toggle('scrolled', y > 20);
    if (y <= 20 || document.querySelector('#drawer.open')) { show(); last = y; return }
    if (Math.abs(d) < 6) return;
    if (d < 0) show(); else if (y > bar.offsetHeight + 60) bar.classList.add('hide');
    last = y
  };
  measure(); update();
  addEventListener('scroll', () => { if (!tick) { tick = true; requestAnimationFrame(update) } }, { passive: true });
  addEventListener('resize', measure);
  if (document.fonts) document.fonts.ready.then(measure);
  bar.addEventListener('focusin', show);
})();

/* ---- page transitions: curtain + fade/slide-in on load (CSS), fade-out before following an internal link ---- */
(() => {
  const root = document.documentElement;
  if (matchMedia('(prefers-reduced-motion: reduce)').matches || document.body.classList.contains('admbody')) return;
  addEventListener('pageshow', e => { if (e.persisted) root.classList.remove('leaving') });
  const go = fn => { root.classList.add('leaving'); setTimeout(fn, 300); setTimeout(() => root.classList.remove('leaving'), 5000) };
  document.addEventListener('click', e => {
    if (e.defaultPrevented || e.button || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    const a = e.target.closest('a[href]'); if (!a || (a.target && a.target != '_self') || a.hasAttribute('download')) return;
    if (a.getAttribute('href').startsWith('#')) return;
    let u; try { u = new URL(a.href, location.href) } catch (x) { return }
    if (!/^https?:$/.test(u.protocol) || u.origin != location.origin) return;
    if (u.pathname == location.pathname && u.search == location.search && u.hash) return;
    e.preventDefault();
    if (u.href == location.href) { scrollTo({ top: 0, behavior: 'smooth' }); return }
    go(() => { location.href = u.href })
  });
  /* GET forms that opt in with data-fade (e.g. the quick availability form) */
  document.addEventListener('submit', e => {
    const f = e.target; if (e.defaultPrevented || !f.matches || !f.matches('form[data-fade]')) return;
    e.preventDefault(); go(() => f.submit())
  });
})();

/* ---- landing page: scroll-driven morphing. Sets --p (0 -> 1) on every [data-morph] element; the CSS turns it into shape / scale / surface changes ---- */
(() => {
  const els = $$('[data-morph]');
  if (!els.length || matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  const clamp = x => Math.min(1, Math.max(0, x)), ease = x => x * x * (3 - 2 * x);
  let tick = false;
  const run = () => {
    tick = false; const vh = innerHeight;
    els.forEach(el => {
      let p;
      if (el.dataset.morph == 'hero') p = clamp(scrollY / (el.offsetHeight * .7));
      else { const r = el.getBoundingClientRect(); p = clamp((vh * .98 - r.top - (+el.dataset.o || 0) * vh * .07) / (vh * .5)) }
      p = ease(p).toFixed(3); if (el._p !== p) { el._p = p; el.style.setProperty('--p', p) }
    })
  };
  const queue = () => { if (!tick) { tick = true; requestAnimationFrame(run) } };
  run(); addEventListener('scroll', queue, { passive: true }); addEventListener('resize', queue);
  addEventListener('load', run)
})();

/* ---- nav: mark the current page / section ---- */
(() => {
  const links = [...document.querySelectorAll('header.top nav a')], here = location.pathname.split('/').pop() || 'index.php';
  const mark = () => links.forEach(a => {
    const [p, h] = a.getAttribute('href').split('#'), onAbout = location.hash == '#about-us';
    const on = p == here && (h ? onAbout : !(here == 'index.php' && onAbout));
    on ? a.setAttribute('aria-current', 'page') : a.removeAttribute('aria-current')
  });
  mark(); addEventListener('hashchange', mark)
})();

/* ---- login: remember the page the guest came from and return there afterwards ---- */
function loginUrl() { return 'login.php?next=' + encodeURIComponent((location.pathname.split('/').pop() || 'index.php') + location.search + location.hash) }
function safeNext(n) { return typeof n == 'string' && /^[\w-]+\.php(\?[^#]*)?(#[\w-]*)?$/.test(n) && !/^(login|admin)/i.test(n) ? n : null }
function backTarget() {
  const q = safeNext(new URLSearchParams(location.search).get('next')); if (q) return q;
  try { const u = new URL(document.referrer); if (u.origin == location.origin) { const f = safeNext(u.pathname.split('/').pop() + u.search + u.hash); if (f) return f } } catch (e) { }
  return 'index.php'
}
