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
    const proofCell = p => p.method == 'cash' ? '<span class="mu">Received in person</span>' : `<a href="proof.php?id=${p.id}" target="_blank">View proof</a>`;
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
        const k = lastI + 1, sum = a => a.reduce((t, v) => t + v, 0), c = sum(cur.slice(0, k)), p = sum(prev.slice(0, k));
        const delta = p > 0 ? ` <b class="${c >= p ? 'up' : 'dn'}">${c >= p ? '▲' : '▼'} ${Math.abs(Math.round((c - p) / p * 100))}%</b>` : '';
        $('#anyear').innerHTML = k ? `${A.name}: <b>${peso(c)}</b> so far · ${B.name} same months: ${peso(p)}${delta}` : `No revenue recorded yet in ${A.name}`
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
        $('#rooms').innerHTML = '<thead><tr><th>Name</th><th>Type</th><th>Today</th><th>Upcoming</th><th>Rate (₱/night)</th><th>Std cap</th><th>Max cap</th><th>On website</th><th></th></tr></thead><tbody>' +
            ROOMS.map(m => `<tr data-room="${m.id}"><td><input class="in" name="name" value="${esc(m.name)}" maxlength="30"></td><td class="cap">${m.type}</td><td><span class="s ${m.occ > 0 ? 'cancelled' : 'approved'}">${m.occ > 0 ? 'Occupied' : 'Available'}</span></td><td>${m.upcoming}</td><td><input class="in num" type="number" name="rate" min="1" value="${m.rate}"></td><td><input class="in num" type="number" name="base_cap" min="1" value="${m.base_cap}"></td><td><input class="in num" type="number" name="max_cap" min="1" value="${m.max_cap}"></td><td><input type="checkbox" name="active" ${+m.active ? 'checked' : ''} aria-label="Show on website"></td><td>${acts(`<button class="btn sm ghost danger" type="button" data-del="${m.id}">Delete Room</button>`)}</td></tr>`).join('') + '</tbody>';
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
        drawRes(); drawRooms(); fillWalkSelect();
        if ($('#bytype')) { const mx = Math.max(1, ...r.byType.map(x => +x.rev));
        $('#bytype').innerHTML = r.byType.map(x => `<div class="bar"><span>${x.type}</span><i style="width:${x.rev / mx * 100}%"></i><b>${peso(x.rev)} <small class="mu">(${x.n})</small></b></div>`).join('') || '<p class="mu">No approved bookings this month</p>' }
        if ($('#chmonth')) drawCharts(r.yearly, r.month, r.now_month);
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
