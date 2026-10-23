@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Navbar left links --}}
    <ul class="navbar-nav">
        {{-- Left sidebar toggler link --}}
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

        {{-- Configured left links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

        {{-- Custom left links --}}
        @yield('content_top_nav_left')
    </ul>

    {{-- Navbar right links --}}
    <ul class="navbar-nav ml-auto">
        {{-- Custom right links --}}
        @yield('content_top_nav_right')

        {{-- USG follow-up notification bell --}}
        @if(Auth::check())
            @php
                $bellCount = \App\Models\DoctorNotification::where('is_read', false)
                    ->whereHas('followup', function ($q) {
                        $q->where('status', 'pending')
                          ->whereDate('followup_date', '<=', now()->toDateString());
                    })->count();
            @endphp
            <li class="nav-item">
                <a class="nav-link" href="{{ route('notifications.index') }}" title="Follow-up Notifications">
                    <i class="far fa-bell"></i>
                    @if($bellCount > 0)
                        <span class="badge badge-danger navbar-badge">{{ $bellCount }}</span>
                    @endif
                </a>
            </li>
        @endif

        {{-- Billing notification bell --}}
        @if(Auth::check())
            @php
                $billingBellCount = \App\Models\BillingNotification::unread()->live()->count();
            @endphp
            <li class="nav-item">
                <a class="nav-link" href="{{ route('billing.notifications.index') }}" title="Billing Notifications">
                    <i class="fas fa-bell"></i>
                    @if($billingBellCount > 0)
                        <span class="badge badge-danger navbar-badge">{{ $billingBellCount }}</span>
                    @endif
                </a>
            </li>
        @endif

        {{-- Configured right links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        {{-- User menu link --}}
        @if(Auth::user())
            @if(config('adminlte.usermenu_enabled'))
                @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
            @else
                @include('adminlte::partials.navbar.menu-item-logout-link')
            @endif
        @endif

        {{-- Right sidebar toggler link --}}
        @if($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif
    </ul>

</nav>
