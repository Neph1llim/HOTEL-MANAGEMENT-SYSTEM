/* ---- admin dashboard (uses $, post, peso, esc from app.js) ---- */
if ($('#dash')) {
    const af = $('#af'), pc = s => s == 'verified' ? 'approved' : s == 'rejected' ? 'cancelled' : s;
    const btn = (k, id, l, s) => `<button class="btn sm ghost" data-${k}="${id}"${s ? ` data-s="${s}"` : ''}>${l}</button>`;
    let RES = [], ROOMS = [], roomsDirty = false;
    const today = () => { const d = new Date(); return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}` };
    const addDays = (s, n) => { const d = new Date(s + 'T00:00'); d.setDate(d.getDate() + n); return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}` };

    /* reservations */
    const refundCell = x => {
        if (x.status != 'cancelled') return '';
        return x.refund_status == 'pending' ? `<span class="s pending">Refund pending</span> ${peso(x.refund_amount)}` : x.refund_status == 'refunded' ? `<span class="s approved">Refunded</span> ${peso(x.refund_amount)}` : +x.paid > 0 ? `<span class="mu">No refund (kept ${peso(x.paid)})</span>` : '<span class="mu">-</span>'
    };
    const actions = x => {
        let h = '';
        if (x.status == 'pending') h += '<small class="mu">Awaiting payment</small> ';
        if ((x.status == 'approved' || x.status == 'completed') && +x.balance > 0) h += btn('bal', x.id, 'Collect balance') + ' ';
        if (x.status == 'approved') h += btn('id', x.id, 'Complete', 'completed') + ' ';
        if (x.status == 'pending' || x.status == 'approved') h += btn('id', x.id, 'Cancel', 'cancelled');
        if (x.status == 'cancelled' && (+x.paid > 0 || x.refund_status != 'none')) h += btn('rf', x.id, 'Refund');
        return h
    };
    const drawRes = () => {
        const f = $('#rfilter').value, rows = RES.filter(x => !f || x.status == f);
        $('#res').innerHTML = '<thead><tr><th>Ref</th><th>Guest</th><th>Room</th><th>Stay</th><th>Pax</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th>Refund</th><th>Actions</th></tr></thead><tbody>' +
            (rows.map(x => `<tr><td>${x.ref}${x.source == 'walkin' ? ' <span class="tag">walk-in</span>' : ''}</td><td>${esc(x.guest)}<br><small class="mu">${esc(x.email || x.phone || '')}</small></td><td>${esc(x.room)}</td><td>${x.checkin} → ${x.checkout}</td><td>${x.pax}</td><td>${peso(x.total)}</td><td>${peso(x.paid)}</td><td>${x.status == 'approved' || x.status == 'completed' ? (+x.balance > 0 ? `<b class="err">${peso(x.balance)}</b>` : '<span class="mu">paid</span>') : '<span class="mu">-</span>'}</td><td><span class="s ${x.status}">${x.status}</span>${x.cancel_reason == 'expired' ? '<br><small class="mu">expired, no payment</small>' : ''}</td><td>${refundCell(x)}</td><td>${actions(x)}</td></tr>`).join('') || '<tr><td colspan="11" class="mu">No reservations to show</td></tr>') + '</tbody>'
    };

    /* rooms */
    const drawRooms = () => {
        if (roomsDirty) return;
        $('#rooms').innerHTML = '<thead><tr><th>Name</th><th>Type</th><th>Today</th><th>Upcoming</th><th>Rate (₱/night)</th><th>Std cap</th><th>Max cap</th><th>On website</th><th></th></tr></thead><tbody>' +
            ROOMS.map(m => `<tr data-room="${m.id}"><td><input class="in" name="name" value="${esc(m.name)}" maxlength="30"></td><td class="cap">${m.type}</td><td><span class="s ${m.occ > 0 ? 'cancelled' : 'approved'}">${m.occ > 0 ? 'Occupied' : 'Available'}</span></td><td>${m.upcoming}</td><td><input class="in num" type="number" name="rate" min="1" value="${m.rate}"></td><td><input class="in num" type="number" name="base_cap" min="1" value="${m.base_cap}"></td><td><input class="in num" type="number" name="max_cap" min="1" value="${m.max_cap}"></td><td><input type="checkbox" name="active" ${+m.active ? 'checked' : ''} aria-label="Show on website"></td><td><button class="btn sm" data-save="${m.id}" data-up="${m.upcoming}">Save</button></td></tr>`).join('') + '</tbody>'
    };
    const fillWalkSelect = () => {
        const sel = $('#wf').room, opts = ROOMS.filter(m => +m.active).map(m => `<option value="${m.id}">${esc(m.name)} (${peso(m.rate)})</option>`).join('');
        if (sel.dataset.sig !== opts) { const v = sel.value; sel.innerHTML = opts; sel.dataset.sig = opts; if (v) sel.value = v }
    };

    const load = async () => {
        const r = await post('dash');
        if (r.error) { $('#af-wrap').hidden = false; $('#dash').hidden = true; return }
        $('#af-wrap').hidden = true; $('#dash').hidden = false; const s = r.stats, pp = r.pays.filter(p => p.status == 'pending').length, occ = r.rooms.filter(m => m.occ > 0).length; RES = r.res; ROOMS = r.rooms;
        $('#today').textContent = new Date().toLocaleDateString('en-PH', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        $('#stats').innerHTML = [['Revenue received', peso(s.revenue), 'hi'], ['Payments to verify', pp], ['Pending reservations', s.pending || 0], ['Occupied today', occ + ' of ' + r.rooms.filter(m => +m.active).length], ['Balance to collect', peso(s.balance_due)], ['Refunds pending', s.refunds_n ? s.refunds_n + ' (' + peso(s.refunds_amt) + ')' : '0'], ['Approved', s.approved || 0], ['Completed', s.completed || 0], ['Cancelled', s.cancelled || 0], ['Total bookings', s.bookings || 0]].map(x => `<div class="kpi ${x[2] || ''}"><span>${x[0]}</span><b>${x[1]}</b></div>`).join('');
        $('#pays').innerHTML = '<thead><tr><th>Ref</th><th>Guest</th><th>Type</th><th>Payment ref no.</th><th>Amount paid</th><th>Due</th><th>Proof</th><th>Status</th><th>Actions</th></tr></thead><tbody>' + (r.pays.map(p => `<tr><td>${p.ref}</td><td>${esc(p.guest)}</td><td>${p.kind == 'balance' ? 'Balance' : 'Downpayment'}${p.method == 'cash' ? ' <span class="tag">cash</span>' : ''}</td><td>${esc(p.ref_no)}</td><td>${peso(p.amount)}${p.kind == 'down' && p.method != 'cash' && +p.amount < +p.downpayment ? ' <small class="err">short</small>' : ''}</td><td>${p.kind == 'down' ? peso(p.downpayment) : '-'}</td><td>${p.method == 'cash' ? '<span class="mu">Received in person</span>' : `<a href="proof.php?id=${p.id}" target="_blank">View proof</a>`}</td><td><span class="s ${pc(p.status)}">${p.status}</span></td><td>${p.status == 'pending' ? `<button class="btn sm" data-pid="${p.id}" data-s="verified">Verify</button> ` + btn('pid', p.id, 'Reject', 'rejected') : ''}</td></tr>`).join('') || '<tr><td colspan="9" class="mu">No payments submitted yet</td></tr>') + '</tbody>';
        drawRes(); drawRooms(); fillWalkSelect();
        const mx = Math.max(1, ...r.byType.map(x => +x.rev));
        $('#bytype').innerHTML = r.byType.map(x => `<div class="bar"><span>${x.type}</span><i style="width:${x.rev / mx * 100}%"></i><b>${peso(x.rev)} <small class="mu">(${x.n})</small></b></div>`).join('') || '<p class="mu">No approved bookings yet</p>'
    };

    af.onsubmit = async e => { e.preventDefault(); const r = await post('login', Object.fromEntries(new FormData(af))); $('#aerr').textContent = r.error || ''; if (!r.error) load() };
    $('#logout').onclick = async () => { await post('logout'); load() };
    $('#rfilter').onchange = drawRes;
    $('#pays').onclick = async e => { const x = e.target.closest('[data-pid]'); if (x) { x.disabled = true; await post('pay_status', { id: x.dataset.pid, s: x.dataset.s }); load() } };

    /* reservation actions: complete, cancel, collect balance, refund */
    const rfd = $('#rfd'), rff = $('#rff'); let rfId = null;
    $('#res').onclick = async e => {
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
        if (x.dataset.s == 'completed' && +row.balance > 0 && !confirm(`${row.ref} still has an unpaid balance of ${peso(row.balance)}. Complete it anyway?`)) return;
        const r = await post('status', { id: x.dataset.id, s: x.dataset.s }); if (r.error) alert(r.error); load()
    };
    rff.st.onchange = () => { rff.amount.disabled = rff.st.value == 'none' };
    rff.onsubmit = async e => {
        e.preventDefault(); const r = await post('refund', { id: rfId, st: rff.st.value, amount: rff.amount.value || 0, note: rff.note.value });
        if (r.error) { $('#rferr').textContent = r.error; return } rfd.close(); load()
    };
    $('#rfx').onclick = () => rfd.close();

    /* rooms and rates */
    $('#rooms').addEventListener('input', () => { roomsDirty = true });
    $('#rooms').onclick = async e => {
        const b = e.target.closest('[data-save]'); if (!b) return; const tr = b.closest('tr'), g = n => tr.querySelector(`[name=${n}]`);
        if (!g('active').checked && +b.dataset.up > 0 && !confirm(`This room has ${b.dataset.up} upcoming booking(s). They stay valid, but new guests will not be able to book it. Switch it off?`)) return;
        const r = await post('room_save', { id: b.dataset.save, name: g('name').value.trim(), rate: g('rate').value, base_cap: g('base_cap').value, max_cap: g('max_cap').value, active: g('active').checked ? 1 : '' });
        $('#rmsg').textContent = r.error || ''; if (!r.error) { roomsDirty = false; load() }
    };
    $('#raf').onsubmit = async e => { e.preventDefault(); const f = e.target, r = await post('room_add', Object.fromEntries(new FormData(f))); $('#rmsg').textContent = r.error || ''; if (!r.error) { f.reset(); roomsDirty = false; load() } };

    /* walk-in booking with live price preview */
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

    /* report form defaults: this month */
    const rp = $('#rpf'), d0 = new Date(); rp.from.value = `${d0.getFullYear()}-${String(d0.getMonth() + 1).padStart(2, '0')}-01`; rp.to.value = today();

    load(); setInterval(() => { if (!$('#dash').hidden) load() }, 15000);
}
