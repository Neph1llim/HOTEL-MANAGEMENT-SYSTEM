<?php include 'head.php'; ?>
<style>
.lgnpage{--c-bg:#FDFBF6;--c-in:#fff;--c-tx:#0A2214;--c-mu:#4E6556;--c-ln:#DDD5C0;--c-err:#A64B3C;--c-ok:#2B6E47;--c-gtx:#86640F;
  min-height:calc(100vh - 13rem);display:grid;place-items:center;padding:2.5rem 1rem 4rem;
  background:radial-gradient(60% 45% at 50% 0%,rgba(212,175,55,.16),transparent 70%)}
[data-theme=dark] .lgnpage{--c-bg:#113320;--c-in:rgba(247,244,236,.06);--c-tx:#F7F4EC;--c-mu:#A9BDB0;--c-ln:#24503A;--c-err:#E08A78;--c-ok:#7FD1A0;--c-gtx:#E8C868}
.authcard{position:relative;width:min(100%,460px);overflow:hidden;background:var(--c-bg);color:var(--c-tx);
  border:1px solid color-mix(in srgb,#D4AF37 55%,var(--c-ln));border-radius:24px;padding:2.2rem clamp(1.3rem,5vw,2.3rem) 1.8rem;
  box-shadow:inset 0 1px 0 rgba(212,175,55,.35),0 30px 60px -30px rgba(10,34,20,.55),0 0 0 6px rgba(212,175,55,.06);
  transition:height .45s cubic-bezier(.22,.61,.36,1),box-shadow .4s ease}
.authcard:focus-within{box-shadow:inset 0 1px 0 rgba(212,175,55,.5),0 36px 70px -30px rgba(10,34,20,.65),0 0 0 6px rgba(212,175,55,.14)}
.authcard::before{content:"";position:absolute;inset:0 0 auto;height:4px;background:linear-gradient(90deg,#B88E28,#E8C868,#B88E28)}
.authcard.shake{animation:authshake .4s}
@keyframes authshake{20%,60%{translate:-6px 0}40%,80%{translate:6px 0}}
.emblem{display:grid;place-items:center;width:76px;height:76px;margin:0 auto 1rem;border-radius:50%;background:#0A2214;border:2px solid #D4AF37;box-shadow:0 8px 20px -10px rgba(10,34,20,.7)}
.emblem img{width:46px;height:auto}
.authcard h1{font-family:var(--hd,'Cormorant Garamond',Georgia,serif);font-size:2.1rem;font-weight:600;text-align:center;margin:.1rem 0 .3rem;color:var(--c-tx)}
.authcard .sub{text-align:center;color:var(--c-mu);margin:0 0 1.2rem;font-size:.95rem}
.authcard .note{margin:0 0 1rem;font-size:.8rem;color:var(--c-mu)}
.req{color:#D4AF37;font-weight:700;font-size:1.05em;line-height:1;margin-left:.15em}
.authalert{margin:0 0 1rem;padding:.75rem 1rem;border-radius:12px;border:1px solid var(--c-err);color:var(--c-err);font-size:.9rem;background:color-mix(in srgb,var(--c-err) 10%,transparent)}
.authalert:empty{display:none}
.authcard form{transition:opacity .2s ease,translate .3s ease}
.authcard form.out{opacity:0;translate:0 -8px}
.authcard form.inn{animation:authin .45s cubic-bezier(.22,.61,.36,1) both}
@keyframes authin{from{opacity:0;translate:0 12px}to{opacity:1;translate:0 0}}
.fld{display:block;margin:0 0 1rem}
.lb{display:flex;align-items:center;margin-bottom:.4rem;font:600 .82rem var(--nav,Manrope),system-ui,sans-serif;letter-spacing:.04em;color:var(--c-tx);transition:color .2s}
.fld:focus-within .lb{color:var(--c-gtx)}
.authcard input{width:100%;height:3rem;margin:0;padding:0 1rem;border:1px solid var(--c-ln);border-radius:12px;background:var(--c-in);color:var(--c-tx);font:inherit;transition:border-color .25s,box-shadow .25s,background-color .25s}
.authcard input:hover{border-color:color-mix(in srgb,#B88E28 60%,var(--c-ln))}
.authcard input:focus{outline:none;border-color:#D4AF37;box-shadow:0 0 0 4px rgba(212,175,55,.26)}
.fld.bad input{border-color:var(--c-err)} .fld.bad .lb{color:var(--c-err)} .fld.bad input:focus{box-shadow:0 0 0 4px color-mix(in srgb,var(--c-err) 25%,transparent)}
.fld.good input{border-color:var(--c-ok)}
.fe{display:block;max-height:0;opacity:0;overflow:hidden;color:var(--c-err);font-size:.82rem;transition:max-height .3s,opacity .3s,margin .3s}
.fe:not(:empty){max-height:3rem;opacity:1;margin-top:.35rem}
.hint{display:block;margin-top:.35rem;font-size:.78rem;color:var(--c-mu)}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:0 .8rem}
.pw{position:relative;display:block}.pw input{padding-right:3rem}
.pwt{position:absolute;right:.4rem;top:50%;translate:0 -50%;display:grid;place-items:center;width:2.3rem;height:2.3rem;border:0;border-radius:50%;background:transparent;color:var(--c-mu);cursor:pointer;transition:color .2s,background-color .2s}
.pwt:hover{color:#B88E28;background:rgba(212,175,55,.14)}.pwt:focus-visible{outline:2px solid #D4AF37}
.pwt .eyeoff,.pwt[aria-pressed=true] .eye{display:none}.pwt[aria-pressed=true] .eyeoff{display:block}
.authbtn{position:relative;width:100%;height:3.1rem;margin-top:.4rem;border:0;border-radius:999px;cursor:pointer;font:600 1rem var(--nav,Manrope),system-ui,sans-serif;letter-spacing:.03em;color:#0A2214;
  background:linear-gradient(135deg,#E8C868,#D4AF37 50%,#B88E28);box-shadow:inset 0 1px 0 rgba(255,255,255,.4),0 12px 26px -12px rgba(212,175,55,.8);transition:transform .25s,filter .25s,box-shadow .25s}
.authbtn:hover:not(:disabled){transform:translateY(-2px);filter:brightness(1.07)}.authbtn:focus-visible{outline:3px solid rgba(212,175,55,.5);outline-offset:3px}
.authbtn.busy{color:transparent;cursor:progress}
.authbtn.busy::after{content:"";position:absolute;inset:0;margin:auto;width:1.2rem;height:1.2rem;border:2px solid rgba(10,34,20,.3);border-top-color:#0A2214;border-radius:50%;animation:authspin .7s linear infinite}
@keyframes authspin{to{rotate:360deg}}
.swap{margin:1.3rem 0 0;text-align:center;font-size:.92rem;color:var(--c-mu)}
.swap a{color:var(--c-gtx);font-weight:600;text-decoration:none;background:linear-gradient(currentColor,currentColor) 0 100%/0 1px no-repeat;transition:background-size .3s}
.swap a:hover,.swap a:focus-visible{background-size:100% 1px}
@media(max-width:420px){.row2{grid-template-columns:1fr}}
@media(prefers-reduced-motion:reduce){.authcard,.authcard form,.authcard input,.authbtn{transition:none!important;animation:none!important}}
</style>
<section class="lgnpage">
    <div class="authcard" id="authcard">
        <div class="emblem"><img src="assets/img/logo.png" alt="" onerror="this.remove()"></div>
        <p id="lerr" class="authalert" role="alert" aria-live="polite"></p>

        <form id="lf" novalidate>
            <h1 data-t>Welcome to Bella Vista</h1>
            <p class="sub">Log in to manage your reservations and rewards.</p>
            <p class="note"><b class="req" aria-hidden="true">*</b> <span data-t>Required field</span></p>
            <label class="fld"><span class="lb"><span data-t>Email</span><b class="req" aria-hidden="true">*</b></span>
                <input type="email" name="email" autocomplete="username" required data-req="Enter your email address"><small class="fe"></small></label>
            <label class="fld"><span class="lb"><span data-t>Password</span><b class="req" aria-hidden="true">*</b></span>
                <span class="pw" data-pw><input type="password" name="pass" autocomplete="current-password" required data-req="Enter your password"></span><small class="fe"></small></label>
            <button class="authbtn" data-t>Log in</button>
            <p class="swap"><span>Don't have an account yet?</span> <a href="#register" data-go="register" data-t>Create an account</a></p>
        </form>

        <form id="rg" novalidate hidden>
            <h1 data-t>Create an account</h1>
            <p class="sub">Join Bella Vista to book rooms and earn rewards.</p>
            <p class="note"><b class="req" aria-hidden="true">*</b> <span data-t>Required field</span></p>
            <div class="row2">
                <label class="fld"><span class="lb"><span>First name</span><b class="req" aria-hidden="true">*</b></span>
                    <input name="first_name" autocomplete="given-name" maxlength="50" required data-req="Enter your first name"><small class="fe"></small></label>
                <label class="fld"><span class="lb"><span>Last name</span><b class="req" aria-hidden="true">*</b></span>
                    <input name="last_name" autocomplete="family-name" maxlength="50" required data-req="Enter your last name"><small class="fe"></small></label>
            </div>
            <label class="fld"><span class="lb"><span data-t>Email</span><b class="req" aria-hidden="true">*</b></span>
                <input type="email" name="email" autocomplete="email" required data-req="Enter your email address"><small class="fe"></small></label>
            <label class="fld"><span class="lb"><span data-t>Mobile number</span><b class="req" aria-hidden="true">*</b></span>
                <input type="tel" name="phone" autocomplete="tel" required data-req="Enter your mobile number"><small class="fe"></small></label>
            <label class="fld"><span class="lb"><span data-t>Password</span><b class="req" aria-hidden="true">*</b></span>
                <span class="pw" data-pw><input type="password" name="pass" autocomplete="new-password" data-min="6" required data-req="Create a password"></span><small class="fe"></small><small class="hint">At least 6 characters</small></label>
            <input type="hidden" name="name">
            <button class="authbtn" data-t>Create an account</button>
            <p class="swap"><span>Already have an account?</span> <a href="#" data-go="login">Back to Log in</a></p>
        </form>
    </div>
</section>
<script>
(() => {
  const $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const lf = $('#lf'), rg = $('#rg'), card = $('#authcard'), err = $('#lerr'), rm = matchMedia('(prefers-reduced-motion: reduce)').matches;
  /* show / hide password */
  const BTN = '<button type="button" class="pwt" aria-label="Show password" aria-pressed="false"><svg class="eye" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg><svg class="eyeoff" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3l18 18"/><path d="M10.6 5.1A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a17.6 17.6 0 0 1-3.2 4.2M6.6 6.6C3.9 8.4 2 12 2 12s3.6 7 10 7c1.7 0 3.2-.4 4.5-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button>';
  $$('[data-pw]').forEach(w => w.insertAdjacentHTML('beforeend', BTN));
  document.addEventListener('click', e => {
    const b = e.target.closest('.pwt'); if (!b) return;
    const i = $('input', b.parentNode), on = i.type === 'password';
    i.type = on ? 'text' : 'password'; b.setAttribute('aria-pressed', on); b.setAttribute('aria-label', on ? 'Hide password' : 'Show password')
  });
  /* inline validation */
  const mail = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
  const check = i => {
    const pw = !!i.closest('[data-pw]'), v = pw ? i.value : i.value.trim(); let m = '';
    if (i.required && !v) m = i.dataset.req || 'This field is required';
    else if (i.name === 'email' && !mail.test(v)) m = 'Enter a valid email address, like name@example.com';
    else if (i.name === 'phone' && v.replace(/\D/g, '').length < 7) m = 'Enter a valid mobile number';
    else if (i.dataset.min && v.length < +i.dataset.min) m = 'Use at least ' + i.dataset.min + ' characters';
    const l = i.closest('label'); l.classList.toggle('bad', !!m); l.classList.toggle('good', !m && !!v);
    i.setAttribute('aria-invalid', !!m); $('.fe', l).textContent = m; return !m
  };
  $$('input[required]').forEach(i => {
    i.addEventListener('blur', () => { if (i.value) check(i) });
    i.addEventListener('input', () => { const c = i.closest('label').classList; if (c.contains('bad') || c.contains('good')) check(i) })
  });
  const busy = (f, on) => { const b = $('.authbtn', f); b.classList.toggle('busy', on); b.disabled = on; f.setAttribute('aria-busy', on) };
  const shake = () => { card.classList.remove('shake'); void card.offsetWidth; card.classList.add('shake') };
  /* runs before app.js: validates, and builds the combined "name" that app.js / the API still use */
  document.addEventListener('submit', e => {
    const f = e.target; if (f !== lf && f !== rg) return;
    if (f === rg) f.elements.name.value = (f.first_name.value.trim() + ' ' + f.last_name.value.trim()).trim();
    const bad = $$('input[required]', f).filter(i => !check(i));
    if (bad.length) { e.preventDefault(); e.stopPropagation(); err.textContent = ''; bad[0].focus(); shake(); return }
    err.textContent = ''; busy(f, true); setTimeout(() => busy(f, false), 15000)
  }, true);
  new MutationObserver(() => { if (err.textContent) { busy(lf, false); busy(rg, false); shake() } }).observe(err, { childList: true, characterData: true, subtree: true });
  /* smooth switch between Log in and Create an account */
  let cur = lf;
  const swap = (name, fx = true) => {
    const to = name === 'register' ? rg : lf; if (to === cur) return;
    const from = cur, h0 = card.offsetHeight; cur = to; err.textContent = '';
    const finish = () => {
      from.hidden = true; from.classList.remove('out'); to.hidden = false;
      if (fx && !rm) {
        card.style.height = 'auto'; const h1 = card.offsetHeight;
        card.style.height = h0 + 'px'; void card.offsetHeight; card.style.height = h1 + 'px';
        to.classList.add('inn'); setTimeout(() => { card.style.height = ''; to.classList.remove('inn') }, 480)
      }
      history.replaceState(null, '', name === 'register' ? '#register' : location.pathname + location.search);
      if (fx) { const f = $('input', to); if (f) f.focus({ preventScroll: true }) }
    };
    if (rm || !fx) finish(); else { from.classList.add('out'); setTimeout(finish, 200) }
  };
  $$('[data-go]').forEach(a => a.addEventListener('click', e => { e.preventDefault(); swap(a.dataset.go) }));
  addEventListener('hashchange', () => swap(location.hash === '#register' ? 'register' : 'login'));
  if (location.hash === '#register') swap('register', false);
})();
</script>
<?php include 'foot.php'; ?>
