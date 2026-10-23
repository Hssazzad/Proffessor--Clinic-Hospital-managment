@if(session('secure_session_flash'))
@php
    $__name = Auth::user()->name ?? '';
    session()->forget('secure_session_flash');
@endphp
<style>
    /* Floating toast — dark, high-contrast, pill-shaped, bottom-right.
       Reuses the flash system's motion easing for consistency. */
    .pc-secure-toast{
        position:fixed;right:22px;bottom:22px;z-index:20000;
        display:flex;align-items:center;gap:12px;
        padding:13px 20px;border-radius:999px; /* pill */
        background:#0f172a;color:#fff;
        box-shadow:0 12px 30px rgba(15,23,42,.35);
        font-size:13.5px;font-weight:600;letter-spacing:.1px;
        max-width:min(360px,calc(100vw - 44px));
        transform:translateY(calc(100% + 40px));opacity:0;
        transition:transform .55s cubic-bezier(.21,1.02,.55,1),opacity .45s ease;
    }
    .pc-secure-toast--in{transform:translateY(0);opacity:1}
    .pc-secure-toast__icon{flex:0 0 30px;width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#10b981,#059669);color:#fff;box-shadow:0 4px 10px rgba(16,185,129,.4)}
    .pc-secure-toast__icon svg{width:16px;height:16px}
    .pc-secure-toast__text{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .pc-secure-toast__sub{color:#94a3b8;font-weight:500}
    @media (prefers-reduced-motion:reduce){.pc-secure-toast{transition:none;transform:none;opacity:1}}
    .dark-mode .pc-secure-toast{background:#1e293b;border:1px solid #334155;box-shadow:0 12px 30px rgba(0,0,0,.55)}
</style>
<div class="pc-secure-toast" id="pcSecureToast" role="status" aria-live="polite">
    <span class="pc-secure-toast__icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    </span>
    <span class="pc-secure-toast__text">Welcome back{{ $__name ? ', ' . e($__name) : '' }} <span class="pc-secure-toast__sub">&middot; your session is secure</span></span>
</div>
<script>
(function () {
    var t = document.getElementById('pcSecureToast');
    if (!t) return;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!reduce) setTimeout(function () { t.classList.add('pc-secure-toast--in'); }, 150);
    else t.classList.add('pc-secure-toast--in');
    setTimeout(function () {
        t.classList.remove('pc-secure-toast--in');
        setTimeout(function () { t.remove(); }, 600);
    }, 4500);
})();
</script>
@endif
