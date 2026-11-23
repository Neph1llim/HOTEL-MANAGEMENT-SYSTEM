const $ = (s) => document.querySelector(s),
  $$ = (s) => [...document.querySelectorAll(s)];
const peso = (n) => "₱" + Number(n).toLocaleString("en-PH");
const esc = (s) =>
  String(s).replace(
    /[&<>"']/g,
    (c) =>
      ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[
        c
      ],
  );
const post = async (a, d = {}) => {
  const f = new FormData();
  f.append("a", a);
  for (const k in d) f.append(k, d[k]);
  return (await fetch("api.php", { method: "POST", body: f })).json();
};
/* English text is the key; add more pairs here to translate more of the site */
const TL = {
  Hall: "Hall",
  "Book the hall": "I-book ang hall",
  "Staff login": "Pag-log in ng staff",
  "Our rooms": "Aming mga kwarto",
  "Photos shown here are placeholders. Add your own in assets/img using the file name shown on each card.":
    "Mga placeholder lamang ang mga larawan dito. Magdagdag ng sarili ninyo sa assets/img gamit ang pangalan ng file sa bawat card.",
  "Good for 2 to 3 guests": "Para sa 2 hanggang 3 bisita",
  "Good for 4 to 5 guests": "Para sa 4 hanggang 5 bisita",
  "Good for up to 30 guests (maximum 40)":
    "Para sa hanggang 30 bisita (pinakamarami 40)",
  "per night": "bawat gabi",
  "Book this room": "I-book ang kwartong ito",
  "See room photos": "Tingnan ang mga larawan ng kwarto",
  "Welcome to Bella Vista": "Maligayang pagdating sa Bella Vista",
  "Log in": "Mag-log in",
  "Log in or register": "Mag-log in o magparehistro",
  "Create an account": "Gumawa ng account",
  Password: "Password",
  "My reservations": "Aking mga reserbasyon",
  "Account profile": "Profile ng account",
  "Save changes": "I-save ang pagbabago",
  "Current password": "Kasalukuyang password",
  "New password": "Bagong password",
  "Pay now": "Magbayad na",
  "No reservations yet.": "Wala pang reserbasyon.",
  "Cancel reservation": "Kanselahin ang reserbasyon",
  "Cancel this reservation? Your downpayment will be refunded according to the hotel's policy.":
    "Kakanselahin ang reserbasyon na ito? Ibabalik ang downpayment ayon sa patakaran ng hotel.",
  "Cancel this reservation? Check-in is less than 3 days away, so the downpayment is non-refundable.":
    "Kakanselahin ang reserbasyon na ito? Wala pang 3 araw bago ang check-in, kaya hindi na maibabalik ang downpayment.",
  "Reservation cancelled. Free cancellation applied: please contact the hotel about your refund.":
    "Nakansela na ang reserbasyon. Libreng pagkansela: makipag-ugnayan sa hotel tungkol sa refund.",
  "Reservation cancelled. Per the Terms & Conditions, the downpayment is non-refundable.":
    "Nakansela na ang reserbasyon. Ayon sa Mga Tuntunin at Kondisyon, hindi na maibabalik ang downpayment.",
  "Reserve a Room": "Mag-reserba ng kwarto",
  "My Reservations": "Aking mga Reserbasyon",
  "My Points & Rewards": "Aking Puntos at Gantimpala",
  "My Vouchers": "Aking mga Voucher",
  "Account Profile": "Profile ng Account",
  "Terms & Conditions": "Mga Tuntunin at Kondisyon",
  "Log out": "Mag-log out",
  "Scan the QR code and pay the downpayment, then upload your proof of payment.":
    "I-scan ang QR code at bayaran ang downpayment, saka i-upload ang patunay ng bayad.",
  "Payment reference no.": "Reference no. ng bayad",
  "Amount paid": "Halagang binayad",
  "Proof of payment (screenshot)": "Patunay ng bayad (screenshot)",
  "Submit payment": "Isumite ang bayad",
  "Payment submitted": "Naisumite na ang bayad",
  "The hotel will verify your proof of payment and confirm your reservation.":
    "Susuriin ng hotel ang patunay ng bayad at kukumpirmahin ang reserbasyon.",
  "About us": "Tungkol sa amin",
  Rooms: "Mga Kwarto",
  "Book now": "Mag-book na",
  Rewards: "Gantimpala",
  "Foothills of the mountains, Philippines": "Paanan ng kabundukan, Pilipinas",
  "Open Monday to Sunday": "Bukas Lunes hanggang Linggo",
  "Check-in": "Check-in",
  "Check-out": "Check-out",
  "Rest at the foot of the mountains": "Magpahinga sa paanan ng kabundukan",
  "A quiet eco-chic retreat for families, travelers and celebrations.":
    "Tahimik na eco-chic na pahingahan para sa pamilya, manlalakbay at mga selebrasyon.",
  "Bella Vista Hotel & Events Place is a premier eco-chic boutique hotel and events destination at the scenic foothills of the mountains. It offers a tranquil, elegant and exclusive retreat for families, tourists and corporate clients, away from the hustle and bustle of the city.":
    "Ang Bella Vista Hotel & Events Place ay isang premyadong eco-chic boutique hotel at lugar para sa mga okasyon sa magandang paanan ng kabundukan. Tahimik, elegante at eksklusibong pahingahan ito para sa pamilya, turista at kliyenteng korporasyon, malayo sa ingay ng lungsod.",
  "Our history": "Aming kasaysayan",
  "Established in 2021, Bella Vista began as an exclusive family-owned resthouse. Growing demand for intimate celebrations and restful getaways turned it into a boutique hotel and events venue, known for warm Filipino hospitality, picturesque views and top-tier service.":
    "Itinatag noong 2021, nagsimula ang Bella Vista bilang pribadong resthouse ng isang pamilya. Dahil sa dumaraming nais ng payapang bakasyon at maliliit na selebrasyon, naging boutique hotel at events venue ito, na kilala sa magiliw na pagtanggap ng Pilipino, magagandang tanawin at de-kalidad na serbisyo.",
  "Rooms and event hall": "Mga kwarto at event hall",
  "Standard Rooms": "Standard na Kwarto",
  "Family Rooms": "Pampamilyang Kwarto",
  "Event Hall": "Event Hall",
  "2 to 3 guests · ₱3,500 per night": "2 hanggang 3 bisita · ₱3,500 bawat gabi",
  "4 to 5 guests · ₱6,000 per night": "4 hanggang 5 bisita · ₱6,000 bawat gabi",
  "Up to 30 guests · ₱15,000 per night":
    "Hanggang 30 bisita · ₱15,000 bawat gabi",
  "₱1,000 extra for a 3rd guest": "Dagdag na ₱1,000 para sa ika-3 bisita",
  "Covers up to 5 guests": "Sakop ang hanggang 5 bisita",
  "₱500 per guest beyond 30, up to 40":
    "₱500 bawat bisitang lampas 30, hanggang 40",
  Gallery: "Galerya",
  "Book your stay": "I-book ang inyong pananatili",
  Guests: "Bilang ng bisita",
  "Full name": "Buong pangalan",
  Email: "Email",
  "Mobile number": "Numero ng telepono",
  Voucher: "Voucher",
  None: "Wala",
  "Check availability": "Tingnan ang availability",
  "night(s)": "gabi",
  Room: "Kwarto",
  "Extra guest fee": "Dagdag na bayad sa bisita",
  Discount: "Diskwento",
  Total: "Kabuuan",
  Downpayment: "Downpayment",
  Reserve: "I-reserve",
  "Invalid dates": "Hindi wastong mga petsa",
  "Check-in cannot be in the past": "Hindi maaaring nakaraan ang check-in",
  "Check-out must be at least 1 night after check-in (check-in 2:00 PM, check-out 12:00 NN)":
    "Dapat hindi bababa sa 1 gabi pagkatapos ng check-in ang check-out (check-in 2:00 PM, check-out 12:00 NN)",
  "Voucher not valid": "Hindi wasto ang voucher",
  "Voucher does not apply to this room":
    "Hindi magagamit ang voucher sa kwartong ito",
  "Not available for these dates": "Hindi available sa mga petsang ito",
  "Reservation received": "Natanggap na ang reserbasyon",
  Reference: "Reference",
  "We will confirm after payment.": "Kukumpirmahin namin pagkatapos ng bayad.",
  "Terms and conditions": "Mga tuntunin at kondisyon",
  "Downpayment: A downpayment secures your reservation. The balance is paid at check-in.":
    "Downpayment: Kinukumpirma ng downpayment ang inyong reserbasyon. Babayaran ang balanse sa check-in.",
  "Cancellation: Cancellations less than 3 days before check-in are non-refundable.":
    "Pagkansela: Hindi na maibabalik ang bayad kung kakanselahin nang wala pang 3 araw bago ang check-in.",
  "Damage: Guests are billed for any damage or loss to hotel property, furniture or fixtures.":
    "Pinsala: Sisingilin ang bisita sa anumang pinsala o pagkawala ng gamit, kasangkapan o fixtures ng hotel.",
  "No smoking: Smoking inside guest rooms is prohibited. Violations are fined ₱2,000.":
    "Bawal manigarilyo: Bawal ang paninigarilyo sa loob ng mga kwarto. May multang ₱2,000 ang lalabag.",
  "Quiet hours: Keep noise low after 10:00 PM around the accommodation areas.":
    "Tahimik na oras: Hinaan ang ingay pagkatapos ng 10:00 PM sa paligid ng mga tuluyan.",
  "I agree to the terms and conditions":
    "Sumasang-ayon ako sa mga tuntunin at kondisyon",
  "Confirm reservation": "Kumpirmahin ang reserbasyon",
  Cancel: "Kanselahin",
  "Please accept the Terms & Conditions":
    "Pakitanggap ang mga Tuntunin at Kondisyon",
  "My points and rewards": "Aking puntos at gantimpala",
  "Show my points": "Ipakita ang puntos ko",
  points: "puntos",
  "You earn 1% of what you spend after each completed stay. Each spin costs 100 points.":
    "Kumikita ka ng 1% ng ginastos mo pagkatapos ng bawat natapos na pananatili. 100 puntos bawat ikot.",
  "Spin for 100 points": "Umikot para sa 100 puntos",
  "Your vouchers": "Mga voucher mo",
  "What you can win": "Maaari mong mapanalunan",
  "You won": "Nanalo ka ng",
  "Free room vouchers cover the room rate for up to 2 nights. Extra guest fees still apply.":
    "Sakop ng libreng voucher ang presyo ng kwarto hanggang 2 gabi. May bayad pa rin ang dagdag na bisita.",
};
let lang = localStorage.lang || "en";
const t = (s) => {
  if (lang != "tl") return s;
  const m = /^Maximum capacity is (\d+) guests$/.exec(s);
  return TL[s] || (m ? `Hanggang ${m[1]} bisita lamang` : s);
};
function applyLang() {
  document.documentElement.lang = lang;
  $$("[data-t]").forEach((e) => {
    e.dataset.en ??= e.textContent.trim();
    e.textContent = t(e.dataset.en);
  });
  if ($("#lang")) $("#lang").textContent = lang == "en" ? "TL" : "EN";
}
function applyTheme(m) {
  document.documentElement.dataset.theme = m;
  localStorage.theme = m;
  $("#theme").innerHTML = m == "dark" ? "&#9728;" : "&#9790;";
}
applyTheme(
  localStorage.theme ||
    (matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"),
);
applyLang();
$("#theme").onclick = () =>
  applyTheme(
    document.documentElement.dataset.theme == "dark" ? "light" : "dark",
  );
if ($("#lang"))
  $("#lang").onclick = () => {
    lang = lang == "en" ? "tl" : "en";
    localStorage.lang = lang;
    applyLang();
  };

/* ---- guest session, drawer, payment form ---- */
let ME = null;
const ready = post("lookup").then((r) => {
  ME = r.error ? null : r;
  if (!$("#dinfo")) return;
  $("#dinfo").innerHTML = ME
    ? `<b>${esc(ME.guest.name)}</b><small>${esc(ME.guest.email)}</small>`
    : `<a href="login.php">${t("Log in or register")}</a>`;
  $("#dlogout").hidden = !ME;
  $$(".auth").forEach((x) => (x.hidden = !ME));
});
if ($("#menu")) {
  const d = $("#drawer");
  $("#menu").onclick = () => d.classList.toggle("open");
  $("#dov").onclick = () => d.classList.remove("open");
  $$("#drawer a").forEach((x) =>
    x.addEventListener("click", () => d.classList.remove("open")),
  );
  $("#dlogout").onclick = async () => {
    await post("glogout");
    location = "index.php";
  };
}
const payForm = (
  ref,
  down,
) => `<img class="qr" src="assets/img/payment-qr.png" alt="Add the hotel payment QR at assets/img/payment-qr.png"><p class="mu" data-t>Scan the QR code and pay the downpayment, then upload your proof of payment.</p>
 <form class="pf form" data-ref="${ref}"><label><span data-t>Payment reference no.</span><input name="ref_no" required></label><label><span data-t>Amount paid</span><input type="number" name="amount" step="0.01" value="${down}" required></label><label><span data-t>Proof of payment (screenshot)</span><input type="file" name="proof" accept="image/*" required></label><button class="btn" data-t>Submit payment</button></form><p class="err"></p>`;
document.addEventListener("submit", async (e) => {
  const f = e.target;
  if (!f.classList.contains("pf")) return;
  e.preventDefault();
  const d = new FormData(f);
  d.append("a", "pay");
  d.append("ref", f.dataset.ref);
  const r = await (await fetch("api.php", { method: "POST", body: d })).json();
  if (r.error) {
    f.nextElementSibling.textContent = r.error;
    return;
  }
  f.closest(".pay").innerHTML =
    `<h3 data-t>Payment submitted</h3><p class="mu" data-t>The hotel will verify your proof of payment and confirm your reservation.</p>`;
  applyLang();
});
if ($("#lf")) {
  const go = async (f) => {
    const r = await post(
      f.id == "lf" ? "glogin" : "register",
      Object.fromEntries(new FormData(f)),
    );
    if (r.error) $("#lerr").textContent = r.error;
    else location = "reserve.php";
  };
  $("#lf").onsubmit = (e) => {
    e.preventDefault();
    go(e.target);
  };
  $("#rg").onsubmit = (e) => {
    e.preventDefault();
    go(e.target);
  };
}
if ($("#acf")) {
  const f = $("#acf");
  ready.then(() => {
    if (!ME) return (location = "login.php");
    f.name.value = ME.guest.name;
    f.phone.value = ME.guest.phone;
  });
  f.onsubmit = async (e) => {
    e.preventDefault();
    const r = await post("profile", Object.fromEntries(new FormData(f)));
    $("#amsg").textContent = r.error || "Saved";
    $("#amsg").className = r.error ? "err" : "mu";
  };
}
if ($("#bklist")) {
  const bkCard = (x) =>
    `<article class="card pay"><h3>${esc(x.room)} · ${x.ref}</h3><p>${x.checkin} → ${x.checkout} · ${x.pax} <span data-t>Guests</span></p><p><span data-t>Total</span> ${peso(x.total)} · <span data-t>Downpayment</span> ${peso(x.downpayment)}</p><p><span class="s ${x.status}">${x.status}</span> ${x.pay ? `<span class="s ${x.pay == "verified" ? "approved" : x.pay == "rejected" ? "cancelled" : "pending"}">payment ${x.pay}</span>` : ""}</p>${x.status == "pending" && x.pay != "pending" ? `<details><summary data-t>Pay now</summary>${payForm(x.ref, x.downpayment)}</details>` : ""}${x.can_cancel ? `<p><button class="btn sm ghost" data-cancel="${x.ref}" data-refund="${x.refundable ? 1 : 0}" data-t>Cancel reservation</button></p>` : ""}</article>`;
  const loadBk = () =>
    post("mybookings").then((r) => {
      if (r.error) return (location = "login.php");
      $("#bklist").innerHTML =
        r.res.map(bkCard).join("") ||
        `<p class="mu" data-t>No reservations yet.</p>`;
      applyLang();
    });
  loadBk();
  $("#bklist").addEventListener("click", async (e) => {
    const b = e.target.closest("[data-cancel]");
    if (!b) return;
    if (
      !confirm(
        t(
          b.dataset.refund == "1"
            ? "Cancel this reservation? Your downpayment will be refunded according to the hotel's policy."
            : "Cancel this reservation? Check-in is less than 3 days away, so the downpayment is non-refundable.",
        ),
      )
    )
      return;
    b.disabled = true;
    const r = await post("cancel_booking", { ref: b.dataset.cancel });
    if (r.error) {
      b.disabled = false;
      alert(r.error);
      return;
    }
    await loadBk();
    alert(
      t(
        r.refundable
          ? "Reservation cancelled. Free cancellation applied: please contact the hotel about your refund."
          : "Reservation cancelled. Per the Terms & Conditions, the downpayment is non-refundable.",
      ),
    );
  });
}

/* ---- booking ---- */
const bf = $("#bf");
if (bf) {
  const fd = () => Object.fromEntries(new FormData(bf)),
    box = $("#results");
  let sel = null;
  bf.ci.min = bf.co.min = new Date().toISOString().slice(0, 10);
  bf.ci.onchange = () => {
    bf.co.min = bf.ci.value;
  };
  ready.then(() => {
    if (ME)
      ME.vouchers.forEach((v) => bf.voucher.add(new Option(v.prize, v.id)));
  });
  const slug = (n) => n.toLowerCase().replace(/[^a-z0-9]+/g, "-");
  bf.onsubmit = async (e) => {
    e.preventDefault();
    const r = await post("search", fd());
    if (r.error) {
      box.innerHTML = `<p class="err" data-t>${esc(r.error)}</p>`;
      applyLang();
      return;
    }
    box.innerHTML = r.rooms
      .map((q) => {
        const ok = !q.error && q.free,
          n = esc(q.room.name);
        return (
          `<article class="card"><div class="rimg"><span>${n}</span><img src="assets/img/${slug(q.room.name)}.jpg" alt="${n}" onerror="this.remove()"></div><h3>${n}</h3>` +
          (ok
            ? `<p>${q.nights} <span data-t>night(s)</span> · <span data-t>${q.room.type == "hall" ? "Hall" : "Room"}</span> ${peso(q.sub)}</p><p><span data-t>Extra guest fee</span> ${peso(q.extra)}</p><p><span data-t>Discount</span> −${peso(q.disc)}</p><p><b><span data-t>Total</span> ${peso(q.total)}</b></p><p class="mu"><span data-t>Downpayment</span> ${peso(q.down)}</p><button class="btn" data-room="${q.room.id}" data-t>Reserve</button>`
            : `<p class="err" data-t>${esc(q.error || "Not available for these dates")}</p>`) +
          "</article>"
        );
      })
      .join("");
    applyLang();
  };
  box.onclick = (e) => {
    const b = e.target.closest("[data-room]");
    if (!b) return;
    if (!ME) {
      location = "login.php";
      return;
    }
    sel = b.dataset.room;
    $("#agree").checked = false;
    $("#tc").showModal();
  };
  $("#cancelTc").onclick = () => $("#tc").close();
  $("#confirm").onclick = async () => {
    if (!$("#agree").checked)
      return alert(t("Please accept the Terms & Conditions"));
    const r = await post("book", { ...fd(), room: sel, agree: 1 });
    $("#tc").close();
    if (r.error) {
      box.innerHTML = `<p class="err">${esc(r.error)}</p>`;
      return;
    }
    box.innerHTML = `<div class="card pay"><h3 data-t>Reservation received</h3><p><span data-t>Reference</span>: <b>${r.ref}</b></p><p><span data-t>Total</span> ${peso(r.total)} · <b><span data-t>Downpayment</span> ${peso(r.down)}</b></p>${payForm(r.ref, r.down)}</div>`;
    applyLang();
  };
  const qs = new URLSearchParams(location.search);
  ["ci", "co", "pax"].forEach((k) => {
    if (qs.get(k)) bf[k].value = qs.get(k);
  });
  if (qs.get("ci") && qs.get("co")) bf.requestSubmit();
}
const qb = $("#qb");
if (qb) {
  qb.ci.min = qb.co.min = new Date().toISOString().slice(0, 10);
  qb.ci.onchange = () => {
    qb.co.min = qb.ci.value;
  };
}

/* ---- rewards wheel (the server picks the prize; this only animates it) ---- */
if ($("#wheel")) {
  const PR = [
      "20% off",
      "50% off",
      "75% + meals",
      "Free Standard",
      "Free Family",
      "VIP",
    ],
    COL = ["#0B5D4B", "#2E8B74", "#0B5D4B", "#2E8B74", "#0B5D4B", "#C8A96A"],
    N = 6,
    A = (2 * Math.PI) / N;
  let rot = 0;
  const draw = () => {
    const c = $("#wheel").getContext("2d");
    c.clearRect(0, 0, 360, 360);
    c.save();
    c.translate(180, 180);
    c.rotate(rot);
    PR.forEach((p, i) => {
      const m = i * A - Math.PI / 2;
      c.beginPath();
      c.moveTo(0, 0);
      c.arc(0, 0, 170, m - A / 2, m + A / 2);
      c.fillStyle = COL[i];
      c.fill();
      c.strokeStyle = "#F7F5EF";
      c.lineWidth = 2;
      c.stroke();
      c.save();
      c.translate(Math.cos(m) * 105, Math.sin(m) * 105);
      c.rotate(-rot);
      c.fillStyle = i == 5 ? "#12231D" : "#fff";
      c.font = "600 13px Inter,sans-serif";
      c.textAlign = "center";
      c.fillText(p, 0, 4);
      c.restore();
    });
    c.restore();
  };
  draw();
  const show = (r) => {
    $("#pts").textContent = r.guest.points;
    $("#spin").disabled = r.guest.points < 100;
    $("#vl").innerHTML =
      r.vouchers.map((v) => `<li>${esc(v.prize)}</li>`).join("") ||
      `<li class="mu">—</li>` + (r.guest.vip ? `<li>VIP</li>` : "");
  };
  const load = async () => {
    const r = await post("lookup");
    $("#rmsg").innerHTML = r.error
      ? `<a href="login.php">${t("Log in or register")}</a>`
      : "";
    if (!r.error) show(r);
    return r;
  };
  load();
  $("#spin").onclick = async () => {
    $("#spin").disabled = true;
    $("#win").textContent = "";
    const r = await post("spin");
    if (r.error) {
      $("#rmsg").textContent = r.error;
      return;
    }
    const start = rot,
      end =
        start +
        12 * Math.PI +
        ((((-r.index * A - start) % (2 * Math.PI)) + 2 * Math.PI) %
          (2 * Math.PI)),
      t0 = performance.now();
    const step = (n) => {
      const k = Math.min((n - t0) / 4500, 1);
      rot = start + (end - start) * (1 - Math.pow(1 - k, 3));
      draw();
      if (k < 1) requestAnimationFrame(step);
      else {
        $("#win").textContent = t("You won") + " " + r.prize;
        load();
      }
    };
    requestAnimationFrame(step);
  };
}

/* ---- admin ---- */
if ($("#dash")) {
  const af = $("#af"),
    b = (k, id, s, l, c) =>
      `<button class="btn sm ${c || "ghost"}" data-${k}="${id}" data-s="${s}">${l}</button>`,
    pc = (s) =>
      s == "verified" ? "approved" : s == "rejected" ? "cancelled" : s;
  let RES = [];
  const drawRes = () => {
    const f = $("#rfilter").value,
      rows = RES.filter((x) => !f || x.status == f);
    $("#res").innerHTML =
      "<thead><tr><th>Ref</th><th>Guest</th><th>Room</th><th>Stay</th><th>Pax</th><th>Total</th><th>Downpayment</th><th>Status</th><th>Actions</th></tr></thead><tbody>" +
      (rows
        .map(
          (x) =>
            `<tr><td>${x.ref}</td><td>${esc(x.guest)}<br><small class="mu">${esc(x.email)}</small></td><td>${esc(x.room)}</td><td>${x.checkin} → ${x.checkout}</td><td>${x.pax}</td><td>${peso(x.total)}</td><td>${peso(x.downpayment)}</td><td><span class="s ${x.status}">${x.status}</span></td><td>${x.status == "pending" ? '<small class="mu">Awaiting payment</small> ' : ""}${x.status == "approved" ? b("id", x.id, "completed", "Complete") : ""} ${x.status == "pending" || x.status == "approved" ? b("id", x.id, "cancelled", "Cancel") : ""}</td></tr>`,
        )
        .join("") ||
        '<tr><td colspan="9" class="mu">No reservations to show</td></tr>') +
      "</tbody>";
  };
  const load = async () => {
    const r = await post("dash");
    if (r.error) {
      $("#af-wrap").hidden = false;
      $("#dash").hidden = true;
      return;
    }
    $("#af-wrap").hidden = true;
    $("#dash").hidden = false;
    const s = r.stats,
      pp = r.pays.filter((p) => p.status == "pending").length,
      occ = r.rooms.filter((m) => m.occ > 0).length;
    RES = r.res;
    $("#today").textContent = new Date().toLocaleDateString("en-PH", {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    });
    $("#stats").innerHTML = [
      ["Revenue", peso(s.revenue), "hi"],
      ["Payments to verify", pp],
      ["Pending reservations", s.pending || 0],
      ["Occupied today", occ + " of " + r.rooms.length],
      ["Approved", s.approved || 0],
      ["Completed", s.completed || 0],
      ["Cancelled", s.cancelled || 0],
      ["Total bookings", s.bookings || 0],
    ]
      .map(
        (x) =>
          `<div class="kpi ${x[2] || ""}"><span>${x[0]}</span><b>${x[1]}</b></div>`,
      )
      .join("");
    $("#pays").innerHTML =
      "<thead><tr><th>Ref</th><th>Guest</th><th>Payment ref no.</th><th>Amount paid</th><th>Due</th><th>Proof</th><th>Status</th><th>Actions</th></tr></thead><tbody>" +
      (r.pays
        .map(
          (p) =>
            `<tr><td>${p.ref}</td><td>${esc(p.guest)}</td><td>${esc(p.ref_no)}</td><td>${peso(p.amount)}${+p.amount < +p.downpayment ? ' <small class="err">short</small>' : ""}</td><td>${peso(p.downpayment)}</td><td><a href="proof.php?id=${p.id}" target="_blank">View proof</a></td><td><span class="s ${pc(p.status)}">${p.status}</span></td><td>${p.status == "pending" ? b("pid", p.id, "verified", "Verify", "") + " " + b("pid", p.id, "rejected", "Reject") : ""}</td></tr>`,
        )
        .join("") ||
        '<tr><td colspan="8" class="mu">No payments submitted yet</td></tr>') +
      "</tbody>";
    drawRes();
    $("#rooms").innerHTML =
      "<thead><tr><th>Name</th><th>Type</th><th>Today</th><th>Upcoming bookings</th></tr></thead><tbody>" +
      r.rooms
        .map(
          (m) =>
            `<tr><td>${esc(m.name)}</td><td class="cap">${m.type}</td><td><span class="s ${m.occ > 0 ? "cancelled" : "approved"}">${m.occ > 0 ? "Occupied" : "Available"}</span></td><td>${m.upcoming}</td></tr>`,
        )
        .join("") +
      "</tbody>";
    const mx = Math.max(1, ...r.byType.map((x) => +x.rev));
    $("#bytype").innerHTML =
      r.byType
        .map(
          (x) =>
            `<div class="bar"><span>${x.type}</span><i style="width:${(x.rev / mx) * 100}%"></i><b>${peso(x.rev)} <small class="mu">(${x.n})</small></b></div>`,
        )
        .join("") || '<p class="mu">No approved bookings yet</p>';
  };
  af.onsubmit = async (e) => {
    e.preventDefault();
    const r = await post("login", Object.fromEntries(new FormData(af)));
    $("#aerr").textContent = r.error || "";
    if (!r.error) load();
  };
  $("#logout").onclick = async () => {
    await post("logout");
    load();
  };
  $("#rfilter").onchange = drawRes;
  $("#pays").onclick = async (e) => {
    const x = e.target.closest("[data-pid]");
    if (x) {
      await post("pay_status", { id: x.dataset.pid, s: x.dataset.s });
      load();
    }
  };
  $("#res").onclick = async (e) => {
    const x = e.target.closest("[data-id]");
    if (x) {
      if (x.dataset.s == "cancelled" && !confirm("Cancel this reservation?"))
        return;
      await post("status", { id: x.dataset.id, s: x.dataset.s });
      load();
    }
  };
  load();
  setInterval(() => {
    if (!$("#dash").hidden) load();
  }, 15000);
}
