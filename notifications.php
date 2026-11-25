<?php require 'config.php';
$bodyClass = 'ntpage';
include 'head.php'; ?>
<section class="wrap ntwrap">
    <div class="nthead">
        <div>
            <h1 data-t>Notifications</h1>
            <p class="mu" id="ntSub" aria-live="polite"></p>
        </div>
        <button type="button" class="ntBtn" id="ntAll" disabled data-t>Mark all as read</button>
    </div>
    <div class="ntTabs" id="ntTabs">
        <button type="button" class="on" data-f="all" data-t>All</button><button type="button" data-f="unread" data-t>Unread</button>
    </div>
    <ul class="ntList" id="ntList" aria-live="polite"></ul>
    <p class="mu ntNote" id="ntNote" hidden>Showing your 100 most recent notifications.</p>
</section>
<style>
    [data-theme=light] body.ntpage { background: #F5F2EB }
    .ntwrap { --c-bg: #fff; --c-tx: #0A2214; --c-mu: #5C6F66; --c-ln: rgba(10, 34, 20, .14); --c-un: rgba(212, 175, 55, .16); max-width: 760px; padding-top: 2rem; padding-bottom: 4rem }
    [data-theme=dark] .ntwrap { --c-bg: #0F2D1F; --c-tx: #F5F2EB; --c-mu: #9DB1A8; --c-ln: rgba(212, 175, 55, .25); --c-un: rgba(212, 175, 55, .12) }
    .nthead { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; justify-content: space-between; margin-bottom: 1rem }
    .nthead h1 { margin: 0 }
    .nthead p { margin: .2rem 0 0 }
    .ntBtn { font: inherit; font-weight: 600; cursor: pointer; border-radius: 10px; padding: .6rem 1.2rem; color: #D4AF37; background: #0A2214; border: 1.5px solid #D4AF37 }
    [data-theme=dark] .ntBtn { color: #0A2214; background: #D4AF37 }
    .ntBtn:disabled { opacity: .4; cursor: not-allowed }
    .ntTabs { display: flex; gap: .5rem; margin-bottom: 1rem }
    .ntTabs button { font: inherit; font-weight: 600; cursor: pointer; padding: .35rem 1rem; border-radius: 99px; color: var(--c-tx); background: none; border: 1px solid var(--c-ln) }
    .ntTabs button.on { color: #0A2214; background: #D4AF37; border-color: #D4AF37 }
    .ntList { list-style: none; margin: 0; padding: 0; display: grid; gap: .7rem }
    .ntItem { position: relative; display: flex; gap: .9rem; align-items: flex-start; padding: .9rem 1rem .9rem 1.1rem; color: var(--c-tx); background: var(--c-bg); border: 1px solid var(--c-ln); border-radius: 14px; opacity: .78; transition: opacity .2s, transform .2s }
    .ntItem.unread { opacity: 1; background: linear-gradient(var(--c-un), var(--c-un)), var(--c-bg); border-color: #D4AF37; cursor: pointer }
    .ntItem.unread::before { content: ""; position: absolute; left: 0; top: 10px; bottom: 10px; width: 4px; border-radius: 0 4px 4px 0; background: #D4AF37 }
    .ntItem.unread:hover { transform: translateY(-1px) }
    .ntItem:focus-visible { outline: 3px solid #D4AF37; outline-offset: 2px }
    .ntIc { flex: none; width: 2.3rem; height: 2.3rem; display: grid; place-items: center; border-radius: 50%; font-weight: 700; color: #D4AF37; background: #0A2214; border: 1px solid rgba(212, 175, 55, .6) }
    .ntBody { flex: 1; min-width: 0 }
    .ntBody p { margin: 0 0 .25rem; line-height: 1.5; overflow-wrap: anywhere }
    .ntItem.unread .ntBody p { font-weight: 600 }
    .ntBody small { color: var(--c-mu) }
    .ntTag { font-weight: 600; letter-spacing: .04em; text-transform: uppercase; font-size: .72rem }
    .ntDot { flex: none; width: .65rem; height: .65rem; margin-top: .55rem; border-radius: 50%; background: #D4AF37; box-shadow: 0 0 0 4px rgba(212, 175, 55, .25) }
    .ntEmpty { padding: 2rem 1rem; text-align: center; color: var(--c-mu); background: var(--c-bg); border: 1px dashed var(--c-ln); border-radius: 14px }
    .ntNote { text-align: center; margin-top: 1rem }
    @media (prefers-reduced-motion: reduce) { .ntItem { transition: none } }
</style>
<script>
(() => {
  const $ = s => document.querySelector(s), esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const api = (a, d = {}) => fetch('api.php', { method: 'POST', body: new URLSearchParams({ a, ...d }) }).then(r => r.json());
  let notes = [], filter = 'all';
  /* icon + label by what the message is about: booking, points, rewards, refunds, alerts */
  const kind = m => /refund/i.test(m) ? ['₱', 'Refund'] : /rejected|cancel|expire/i.test(m) ? ['!', 'Alert'] : /earned|points/i.test(m) ? ['★', 'Points'] : /won|voucher|reward|vip/i.test(m) ? ['✦', 'Reward'] : /verified|approved|received|submitted|completed|thank/i.test(m) ? ['✓', 'Booking'] : ['•', 'Update'];
  const ago = s => { const d = new Date(String(s).replace(' ', 'T')), m = Math.round((Date.now() - d) / 60000); return m < 1 ? 'just now' : m < 60 ? m + ' min ago' : m < 1440 ? Math.floor(m / 60) + ' h ago' : d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' }) };
  const sync = n => { if (window.bvBell) window.bvBell(n) };

  function render() {
    const un = notes.filter(n => !+n.is_read).length, list = filter == 'unread' ? notes.filter(n => !+n.is_read) : notes;
    $('#ntSub').textContent = notes.length ? (un ? `${un} unread` : 'You’re all caught up.') : '';
    $('#ntAll').disabled = !un; $('#ntNote').hidden = notes.length < 100;
    $('#ntList').innerHTML = list.length ? list.map(n => {
      const [ic, lb] = kind(n.msg), u = !+n.is_read;
      return `<li class="ntItem${u ? ' unread' : ''}" data-id="${+n.id}"${u ? ' tabindex="0" role="button" aria-label="Unread: mark as read"' : ''}><span class="ntIc" aria-hidden="true">${ic}</span><div class="ntBody"><p>${esc(n.msg)}</p><small><span class="ntTag">${lb}</span> · <time datetime="${esc(n.created_at)}" title="${esc(n.created_at)}">${ago(n.created_at)}</time></small></div>${u ? '<i class="ntDot" title="Unread"></i>' : ''}</li>`
    }).join('') : `<li class="ntEmpty">${notes.length ? 'No unread notifications.' : 'No notifications yet. Booking updates and rewards will show up here.'}</li>`
  }
  async function readOne(id) {
    const r = await api('notes_read', { id }); if (r.error) return;
    const n = notes.find(x => +x.id == id); if (n) n.is_read = 1; render(); sync(r.unread)
  }
  $('#ntAll').onclick = async () => {
    $('#ntAll').disabled = true; const r = await api('notes_read'); if (r.error) { $('#ntAll').disabled = false; return }
    notes.forEach(n => n.is_read = 1); render(); sync(0)
  };
  $('#ntTabs').onclick = e => {
    const b = e.target.closest('button'); if (!b) return; filter = b.dataset.f;
    document.querySelectorAll('#ntTabs button').forEach(x => x.classList.toggle('on', x == b)); render()
  };
  const pick = e => { const li = e.target.closest('.ntItem.unread'); if (li) readOne(+li.dataset.id) };
  $('#ntList').addEventListener('click', pick);
  $('#ntList').addEventListener('keydown', e => { if (e.key == 'Enter' || e.key == ' ') { e.preventDefault(); pick(e) } });

  api('notes', { limit: 100 }).then(r => {
    if (r.error) {
      const u = typeof loginUrl == 'function' ? loginUrl() : 'login.php?next=notifications.php';
      $('#ntAll').hidden = true; $('#ntTabs').hidden = true;
      $('#ntList').innerHTML = `<li class="ntEmpty"><a href="${esc(u)}">Log in or register</a> to see your notifications.</li>`; return
    }
    notes = r.notes; render()
  }).catch(() => { $('#ntList').innerHTML = '<li class="ntEmpty">Could not load notifications. Please try again.</li>' });
})();
</script>
<?php include 'foot.php'; ?>
