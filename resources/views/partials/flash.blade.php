@php
    $__flashItems = [];

    if (session()->has('flash')) {
        $f = session('flash');
        if (is_array($f)) {
            $__flashItems[] = [
                'type' => $f['type'] ?? 'info',
                'title' => $f['title'] ?? '',
                'message' => $f['message'] ?? '',
            ];
        } else {
            $__flashItems[] = ['type' => 'info', 'title' => '', 'message' => (string) $f];
        }
    }

    foreach (['success' => 'success', 'status' => 'success', 'info' => 'info', 'warning' => 'warning', 'error' => 'error', 'danger' => 'error'] as $__key => $__type) {
        if (session()->has($__key)) {
            $v = session($__key);
            if (is_string($v)) {
                $__flashItems[] = ['type' => $__type, 'title' => '', 'message' => $v];
            }
        }
    }

    if (isset($errors) && ! $errors->isEmpty() && ! request()->expectsJson()) {
        foreach ($errors->all() as $__err) {
            $__flashItems[] = ['type' => 'error', 'title' => '', 'message' => $__err];
        }
    }
@endphp

@if(count($__flashItems))
<style>
    .pc-flash-zone{position:fixed;top:18px;right:18px;z-index:20000;display:flex;flex-direction:column;gap:12px;max-width:min(420px,calc(100vw - 36px));pointer-events:none}
    .pc-flash{pointer-events:auto;display:flex;align-items:flex-start;gap:14px;position:relative;overflow:hidden;min-width:300px;padding:16px 44px 16px 16px;border-radius:14px;background:rgba(255,255,255,.92);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border:1px solid rgba(255,255,255,.65);box-shadow:0 12px 34px rgba(15,23,42,.14),0 3px 10px rgba(15,23,42,.08);transform:translateX(calc(100% + 40px));opacity:0;transition:transform .55s cubic-bezier(.21,1.02,.55,1),opacity .45s ease}
    .pc-flash.pc-flash--in{transform:translateX(0);opacity:1}
    .pc-flash.pc-flash--out{transform:translateX(calc(100% + 40px));opacity:0}
    .pc-flash::before{content:"";position:absolute;left:0;top:0;bottom:0;width:5px;border-radius:14px 0 0 14px}
    .pc-flash-icon{flex:0 0 40px;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 14px rgba(15,23,42,.12)}
    .pc-flash-icon svg{width:20px;height:20px}
    .pc-flash-body{flex:1;min-width:0;padding-top:1px}
    .pc-flash-title{margin:0 0 2px;font-size:14.5px;font-weight:700;color:#0f172a;letter-spacing:.1px}
    .pc-flash-msg{margin:0;font-size:13px;line-height:1.45;color:#475569;word-wrap:break-word}
    .pc-flash-title:empty{display:none}
    .pc-flash-msg:empty{display:none}
    .pc-flash-close{position:absolute;top:9px;right:9px;width:26px;height:26px;border:0;border-radius:8px;background:transparent;color:#94a3b8;font-size:15px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .2s,color .2s}
    .pc-flash-close:hover{background:#f1f5f9;color:#334155}
    .pc-flash-bar{position:absolute;left:0;bottom:0;height:3px;width:100%;transform-origin:left;animation:pcFlashBar linear forwards}
    .pc-flash--success::before{background:linear-gradient(180deg,#34d399,#059669)}
    .pc-flash--success .pc-flash-icon{background:linear-gradient(135deg,#d1fae5,#a7f3d0);color:#059669}
    .pc-flash--success .pc-flash-bar{background:#10b981}
    .pc-flash--info::before{background:linear-gradient(180deg,#38bdf8,#2563eb)}
    .pc-flash--info .pc-flash-icon{background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:#2563eb}
    .pc-flash--info .pc-flash-bar{background:#3b82f6}
    .pc-flash--warning::before{background:linear-gradient(180deg,#fbbf24,#d97706)}
    .pc-flash--warning .pc-flash-icon{background:linear-gradient(135deg,#fef3c7,#fde68a);color:#d97706}
    .pc-flash--warning .pc-flash-bar{background:#f59e0b}
    .pc-flash--error::before{background:linear-gradient(180deg,#f87171,#dc2626)}
    .pc-flash--error .pc-flash-icon{background:linear-gradient(135deg,#fee2e2,#fecaca);color:#dc2626}
    .pc-flash--error .pc-flash-bar{background:#ef4444}
    @keyframes pcFlashBar{from{transform:scaleX(1)}to{transform:scaleX(0)}}
    @media (prefers-reduced-motion:reduce){.pc-flash{transition:none}}
    @media (max-width:575.98px){.pc-flash-zone{top:12px;right:12px;left:12px;max-width:none}.pc-flash{min-width:0}}
</style>
<div class="pc-flash-zone" id="pcFlashZone">
    @foreach($__flashItems as $__item)
        @php
            $__type = in_array($__item['type'], ['success', 'error', 'warning', 'info'], true) ? $__item['type'] : 'info';
            $__duration = 4500;
        @endphp
        <div class="pc-flash pc-flash--{{ $__type }}" role="alert" data-duration="{{ $__duration }}">
            <span class="pc-flash-icon">
                @if($__type === 'success')
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                @elseif($__type === 'error')
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                @elseif($__type === 'warning')
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                @endif
            </span>
            <div class="pc-flash-body">
                <p class="pc-flash-title">{{ $__item['title'] }}</p>
                <p class="pc-flash-msg">{{ $__item['message'] }}</p>
            </div>
            <button type="button" class="pc-flash-close" aria-label="Close">&times;</button>
        </div>
    @endforeach
</div>
<script>
(function () {
    'use strict';
    var zone = document.getElementById('pcFlashZone');
    if (!zone) return;

    var icons = {
        success: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
        error: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
        warning: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        info: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
    };

    function dismiss(toast) {
        if (toast.dataset.dismissing) return;
        toast.dataset.dismissing = '1';
        toast.classList.remove('pc-flash--in');
        toast.classList.add('pc-flash--out');
        setTimeout(function () {
            toast.remove();
            if (!zone.children.length) zone.remove();
        }, 600);
    }

    function arm(toast) {
        var duration = parseInt(toast.dataset.duration || '4500', 10);
        var bar = document.createElement('span');
        bar.className = 'pc-flash-bar';
        bar.style.animationDuration = duration + 'ms';
        toast.appendChild(bar);
        var timer = setTimeout(function () { dismiss(toast); }, duration + 350);
        toast.addEventListener('mouseenter', function () {
            clearTimeout(timer);
            bar.style.animationPlayState = 'paused';
        });
        toast.addEventListener('mouseleave', function () {
            bar.style.animationPlayState = 'running';
        });
    }

    zone.addEventListener('click', function (e) {
        var btn = e.target.closest('.pc-flash-close');
        if (btn) dismiss(btn.closest('.pc-flash'));
    });

    Array.prototype.forEach.call(zone.querySelectorAll('.pc-flash'), function (toast, i) {
        toast.querySelector('.pc-flash-close').addEventListener('click', function () { dismiss(toast); });
        setTimeout(function () {
            toast.classList.add('pc-flash--in');
            arm(toast);
        }, 120 + i * 140);
    });

    window.pcFlash = function (type, title, message, duration) {
        type = icons[type] ? type : 'info';
        duration = duration || 4500;
        var t = document.createElement('div');
        t.className = 'pc-flash pc-flash--' + type;
        t.setAttribute('role', 'alert');
        t.dataset.duration = duration;
        t.innerHTML = '<span class="pc-flash-icon">' + icons[type] + '</span>' +
            '<div class="pc-flash-body"><p class="pc-flash-title"></p><p class="pc-flash-msg"></p></div>' +
            '<button type="button" class="pc-flash-close" aria-label="Close">&times;</button>';
        if (title) t.querySelector('.pc-flash-title').textContent = title;
        if (message) t.querySelector('.pc-flash-msg').textContent = message;
        t.querySelector('.pc-flash-close').addEventListener('click', function () { dismiss(t); });
        var host = document.getElementById('pcFlashZone');
        if (!host) {
            host = document.createElement('div');
            host.className = 'pc-flash-zone';
            host.id = 'pcFlashZone';
            document.body.appendChild(host);
            zone = host;
        }
        host.appendChild(t);
        setTimeout(function () {
            t.classList.add('pc-flash--in');
            arm(t);
        }, 60);
        return t;
    };
})();
</script>
@endif
