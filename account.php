<?php require 'config.php';
$bodyClass = 'flow';
include 'head.php'; ?>
<section class="wrap profilewrap">
    <h1 class="bigtitle" data-t>My profile.</h1>
    <form id="acf" class="card profilecard" novalidate>
        <h2 data-t>Customer profile</h2>
        <p class="full mu"><small><b class="req" aria-hidden="true">*</b> <span data-t>Required field</span></small></p>
        <label><span><span>First name</span><b class="req" aria-hidden="true">*</b></span><input name="first_name" autocomplete="given-name" maxlength="50" required data-req="Enter your first name"><small class="fe"></small></label>
        <label><span><span>Last name</span><b class="req" aria-hidden="true">*</b></span><input name="last_name" autocomplete="family-name" maxlength="50" required data-req="Enter your last name"><small class="fe"></small></label>
        <label><span data-t>Email address</span><input type="email" name="email" autocomplete="email" readonly aria-describedby="emailnote"></label>
        <label><span><span data-t>Phone number</span><b class="req" aria-hidden="true">*</b></span><input type="tel" name="phone" autocomplete="tel" required data-req="Enter your mobile number"><small class="fe"></small></label>
        <input type="hidden" name="name">
        <p id="emailnote" class="mu full"><small data-t>Your email is your login, so it can’t be changed here.</small></p>
        <div class="full pwsec">
            <h3 data-t>Change password (optional)</h3>
            <div class="pwgrid">
                <label><span data-t>Current password</span><span class="pw" data-pw><input type="password" name="cur_pass" autocomplete="current-password"></span><small class="fe"></small></label>
                <label><span data-t>New password</span><span class="pw" data-pw><input type="password" name="new_pass" minlength="6" data-min="6" autocomplete="new-password"></span><small class="fe"></small></label>
            </div>
        </div>
        <p id="amsg" class="full err" aria-live="polite"></p>
        <button class="btn full" data-t>Save profile changes</button>
    </form>
</section>
<style>
#acf .req{color:#D4AF37;font-weight:700;margin-left:.15em}
#acf .pw{position:relative;display:block}#acf .pw input{width:100%;padding-right:3rem}
#acf .pwt{position:absolute;right:.4rem;top:50%;translate:0 -50%;display:grid;place-items:center;width:2.3rem;height:2.3rem;border:0;border-radius:50%;background:transparent;color:var(--mu,#4E6556);cursor:pointer;transition:color .2s,background-color .2s}
#acf .pwt:hover{color:#B88E28;background:rgba(212,175,55,.14)}#acf .pwt:focus-visible{outline:2px solid #D4AF37}
#acf .pwt .eyeoff,#acf .pwt[aria-pressed=true] .eye{display:none}#acf .pwt[aria-pressed=true] .eyeoff{display:block}
#acf input:focus{outline:none;border-color:#D4AF37;box-shadow:0 0 0 4px rgba(212,175,55,.26)}
#acf .fe{display:block;max-height:0;opacity:0;overflow:hidden;color:var(--err,#A64B3C);font-size:.82rem;transition:max-height .3s,opacity .3s,margin .3s}
#acf .fe:not(:empty){max-height:3rem;opacity:1;margin-top:.35rem}
#acf label.bad input{border-color:var(--err,#A64B3C)}
#acf label.good input:not([readonly]){border-color:var(--ok,#2B6E47)}
</style>
<script>
(() => {
  const f = document.getElementById('acf'), $$ = s => [...f.querySelectorAll(s)];
  const BTN = '<button type="button" class="pwt" aria-label="Show password" aria-pressed="false"><svg class="eye" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg><svg class="eyeoff" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3l18 18"/><path d="M10.6 5.1A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a17.6 17.6 0 0 1-3.2 4.2M6.6 6.6C3.9 8.4 2 12 2 12s3.6 7 10 7c1.7 0 3.2-.4 4.5-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button>';
  $$('[data-pw]').forEach(w => w.insertAdjacentHTML('beforeend', BTN));
  f.addEventListener('click', e => {
    const b = e.target.closest('.pwt'); if (!b) return;
    const i = b.parentNode.querySelector('input'), on = i.type === 'password';
    i.type = on ? 'text' : 'password'; b.setAttribute('aria-pressed', on); b.setAttribute('aria-label', on ? 'Hide password' : 'Show password')
  });
  const check = (i, force) => {
    const v = i.closest('[data-pw]') ? i.value : i.value.trim(); let m = '';
    if (i.required && !v) m = i.dataset.req || 'This field is required';
    else if (i.name === 'phone' && v.replace(/\D/g, '').length < 7) m = 'Enter a valid mobile number';
    else if (i.dataset.min && v && v.length < +i.dataset.min) m = 'Use at least ' + i.dataset.min + ' characters';
    else if (force) m = force;
    const l = i.closest('label'); l.classList.toggle('bad', !!m); l.classList.toggle('good', !m && !!v);
    i.setAttribute('aria-invalid', !!m); l.querySelector('.fe').textContent = m; return !m
  };
  $$('input[required],input[name=new_pass],input[name=cur_pass]').forEach(i => {
    i.addEventListener('blur', () => { if (i.value) check(i) });
    i.addEventListener('input', () => { const c = i.closest('label').classList; if (c.contains('bad') || c.contains('good')) check(i) })
  });
  /* runs before app.js: validate, then build the combined "name" the API still stores */
  document.addEventListener('submit', e => {
    if (e.target !== f) return;
    f.elements.name.value = (f.first_name.value.trim() + ' ' + f.last_name.value.trim()).trim();
    const bad = $$('input[required]').filter(i => !check(i));
    if (!check(f.new_pass)) bad.push(f.new_pass);
    if (f.new_pass.value && !f.cur_pass.value) { check(f.cur_pass, 'Enter your current password to set a new one'); bad.push(f.cur_pass) }
    if (bad.length) { e.preventDefault(); e.stopPropagation(); bad[0].focus() }
  }, true);
  /* fill first / last name (older accounts only have a full name, so split it at the last space) */
  const d = new FormData(); d.append('a', 'lookup');
  fetch('api.php', { method: 'POST', body: d }).then(r => r.json()).then(r => {
    const g = r.guest; if (!g) return; let a = g.first_name, b = g.last_name;
    if (a == null && b == null) { const p = (g.name || '').trim().split(/\s+/); b = p.length > 1 ? p.pop() : ''; a = p.join(' ') }
    f.first_name.value = a || ''; f.last_name.value = b || ''
  }).catch(() => { });
})();
</script>
<?php include 'foot.php'; ?>
