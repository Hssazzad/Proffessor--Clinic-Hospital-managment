@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

<div class="{{ $preloaderHelper->makePreloaderClasses() }}" style="{{ $preloaderHelper->makePreloaderStyle() }}">

    @hasSection('preloader')

        {{-- Use a custom preloader content --}}
        @yield('preloader')

    @else

        {{-- Enterprise Professor Clinic loading screen:
             centered logo + brand + thin progress bar + status text.
             No fake percentages: indeterminate bar while loading, 100% + "Ready" on window load. --}}
        <style>
            .preloader { background-color: #F8FAFC !important;
                background-image: radial-gradient(circle at 50% 42%, rgba(22, 119, 200, .06), transparent 58%); }
            .pc-enterprise { display: flex; flex-direction: column; align-items: center; text-align: center;
                padding: 0 24px; font-family: "Source Sans Pro", -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
            .pc-logo { height: 88px; width: auto; max-width: 240px; object-fit: contain;
                filter: drop-shadow(0 4px 14px rgba(30, 41, 59, .10));
                animation: pc-fade-up .5s ease both; }
            .pc-brand { margin-top: 24px; font-size: 20px; font-weight: 700; letter-spacing: 2px;
                color: #1E293B; text-transform: uppercase; animation: pc-fade-up .5s ease .12s both; }
            .pc-sub { margin-top: 8px; font-size: 13px; font-weight: 400; color: #64748B;
                animation: pc-fade-up .5s ease .22s both; }
            .pc-bar { width: 220px; height: 4px; margin-top: 32px; border-radius: 99px;
                background: #E2E8F0; overflow: hidden; animation: pc-fade-up .5s ease .32s both; }
            .pc-bar i { display: block; height: 100%; width: 35%; border-radius: 99px;
                background: linear-gradient(90deg, #1677C8, #0FA3A3);
                animation: pc-slide 1.4s ease-in-out infinite; }
            .pc-enterprise.done .pc-bar i { animation: none; width: 100%; margin-left: 0; transition: width .3s ease; }
            .pc-status { margin-top: 10px; font-size: 12px; font-weight: 400; color: #64748B;
                animation: pc-fade-up .5s ease .4s both; }
            @keyframes pc-fade-up { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
            @keyframes pc-slide { 0% { margin-left: -35%; } 100% { margin-left: 100%; } }
            @media (max-width: 576px) {
                .pc-logo { height: 72px; max-width: 210px; }
                .pc-brand { font-size: 17px; letter-spacing: 1.5px; }
                .pc-sub { font-size: 12px; }
                .pc-bar { width: 190px; }
            }
            @media (prefers-reduced-motion: reduce) {
                .pc-logo, .pc-brand, .pc-sub, .pc-bar, .pc-status, .pc-bar i { animation: none !important; }
            }
        </style>
        <div class="pc-enterprise">
            <img src="{{ asset('images/PC.png') }}" class="pc-logo" alt="Professor Clinic logo">
            <div class="pc-brand">Professor Clinic</div>
            <div class="pc-sub">Healthcare Management System</div>
            <div class="pc-bar" aria-hidden="true"><i></i></div>
            <div class="pc-status" role="status" aria-live="polite" data-pc-status>Loading...</div>
        </div>
        <script>
            window.addEventListener('load', function () {
                var root = document.querySelector('.preloader .pc-enterprise');
                if (!root) return;
                root.classList.add('done');
                var st = root.querySelector('[data-pc-status]');
                if (st) st.textContent = 'Ready';
            });
        </script>

    @endif

</div>
