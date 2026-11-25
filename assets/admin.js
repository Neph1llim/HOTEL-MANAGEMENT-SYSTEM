/* ---- admin dashboard (uses $, post, peso, esc from app.js) ---- */
if ($('#dash')) {
    const af = $('#af'), pc = s => s == 'verified' ? 'approved' : s == 'rejected' ? 'cancelled' : s;
    const btn = (k, id, l, s) => `<button class="btn sm ghost" data-${k}="${id}"${s ? ` data-s="${s}"` : ''}>${l}</button>`;
    let RES = [], ROOMS = [], roomsDirty = false;
    const pad = n => String(n).padStart(2, '0');
    const today = () => { const d = new Date(); return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}` };
    const addDays = (s, n) => { const d = new Date(s + 'T00:00'); d.setDate(d.getDate() + n); return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}` };

    /* ---- Overview month navigation. ovSel = null means "follow the current month", so when the calendar rolls over
       to a new month the Overview switches to it by itself and starts empty; a month picked with the arrows stays put. ---- */
    const MS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    let ovSel = null, NOWM = today().slice(0, 7), MAXM = null, seq = 0;
    let SEARCH = ''; /* text in the Payments / Reservations search box, sent to the server so it searches ALL records */
    if ($('#mnav') && /^\d{4}-(0[1-9]|1[0-2])$/.test(location.hash.slice(1))) ovSel = location.hash.slice(1);
    const shiftM = (m, n) => { const d = new Date(+m.slice(0, 4), +m.slice(5) - 1 + n, 1); return `${d.getFullYear()}-${pad(d.getMonth() + 1)}` };
    const monthName = m => new Date(+m.slice(0, 4), +m.slice(5) - 1, 1).toLocaleDateString('en-PH', { month: 'long', year: 'numeric' });
    const curM = () => ovSel || NOWM;
    const goMonth = m => {
        if (m && m > (MAXM || shiftM(NOWM, 1))) return;
        ovSel = (!m || m == NOWM) ? null : m;
        history.replaceState(null, '', ovSel ? '#' + ovSel : location.pathname); load()
    };

    /* ---- shared pieces ---- */
    const payType = p => p.kind == 'balance' ? 'Paid in Cash' : 'Downpayment';
    const cashTag = p => p.method == 'cash' && p.kind != 'balance' ? ' <span class="tag">cash</span>' : '';
    const shortTag = p => p.kind == 'down' && p.method != 'cash' && +p.amount < +p.downpayment ? ' <small class="err">short</small>' : '';
    const proofCell = p => p.method == 'cash' ? '<span class="mu">Received in person</span>' : `<a href="proof.php?id=${p.id}" data-proof="${p.id}" data-ref="${esc(p.ref)}">View proof</a>`;
    /* "View proof" opens the image in a popup on the same page (X button, Esc or a click outside closes it) instead of a new tab */
    document.addEventListener('click', e => {
        const a = e.target.closest('[data-proof]'), dlg = $('#pfd'); if (!a || !dlg) return;
        if (e.ctrlKey || e.metaKey || e.shiftKey || e.button) return;   /* still lets staff force a new tab on purpose */
        e.preventDefault(); const img = $('#pfimg'), box = $('#pfbox');
        $('#pftitle').textContent = 'Payment proof' + (a.dataset.ref ? ' · ' + a.dataset.ref : ''); box.className = 'pfbox loading';
        img.hidden = true; img.onload = () => { box.className = 'pfbox'; img.hidden = false }; img.onerror = () => { box.className = 'pfbox failed' };
        img.src = 'proof.php?id=' + encodeURIComponent(a.dataset.proof); dlg.showModal()
    });
    if ($('#pfd')) {
        $('#pfx').onclick = () => $('#pfd').close();
        $('#pfd').addEventListener('click', e => { const d = $('#pfd'), r = d.getBoundingClientRect(); if (e.target === d && (e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom)) d.close() });   /* only a click on the dark backdrop closes it, not the popup's own padding */
        $('#pfd').addEventListener('close', () => { $('#pfimg').removeAttribute('src') })
    }
    const stay = x => `<span class="nw">${x.checkin}</span> → <span class="nw">${x.checkout}</span>`;
    const acts = h => h ? `<div class="acts">${h}</div>` : '';
    const reviewBtns = p => acts(`<button class="btn sm" data-pid="${p.id}" data-s="verified">Verify</button>` + btn('pid', p.id, 'Reject', 'rejected'));
    /* contact line under a guest name: show email AND phone, since the search box looks at both */
    const contact = x => [x.email, x.phone].filter(Boolean).map(esc).join(' / ');

    /* reservations */
    const refundCell = x => {
        if (x.status != 'cancelled') return '';
        return x.refund_status == 'pending' ? `<span class="s pending">Refund pending</span> ${peso(x.refund_amount)}` : x.refund_status == 'refunded' ? `<span class="s approved">Refunded</span> ${peso(x.refund_amount)}` : +x.paid > 0 ? `<span class="mu">No refund (kept ${peso(x.paid)})</span>` : '<span class="mu">-</span>'
    };
    const balCell = x => x.status == 'approved' || x.status == 'completed' ? (+x.balance > 0 ? `<b class="err">${peso(x.balance)}</b>` : '<span class="mu">paid</span>') : '<span class="mu">-</span>';
    const actions = x => {
        let h = '';
        if (x.status == 'pending') h += '<small class="mu">Awaiting payment</small> ';
        if ((x.status == 'approved' || x.status == 'completed') && +x.balance > 0) h += btn('bal', x.id, 'Collect balance') + ' ';
        /* Complete only once the balance has been collected */
        if (x.status == 'approved' && +x.balance <= 0) h += btn('id', x.id, 'Complete', 'completed') + ' ';
        if (x.status == 'pending' || x.status == 'approved') h += btn('id', x.id, 'Cancel', 'cancelled');
        /* Refund only while a refund is actually still owed: not after it was paid out, not when the hotel keeps the money */
        if (x.status == 'cancelled' && x.refund_status == 'pending') h += btn('rf', x.id, 'Refund');
        return acts(h)
    };
    const drawRes = () => {
        if (!$('#res')) return;
        const f = $('#rfilter').value, rows = RES.filter(x => !f || x.status == f);
        $('#res').innerHTML = '<thead><tr><th>Reference No.</th><th>Reserved by</th><th>Room</th><th>Stay</th><th>Pax</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th>Refund</th><th>Actions</th></tr></thead><tbody>' +
            (rows.map(x => `<tr><td>${x.ref}${x.source == 'walkin' ? ' <span class="tag">walk-in</span>' : ''}</td><td>${esc(x.guest)}<br><small class="mu">${contact(x)}</small></td><td>${esc(x.room)}</td><td class="wrapok">${stay(x)}</td><td>${x.pax}</td><td>${peso(x.total)}</td><td>${peso(x.paid)}</td><td>${balCell(x)}</td><td><span class="s ${x.status}">${x.status}</span>${x.cancel_reason == 'expired' ? '<br><small class="mu">expired, no payment</small>' : ''}</td><td class="wrapok">${refundCell(x)}</td><td>${actions(x)}</td></tr>`).join('') || `<tr><td colspan="11" class="mu">${SEARCH ? 'No reservations match your search' : 'No reservations to show'}</td></tr>`) + '</tbody>'
    };

    /* Overview: only items that still need action this month */
    const drawOvPays = rows => {
        const n = $('#ovpn'); n.hidden = !rows.length; n.textContent = rows.length;
        $('#ovpays').innerHTML = rows.length ? '<div class="tw"><table class="ovt"><thead><tr><th>Ref</th><th>Reserved by</th><th>Payment type</th><th>Payment ref no.</th><th>Amount paid</th><th>Proof</th><th>Actions</th></tr></thead><tbody>' +
            rows.map(p => `<tr><td>${p.ref}</td><td>${esc(p.guest)}</td><td>${payType(p)}${cashTag(p)}</td><td>${esc(p.ref_no)}</td><td>${peso(p.amount)}${shortTag(p)}</td><td>${proofCell(p)}</td><td>${reviewBtns(p)}</td></tr>`).join('') + '</tbody></table></div>'
            : '<p class="ovempty">All caught up. Nothing to verify this month.</p>'
    };
    const drawOvRes = rows => {
        $('#ovres').innerHTML = rows.length ? '<div class="tw"><table class="ovt"><thead><tr><th>Reference No.</th><th>Reserved by</th><th>Room</th><th>Stay</th><th>Paid</th><th>Balance</th><th>Status</th><th>Actions</th></tr></thead><tbody>' +
            rows.map(x => `<tr><td>${x.ref}${x.source == 'walkin' ? ' <span class="tag">walk-in</span>' : ''}</td><td>${esc(x.guest)}</td><td>${esc(x.room)}</td><td class="wrapok">${stay(x)}</td><td>${peso(x.paid)}</td><td>${balCell(x)}</td><td><span class="s ${x.status}">${x.status}</span>${x.status == 'cancelled' ? `<br><small class="mu">refund of ${peso(x.refund_amount)} due</small>` : ''}</td><td>${actions(x)}</td></tr>`).join('') + '</tbody></table></div>'
            : '<p class="ovempty">No reservations need action this month.</p>'
    };

    /* ---- analytics: dependency-free SVG line charts ---- */
    const niceMax = v => { const p = Math.pow(10, Math.floor(Math.log10(v))), f = v / p; return (f <= 1 ? 1 : f <= 2 ? 2 : f <= 2.5 ? 2.5 : f <= 5 ? 5 : 10) * p };
    const kfmt = v => { const a = Math.abs(v), g = a >= 1e6 ? +(a / 1e6).toFixed(1) + 'M' : a >= 1e3 ? +(a / 1e3).toFixed(1) + 'k' : String(Math.round(a)); return (v < 0 ? '-' : '') + '₱' + g };
    const lineChart = (labels, series, selI) => {
        const W = 640, H = 270, L = 58, R = 14, T = 14, B = 30, pw = W - L - R, ph = H - T - B, n = labels.length;
        const vals = series.flatMap(s => s.data).filter(v => v != null), mx = Math.max(0, ...vals), mn = Math.min(0, ...vals);
        const hi = mx > 0 ? niceMax(mx) : 1000, lo = mn < 0 ? -niceMax(-mn) : 0;
        const x = i => L + pw * (i + .5) / n, y = v => T + ph * (1 - (v - lo) / (hi - lo));
        let g = '';
        if (selI >= 0) g += `<rect class="chsel" x="${(L + pw * selI / n).toFixed(1)}" y="${T}" width="${(pw / n).toFixed(1)}" height="${ph}"/>`;
        for (let i = 0; i <= 4; i++) { const v = lo + (hi - lo) * i / 4, yy = y(v).toFixed(1); g += `<line class="gl" x1="${L}" x2="${W - R}" y1="${yy}" y2="${yy}"/><text x="${L - 7}" y="${(+yy + 4).toFixed(1)}" text-anchor="end">${kfmt(v)}</text>` }
        labels.forEach((l, i) => { g += `<text x="${x(i).toFixed(1)}" y="${H - 10}" text-anchor="middle">${l}</text>` });
        series.forEach(s => {
            let d = '', prev = false;
            s.data.forEach((v, i) => { if (v == null) { prev = false; return } d += `${prev ? 'L' : 'M'}${x(i).toFixed(1)},${y(v).toFixed(1)}`; prev = true });
            g += `<path class="ln ${s.cls}" d="${d}"/>` + s.data.map((v, i) => v == null ? '' : `<circle class="dot ${s.cls}" cx="${x(i).toFixed(1)}" cy="${y(v).toFixed(1)}" r="3"/>`).join('')
        });
        labels.forEach((l, i) => { g += `<rect class="chcol" x="${(L + pw * i / n).toFixed(1)}" y="${T}" width="${(pw / n).toFixed(1)}" height="${ph}"><title>${l}: ${series.slice().reverse().map(s => `${s.name} ${s.data[i] == null ? '-' : peso(s.data[i])}`).join(' | ')}</title></rect>` });
        return `<svg class="chart" viewBox="0 0 ${W} ${H}" role="img" aria-label="${series.map(s => s.name).join(' vs ')} revenue by month">${g}</svg>`
    };
    const drawCharts = (yy, sel, nowM) => {
        const nowY = +nowM.slice(0, 4), nowI = +nowM.slice(5) - 1, lastI = yy.year < nowY ? 11 : yy.year == nowY ? nowI : -1;
        const cur = yy.cur.map((v, i) => i <= lastI ? +v : null), prev = yy.prev.map(Number);
        const cum = a => { let t = 0; return a.map(v => v == null ? null : (t += v)) };
        const selI = +sel.slice(0, 4) == yy.year ? +sel.slice(5) - 1 : -1;
        const A = { name: String(yy.year), cls: 'a' }, B = { name: String(yy.year - 1), cls: 'b' };
        $('#chmonth').innerHTML = lineChart(MS, [{ ...B, data: prev }, { ...A, data: cur }], selI);
        $('#chcum').innerHTML = lineChart(MS, [{ ...B, data: cum(prev) }, { ...A, data: cum(cur) }], selI);
        const leg = `<i class="lg a"></i>${A.name}<i class="lg b"></i>${B.name}`; $('#anleg1').innerHTML = $('#anleg2').innerHTML = leg;
    };

    /* occupancy by room type for the selected month: number of bookings per room type (standard, family, event hall), same bar graph as the old "booked value by room type" */
    const drawOcc = o => {
        $('#anleg3').textContent = monthName(o.month); const mx = Math.max(1, ...o.rows.map(x => +x.bookings));
        $('#choc').innerHTML = o.rows.map(x => `<div class="bar"><span>${x.type}</span><i style="width:${x.bookings / mx * 100}%"></i><b>${x.bookings} <small class="mu">${x.bookings == 1 ? 'booking' : 'bookings'} (${x.nights} night${x.nights == 1 ? '' : 's'})</small></b></div>`).join('')
    };

    /* rooms: edit any rows, then ONE "Save All Changes" saves every modified row at once */
    const rv = (tr, n) => tr.querySelector(`[name=${n}]`);
    const readRoom = tr => ({ id: +tr.dataset.room, name: rv(tr, 'name').value.trim(), rate: rv(tr, 'rate').value, base_cap: rv(tr, 'base_cap').value, max_cap: rv(tr, 'max_cap').value, active: rv(tr, 'active').checked ? 1 : 0 });
    const roomChanged = d => { const m = ROOMS.find(x => x.id == d.id); return !!m && (d.name != m.name || +d.rate != +m.rate || +d.base_cap != +m.base_cap || +d.max_cap != +m.max_cap || d.active != (+m.active ? 1 : 0)) };
    const syncRooms = () => {
        if (!$('#rooms')) return; let n = 0;
        $$('#rooms tbody tr[data-room]').forEach(tr => { const c = roomChanged(readRoom(tr)); tr.classList.toggle('dirty', c); if (c) n++ });
        roomsDirty = n > 0; $('#rsave').disabled = !n; $('#rstate').textContent = n ? `${n} unsaved change${n > 1 ? 's' : ''}` : 'No unsaved changes'
    };
    const drawRooms = () => {
        if (roomsDirty || !$('#rooms')) return;
        $('#rooms').innerHTML = '<thead><tr><th>Name</th><th>Type</th><th>Today</th><th>Upcoming</th><th>Rate (₱/night)</th><th>Standard Capacity</th><th>Maximum Capacity</th><th>Show on Website</th><th></th></tr></thead><tbody>' +
            ROOMS.map(m => `<tr data-room="${m.id}"><td><input class="in" name="name" value="${esc(m.name)}" maxlength="30"></td><td class="cap">${m.type}</td><td><span class="s ${m.occ > 0 ? 'cancelled' : 'approved'}">${m.occ > 0 ? 'Occupied' : 'Available'}</span></td><td>${m.upcoming}</td><td><input class="in num" type="number" name="rate" min="1" value="${m.rate}"></td><td><input class="in num" type="number" name="base_cap" min="1" value="${m.base_cap}"></td><td><input class="in num" type="number" name="max_cap" min="1" value="${m.max_cap}"></td><td><input type="checkbox" name="active" ${+m.active ? 'checked' : ''} aria-label="Show on Website"></td><td>${acts(`<button class="btn sm ghost danger" type="button" data-del="${m.id}">Delete Room</button>`)}</td></tr>`).join('') + '</tbody>';
        syncRooms()
    };
    const fillWalkSelect = () => {
        if (!$('#wf')) return;
        const sel = $('#wf').room, opts = ROOMS.filter(m => +m.active).map(m => `<option value="${m.id}">${esc(m.name)} (${peso(m.rate)})</option>`).join('');
        if (sel.dataset.sig !== opts) { const v = sel.value; sel.innerHTML = opts; sel.dataset.sig = opts; if (v) sel.value = v }
    };

    const load = async () => {
        const my = ++seq, d = {}; if (ovSel) d.month = ovSel; if (SEARCH) d.q = SEARCH; const r = await post('dash', d);
        if (my != seq) return; /* a newer request (month change, poll) is already on its way */
        if (r.error) { $('#af-wrap').hidden = false; $('#dash').hidden = true; return }
        $('#af-wrap').hidden = true; $('#dash').hidden = false; const s = r.stats; RES = r.res; ROOMS = r.rooms;
        const occ = r.rooms.filter(m => m.occ > 0).length;
        NOWM = r.now_month; MAXM = r.max_month;
        if (ovSel && (ovSel == NOWM || ovSel > MAXM)) { ovSel = ovSel == NOWM ? null : MAXM; history.replaceState(null, '', ovSel ? '#' + ovSel : location.pathname) }
        if ($('#mnav')) { const m = curM(); $('#mlabel').textContent = monthName(m); $('#mnext').disabled = m >= MAXM; $('#mnow').hidden = ovSel === null }
        else if ($('#today')) $('#today').textContent = new Date().toLocaleDateString('en-PH', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        if ($('#stats')) $('#stats').innerHTML = [['Revenue received', peso(s.revenue), 'hi', 'admin-reports.php'], ['Payments to verify', s.pays_pending || 0, '', 'admin-payments.php'], ['Pending reservations', s.pending || 0, '', 'admin-reservations.php#pending'], ['Occupied today', ovSel === null ? occ + ' of ' + r.rooms.filter(m => +m.active).length : '-', '', 'admin-rooms.php'], ['Balance to collect', peso(s.balance_due), '', 'admin-reservations.php'], ['Refunds pending', +s.refunds_n || 0, '', 'admin-reservations.php#cancelled'], ['Approved', s.approved || 0, '', 'admin-reservations.php#approved'], ['Completed', s.completed || 0, '', 'admin-reservations.php#completed'], ['Cancelled', s.cancelled || 0, '', 'admin-reservations.php#cancelled'], ['Total bookings', s.bookings || 0, '', 'admin-reservations.php']].map(x => `<a class="kpi ${x[2]}" href="${x[3]}"><span>${x[0]}</span><b>${x[1]}</b></a>`).join('');
        if ($('#pays')) $('#pays').innerHTML = '<thead><tr><th>Ref</th><th>Guest</th><th>Type</th><th>Payment ref no.</th><th>Amount paid</th><th>Due</th><th>Proof</th><th>Status</th><th>Actions</th></tr></thead><tbody>' + (r.pays.map(p => `<tr><td>${p.ref}</td><td>${esc(p.guest)}<br><small class="mu">${contact(p)}</small></td><td>${payType(p)}${cashTag(p)}</td><td>${esc(p.ref_no)}</td><td>${peso(p.amount)}${shortTag(p)}</td><td>${p.kind == 'down' ? peso(p.downpayment) : '-'}</td><td>${proofCell(p)}</td><td><span class="s ${pc(p.status)}">${p.status}</span></td><td>${p.status == 'pending' ? reviewBtns(p) : acts(btn('rv', p.id, 'Revert to pending'))}</td></tr>`).join('') || `<tr><td colspan="9" class="mu">${SEARCH ? 'No payments match your search' : 'No payments submitted yet'}</td></tr>`) + '</tbody>';
        if ($('#ovpays')) drawOvPays(r.ov.pays);
        if ($('#ovres')) drawOvRes(r.ov.res);
        drawRes(); drawRooms(); fillWalkSelect(); if (window.loadRw) loadRw();
        if ($('#chmonth')) drawCharts(r.yearly, r.month, r.now_month);
        if ($('#choc')) drawOcc(r.occ);
    };

    af.onsubmit = async e => { e.preventDefault(); const r = await post('login', Object.fromEntries(new FormData(af))); $('#aerr').textContent = r.error || ''; if (!r.error) load() };
    $('#logout').onclick = async () => { await post('logout'); load() };
    if ($('#rfilter')) {
        const h = location.hash.slice(1); if (['pending', 'approved', 'completed', 'cancelled'].includes(h)) $('#rfilter').value = h;
        $('#rfilter').onchange = () => { history.replaceState(null, '', $('#rfilter').value ? '#' + $('#rfilter').value : location.pathname); drawRes() }
    }
    const payClick = async e => {
        const rv = e.target.closest('[data-rv]');
        if (rv) {
            if (!confirm('Set this payment back to pending? It will go back into the list of payments to verify.')) return;
            rv.disabled = true; const r = await post('pay_revert', { id: rv.dataset.rv }); if (r.error) alert(r.error); load(); return
        }
        const x = e.target.closest('[data-pid]'); if (x) { x.disabled = true; const r = await post('pay_status', { id: x.dataset.pid, s: x.dataset.s }); if (r.error) alert(r.error); load() }
    };
    ['#pays', '#ovpays'].forEach(k => { if ($(k)) $(k).onclick = payClick });

    /* reservation actions: complete, cancel, collect balance, refund */
    const rfd = $('#rfd'), rff = $('#rff'); let rfId = null;
    const resClick = async e => {
        const bal = e.target.closest('[data-bal]');
        if (bal) {
            const x = RES.find(r => r.id == bal.dataset.bal); if (!confirm(`Collect the remaining balance of ${peso(x.balance)} in cash for ${x.ref}?`)) return;
            const r = await post('collect_balance', { id: x.id }); if (r.error) alert(r.error); load(); return
        }
        const rf = e.target.closest('[data-rf]');
        if (rf) {
            const x = RES.find(r => r.id == rf.dataset.rf); rfId = x.id; rff.reset(); rff.st.value = x.refund_status == 'none' ? 'pending' : x.refund_status; rff.amount.value = +x.refund_amount > 0 ? x.refund_amount : x.paid; rff.note.value = x.refund_note || '';
            $('#rfinfo').textContent = `${x.ref} · ${x.guest} · received so far ${peso(x.paid)}`; $('#rferr').textContent = ''; rfd.showModal(); return
        }
        const x = e.target.closest('[data-id]'); if (!x) return;
        const row = RES.find(r => r.id == x.dataset.id);
        if (x.dataset.s == 'cancelled' && !confirm(+row.paid > 0 ? `Cancel ${row.ref}? ${peso(row.paid)} was already received, so it will be marked "refund pending".` : `Cancel ${row.ref}?`)) return;
        const r = await post('status', { id: x.dataset.id, s: x.dataset.s }); if (r.error) alert(r.error); load()
    };
    ['#res', '#ovres'].forEach(k => { if ($(k)) $(k).onclick = resClick });
    if (rff) rff.st.onchange = () => { rff.amount.disabled = rff.st.value == 'none' };
    if (rff) rff.onsubmit = async e => {
        e.preventDefault(); const r = await post('refund', { id: rfId, st: rff.st.value, amount: rff.amount.value || 0, note: rff.note.value });
        if (r.error) { $('#rferr').textContent = r.error; return } rfd.close(); load()
    };
    if ($('#rfx')) $('#rfx').onclick = () => rfd.close();

    /* rooms and rates */
    if ($('#rooms')) {
        $('#rooms').addEventListener('input', syncRooms); $('#rooms').addEventListener('change', syncRooms);
        $('#rsave').onclick = async () => {
            const rows = $$('#rooms tbody tr[data-room]').map(readRoom).filter(roomChanged); if (!rows.length) return;
            const off = rows.filter(d => { const m = ROOMS.find(x => x.id == d.id); return !d.active && +m.active && +m.upcoming > 0 });
            if (off.length && !confirm(`${off.length} room(s) being switched off have upcoming bookings. They stay valid, but new guests will not be able to book those rooms. Save anyway?`)) return;
            $('#rsave').disabled = true; const r = await post('rooms_save', { rows: JSON.stringify(rows) });
            $('#rmsg').textContent = r.error || ''; if (r.error) { syncRooms(); return }
            roomsDirty = false; await load(); $('#rstate').textContent = `All changes saved (${r.n} room${r.n > 1 ? 's' : ''})`
        };
        $('#rooms').onclick = async e => {
            const b = e.target.closest('[data-del]'); if (!b) return; const tr = b.closest('tr'), name = rv(tr, 'name').value.trim() || 'this room';
            if (!confirm(`Delete ${name}? Guests will no longer be able to book it. Past bookings and reports keep its name.`)) return;
            b.disabled = true; const r = await post('room_delete', { id: b.dataset.del }); $('#rmsg').textContent = r.error || '';
            if (r.error) { b.disabled = false; return }
            ROOMS = ROOMS.filter(m => m.id != b.dataset.del); tr.remove(); syncRooms(); load()
        }
    }
    if ($('#raf')) $('#raf').onsubmit = async e => {
        e.preventDefault(); if (roomsDirty && !confirm('You have unsaved changes in the table. Adding a room reloads it and they will be lost. Continue?')) return;
        const f = e.target, r = await post('room_add', Object.fromEntries(new FormData(f))); $('#rmsg').textContent = r.error || ''; if (!r.error) { f.reset(); roomsDirty = false; load() }
    };


    /* rewards: points rate + wheel rewards + reward types.
       Adding, editing and removing wheel rewards are staged in the table; ONE "Save Reward Settings" saves the points rate and the whole list together
       (the server checks that the enabled rewards add up to exactly 100%). Reward types are saved straight away with their own buttons. */
    if ($('#rwt')) {
        const BASEL = { pct: 'Discount voucher (% off)', free_room: 'Free room stay (room + nights)', vip: 'VIP status (discount on every booking)', free_food: 'Free food (claimed at the front desk)' };
        let W = [], BASE = '', BASEROWS = {}, KNOWN = [], MAXW = 8, MAXN = 30, TYPES = [], ROOMOPTS = [], uid = 0, rwDirty = false;
        const baseOf = kind => (TYPES.find(t => t.code == kind) || {}).base || kind;
        const roomOk = v => v == 'any' || v == 'standard' || v == 'family' || ROOMOPTS.some(m => String(m.id) == String(v));
        const roomWord = v => v == 'standard' ? 'Standard Room' : v == 'family' ? 'Family Room' : v == 'any' ? 'Room' : ((ROOMOPTS.find(m => String(m.id) == String(v)) || {}).name || '');
        /* suggested name for a free-room reward: it says WHICH room and HOW LONG, e.g. "Free Room A stay, 3 nights" or "Free Family Room, 2 nights" */
        const autoName = r => {
            if (baseOf(r.kind) != 'free_room' || !roomOk(r.room) || !(+r.nights >= 1)) return '';
            const n = +r.nights, spec = !['any', 'standard', 'family'].includes(String(r.room));
            return `Free ${roomWord(r.room)}${spec ? ' stay' : ''}, ${n} night${n == 1 ? '' : 's'}`
        };
        const norm = r => { const b = baseOf(r.kind); return { id: r.id, label: String(r.label).trim(), kind: r.kind, pct: b == 'pct' || b == 'vip' ? (+r.pct || 0) : 0, room: b == 'free_room' ? String(r.room || '') : 'any', nights: b == 'free_room' ? (+r.nights || 0) : 2, chance: +r.chance || 0, active: r.active ? 1 : 0 } };
        const pctVal = () => $('#rwpct').value === '' ? 'x' : +$('#rwpct').value;
        const rwSig = () => JSON.stringify(W.map(norm)) + '|' + pctVal();
        const hund = r => Math.round((+r.chance || 0) * 100);
        const fmtPct = h => String(+(h / 100).toFixed(2));
        const typeOpts = sel => TYPES.map(t => `<option value="${esc(t.code)}"${t.code == sel ? ' selected' : ''}>${esc(t.label)}</option>`).join('');
        const roomSelect = sel => (roomOk(sel) ? '' : '<option value="" selected>Choose a room...</option>') +
            [['any', 'Any Standard or Family room'], ['standard', 'Any Standard room'], ['family', 'Any Family room']].map(o => `<option value="${o[0]}"${o[0] == sel ? ' selected' : ''}>${o[1]}</option>`).join('') +
            (ROOMOPTS.length ? '<optgroup label="Specific room">' + ROOMOPTS.map(m => `<option value="${m.id}"${String(m.id) == String(sel) ? ' selected' : ''}>${esc(m.name)} (${m.type}${+m.active ? '' : ', closed'})</option>`).join('') + '</optgroup>' : '');
        /* the "Details" cell depends on what the reward type does: a discount %, a room + number of nights, or nothing */
        const detail = r => {
            const b = baseOf(r.kind);
            if (b == 'pct') return `<input class="in num" type="number" name="pct" min="1" max="100" step="1" value="${esc(r.pct)}" aria-label="Discount percent"> <span class="mu">% off</span>`;
            if (b == 'vip') return `<input class="in num" type="number" name="pct" min="1" max="100" step="1" value="${esc(r.pct)}" aria-label="VIP discount percent"> <span class="mu">% off every booking</span>`;
            if (b == 'free_food') return '<span class="mu">Name the food in the reward name</span>';
            if (b == 'free_room') return `<div class="rwdet"><select class="in" name="room" aria-label="Room">${roomSelect(r.room)}</select><span class="rwn"><input class="in num" type="number" name="nights" min="1" max="${MAXN}" step="1" value="${esc(r.nights)}" aria-label="Free nights"> night(s)</span></div>`;
            return '<span class="mu">No extra settings</span>'
        };
        const rwRow = r => `<tr data-k="${r.k}"><td><input class="in" name="label" value="${esc(r.label)}" maxlength="60" aria-label="Reward name"></td><td><select class="in" name="kind" aria-label="Reward type">${typeOpts(r.kind)}</select></td><td>${detail(r)}</td><td><input class="in num" type="number" name="chance" min="0" max="100" step="0.01" value="${esc(+r.chance)}" aria-label="Probability percent"></td><td><input type="checkbox" name="active"${r.active ? ' checked' : ''} aria-label="Enabled"></td><td>${acts('<button class="btn sm ghost danger" type="button" data-rwdel>Remove</button>')}</td></tr>`;
        const syncRw = () => {
            const on = W.filter(r => r.active), tot = on.reduce((t, r) => t + hund(r), 0), ok = tot == 10000 && on.length >= 2 && on.length <= MAXW;
            $$('#rwt tbody tr[data-k]').forEach(tr => { const r = W.find(x => x.k == tr.dataset.k); tr.classList.toggle('dirty', !!r && (!r.id || JSON.stringify(norm(r)) != BASEROWS[r.id])) });
            const t = $('#rwtotal'); t.className = 'rwtotal ' + (ok ? 'ok' : 'err');
            t.textContent = `Enabled rewards: ${on.length} (2 to ${MAXW}) · Total probability: ${fmtPct(tot)}%` + (tot == 10000 ? '' : ' - the enabled rewards must add up to exactly 100%');
            const p = +$('#rwpct').value; $('#rwhint').textContent = $('#rwpct').value === '' ? '' : `A guest earns ${p}% of the stay total when it is completed. Example: a ₱10,000 stay gives ${Math.floor(Math.round(10000 * p) / 100)} points.`;
            rwDirty = rwSig() != BASE; $('#rwsave').disabled = !rwDirty; $('#rwdiscard').disabled = !rwDirty;
            $('#rwstate').textContent = rwDirty ? 'Unsaved changes' : 'No unsaved changes'
        };
        const drawRw = () => {
            $('#rwt').innerHTML = '<thead><tr><th>Wheel reward</th><th>Reward type</th><th>Discount, or room and nights</th><th>Probability (%)</th><th>Enabled</th><th></th></tr></thead><tbody>' + (W.map(rwRow).join('') || '<tr><td colspan="6" class="mu">No rewards yet. Add one below.</td></tr>') + '</tbody>';
            syncRw()
        };

        /* ---- reward types: saved straight away (not part of Save Reward Settings) ---- */
        const typeUse = code => W.filter(r => r.kind == code).length;
        const drawTypes = () => {
            $('#rtt').innerHTML = '<thead><tr><th>Type name</th><th>What it does</th><th>Rewards using it</th><th></th></tr></thead><tbody>' + TYPES.map(t => `<tr data-code="${esc(t.code)}"><td><input class="in" name="tlabel" value="${esc(t.label)}" maxlength="60" aria-label="Type name"></td><td>${BASEL[t.base] || esc(t.base)}${+t.builtin ? ' <span class="tag">built-in</span>' : ''}</td><td>${typeUse(t.code)}</td><td>${acts('<button class="btn sm" type="button" data-tsave disabled>Save name</button>' + (+t.builtin ? '' : '<button class="btn sm ghost danger" type="button" data-tdel>Delete</button>'))}</td></tr>`).join('') + '</tbody>'
        };
        $('#rtt').addEventListener('input', e => {
            const tr = e.target.closest('tr[data-code]'); if (!tr) return;
            const t = TYPES.find(x => x.code == tr.dataset.code), ch = !!t && e.target.value.trim() != t.label;
            tr.classList.toggle('dirty', ch); tr.querySelector('[data-tsave]').disabled = !ch
        });
        const typesChanged = r => { TYPES = r.types; drawTypes(); drawRw(); fillAf() };
        $('#rtt').onclick = async e => {
            const tr = e.target.closest('tr[data-code]'), b = e.target.closest('button'); if (!tr || !b) return;
            const code = tr.dataset.code, msg = $('#rtmsg'), t = TYPES.find(x => x.code == code); if (!t) return;
            if (b.hasAttribute('data-tsave')) {
                b.disabled = true; const r = await post('type_save', { code, label: tr.querySelector('[name=tlabel]').value });
                msg.className = r.error ? 'err' : 'ok'; msg.textContent = r.error || 'Reward type saved'; if (r.error) { b.disabled = false; return }
                typesChanged(r)
            } else if (b.hasAttribute('data-tdel')) {
                if (typeUse(code)) { msg.className = 'err'; msg.textContent = `"${t.label}" is still used by a reward in the table above. Change or remove those rewards first.`; return }
                if (!confirm(`Delete the reward type "${t.label}"?`)) return;
                b.disabled = true; const r = await post('type_delete', { code });
                msg.className = r.error ? 'err' : 'ok'; msg.textContent = r.error || 'Reward type deleted'; if (r.error) { b.disabled = false; return }
                typesChanged(r)
            }
        };

        /* ---- "Add a reward" form: the fields that apply to the chosen type are shown, and the name is suggested from the room and nights ---- */
        const af = $('#rwaf');
        const afAuto = () => autoName({ kind: af.kind.value, room: af.room.value, nights: af.nights.value });
        const syncAf = e => {
            const b = baseOf(af.kind.value); $$('#rwaf [data-for]').forEach(l => l.hidden = !l.dataset.for.split(' ').includes(b));
            if (e && ['kind', 'room', 'nights'].includes(e.target.name)) {
                const cur = af.label.value.trim();
                if (!cur || cur == af.dataset.auto) { const nn = afAuto(); af.label.value = nn; af.dataset.auto = nn }
            }
        };
        function fillAf() {
            const sig = JSON.stringify([TYPES, ROOMOPTS]); if (af.dataset.sig == sig) return;
            const kv = af.kind.value, rvv = af.room.value;
            af.kind.innerHTML = typeOpts(kv || 'pct'); af.room.innerHTML = roomSelect(rvv || 'any'); af.dataset.sig = sig; af.nights.max = MAXN; syncAf()
        }
        af.addEventListener('input', syncAf); af.addEventListener('change', syncAf);

        const loadRw = async force => {
            if (rwDirty && !force) return;
            const r = await post('rewards_get'); if (r.error) return;
            MAXW = r.max_wheel; MAXN = r.max_nights || 30; TYPES = r.types; ROOMOPTS = r.rooms; KNOWN = r.prizes.map(p => +p.id); BASEROWS = {};
            W = r.prizes.map(p => ({ k: ++uid, id: +p.id, label: p.label, kind: p.kind, pct: +p.pct, room: p.room_id ? String(p.room_id) : (p.room_type || 'any'), nights: +p.nights || 2, chance: p.chance, active: +p.active }));
            W.forEach(x => BASEROWS[x.id] = JSON.stringify(norm(x)));
            $('#rwpct').value = +r.percent; BASE = rwSig(); drawRw(); fillAf(); if (!$('#rtt tr.dirty')) drawTypes()
        };
        $('#rwt').addEventListener('input', e => rwEdit(e)); $('#rwt').addEventListener('change', e => rwEdit(e));
        function rwEdit(e) {
            const el = e.target, tr = el.closest('tr[data-k]'); if (!tr) return;
            const r = W.find(x => x.k == tr.dataset.k); if (!r) return;
            const old = autoName(r), wasAuto = !r.label.trim() || r.label.trim() == old;   /* is the name still the suggested one? then it follows the room/nights */
            const vipAuto = baseOf(r.kind) == 'vip' && /^VIP status \([\d.]+% off every booking\)$/.test(r.label.trim());   /* a VIP name that quotes the discount follows the number */
            const q = n => tr.querySelector(`[name=${n}]`), v = n => { const x = q(n); return x ? x.value : undefined };
            r.label = v('label'); r.kind = v('kind'); r.chance = v('chance'); r.active = q('active').checked ? 1 : 0;
            if (v('pct') !== undefined) r.pct = v('pct'); if (v('room') !== undefined) r.room = v('room'); if (v('nights') !== undefined) r.nights = v('nights');
            if (el.name == 'kind') {
                const b = baseOf(r.kind);
                if (b == 'pct' && !+r.pct) r.pct = 10;
                if (b == 'vip' && !+r.pct) r.pct = 5;
                if (b == 'free_room') { if (!roomOk(r.room)) r.room = 'any'; if (!(+r.nights >= 1)) r.nights = 2 }
            }
            if (['kind', 'room', 'nights'].includes(el.name)) { const nn = autoName(r); if (nn && wasAuto) r.label = nn }
            if (el.name == 'pct' && vipAuto && +r.pct >= 1) { r.label = `VIP status (${+r.pct}% off every booking)`; const li = q('label'); if (li) li.value = r.label }
            if (el.name == 'kind') { drawRw(); return }   /* a different type shows different fields */
            if (el.name == 'room' || el.name == 'nights') { const li = q('label'); if (li && li.value != r.label) li.value = r.label }
            syncRw()
        }
        $('#rwt').onclick = e => {
            const b = e.target.closest('[data-rwdel]'); if (!b) return;
            const k = b.closest('tr').dataset.k, r = W.find(x => x.k == k); W = W.filter(x => x.k != k);
            drawRw(); drawTypes(); $('#rwmsg').textContent = ''; $('#rwstate').textContent = `"${r.label.trim() || 'Reward'}" removed. It is deleted when you press Save Reward Settings.`
        };
        $('#rwpct').addEventListener('input', syncRw);
        af.onsubmit = e => {
            e.preventDefault(); const b = baseOf(af.kind.value), label = af.label.value.trim(), msg = $('#rwmsg');
            if (W.some(r => r.label.trim().toLowerCase() == label.toLowerCase())) { msg.textContent = 'A reward with that name already exists'; return }
            if (b == 'vip' && !(+af.pct.value >= 1 && +af.pct.value <= 100)) { msg.textContent = 'The VIP discount must be from 1% to 100%'; return }
            if (b == 'free_room' && !roomOk(af.room.value)) { msg.textContent = 'Choose which room the free stay is for'; return }
            if (b == 'free_room' && !(Number.isInteger(+af.nights.value) && +af.nights.value >= 1 && +af.nights.value <= MAXN)) { msg.textContent = `Free nights must be a whole number from 1 to ${MAXN}`; return }
            W.push({ k: ++uid, id: 0, label, kind: af.kind.value, pct: b == 'pct' || b == 'vip' ? +af.pct.value : 0, room: b == 'free_room' ? af.room.value : 'any', nights: b == 'free_room' ? +af.nights.value : 2, chance: af.chance.value || 0, active: 1 });
            af.reset(); af.dataset.sig = ''; af.dataset.auto = ''; af.kind.innerHTML = ''; af.room.innerHTML = ''; fillAf();
            msg.textContent = ''; drawRw(); drawTypes();
            $('#rwstate').textContent = `"${label}" added to the table. Set its probability, then press Save Reward Settings.`
        };
        const rwCheck = () => {
            const names = new Set(); let on = 0, tot = 0;
            for (const r of W) {
                const n = r.label.trim(); if (!n) return 'Every reward needs a name';
                if (names.has(n.toLowerCase())) return `Two rewards cannot have the same name (${n})`; names.add(n.toLowerCase());
                if (!TYPES.some(t => t.code == r.kind)) return `${n}: choose a reward type`;
                const b = baseOf(r.kind);
                if (b == 'pct' && !(+r.pct >= 1 && +r.pct <= 100)) return `${n}: the discount must be from 1% to 100%`;
                if (b == 'vip' && !(+r.pct >= 1 && +r.pct <= 100)) return `${n}: the VIP discount must be from 1% to 100%`;
                if (b == 'free_room') {
                    if (!roomOk(r.room)) return `${n}: choose which room the free stay is for`;
                    if (!(Number.isInteger(+r.nights) && +r.nights >= 1 && +r.nights <= MAXN)) return `${n}: free nights must be a whole number from 1 to ${MAXN}`
                }
                if (r.chance === '' || isNaN(+r.chance) || +r.chance < 0 || +r.chance > 100) return `${n}: the probability must be a number from 0 to 100`;
                if (Math.abs(+r.chance * 100 - hund(r)) > 1e-6) return `${n}: use at most 2 decimal places for the probability`;
                if (r.active) { if (hund(r) < 1) return `${n}: an enabled reward needs a probability above 0`; on++; tot += hund(r) }
            }
            if ($('#rwpct').value === '' || +$('#rwpct').value < 0 || +$('#rwpct').value > 100) return 'Points earned must be a number from 0 to 100 (% of the amount spent)';
            if (on < 2) return 'Enable at least 2 rewards so the wheel has something to spin';
            if (on > MAXW) return `The wheel can show at most ${MAXW} enabled rewards. Switch some off.`;
            if (tot != 10000) return `The probabilities of the enabled rewards must add up to exactly 100% (they add up to ${fmtPct(tot)}%)`;
            return ''
        };
        $('#rwsave').onclick = async () => {
            const bad = rwCheck(); $('#rwmsg').textContent = bad; if (bad) return;
            $('#rwsave').disabled = true; $('#rwdiscard').disabled = true;
            const r = await post('rewards_save', { percent: $('#rwpct').value, rows: JSON.stringify(W.map(norm)), known: JSON.stringify(KNOWN) });
            $('#rwmsg').textContent = r.error || ''; if (r.error) { syncRw(); return }
            rwDirty = false; await loadRw(true); $('#rwstate').textContent = 'All changes saved'
        };
        $('#rwdiscard').onclick = async () => { $('#rwmsg').textContent = ''; await loadRw(true) };
        window.loadRw = loadRw
    }

    /* walk-in booking with live price preview */
    if ($('#wf')) {
    const wf = $('#wf'), wq = $('#wq'), wmsg = $('#wmsg');
    wf.ci.value = today(); wf.co.value = addDays(today(), 1); wf.ci.min = today();
    const quote = async () => {
        const d = { room: wf.room.value, ci: wf.ci.value, co: wf.co.value, pax: wf.pax.value }; if (!d.room || !d.ci || !d.co) { wq.hidden = true; return }
        const r = await post('wk_quote', d); wq.hidden = false; wq.classList.toggle('warn', !!r.error || !r.free);
        if (r.error) { wq.textContent = r.error; return }
        wq.textContent = `${r.nights} night(s) · total ${peso(r.total)} · 50% downpayment ${peso(r.down)}` + (r.free ? '' : ' · NOT AVAILABLE for these dates'); wf.amount.placeholder = 'Downpayment ' + peso(r.down)
    };
    ['room', 'ci', 'co', 'pax'].forEach(k => wf[k].addEventListener('change', quote)); setTimeout(quote, 600);
    wf.onsubmit = async e => {
        e.preventDefault(); wmsg.textContent = ''; wmsg.className = 'err'; const r = await post('walkin', Object.fromEntries(new FormData(wf)));
        if (r.error) { wmsg.textContent = r.error; return }
        wmsg.className = 'ok'; wmsg.textContent = `Booked ${r.ref}. Total ${peso(r.total)}, received ${peso(r.paid)}, balance ${peso(r.balance)}.`;
        wf.name.value = wf.phone.value = wf.email.value = wf.amount.value = ''; wf.pax.value = 2; load().then(quote)
    };
    }

    /* report form defaults: this month */
    const rp = $('#rpf'), d0 = new Date(); if (rp) { rp.from.value = `${d0.getFullYear()}-${String(d0.getMonth() + 1).padStart(2, '0')}-01`; rp.to.value = today() }

    /* search boxes (Payments and Reservations pages): typing re-queries the server a moment after the last key */
    ['#psearch', '#rsearch'].forEach(k => { const i = $(k); if (!i) return; let t; i.addEventListener('input', () => { clearTimeout(t); t = setTimeout(() => { SEARCH = i.value.trim(); load() }, 250) }) });

    /* month arrows (Overview) */
    if ($('#mnav')) { $('#mprev').onclick = () => goMonth(shiftM(curM(), -1)); $('#mnext').onclick = () => goMonth(shiftM(curM(), 1)); $('#mnow').onclick = () => goMonth(null) }

    load(); setInterval(() => { if (!$('#dash').hidden) load() }, 15000);
}
