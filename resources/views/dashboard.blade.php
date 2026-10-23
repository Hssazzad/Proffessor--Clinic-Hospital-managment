@extends('adminlte::page')

@section('title', 'Clinic Dashboard')

@section('css')
<style>
    /* ============ Dashboard UI/UX redesign — scoped, presentation only ============ */
    .dash { --ds-border: #e9edf2; --ds-muted: #64748b; --ds-faint: #94a3b8; --ds-ink: #0f172a; }

    /* Section headers */
    .dash-section-title { display: flex; align-items: center; gap: 9px; margin: 26px 0 14px; font-size: 15px; font-weight: 700; letter-spacing: .2px; color: #334155; }
    .dash-section-title:first-child { margin-top: 4px; }
    .dash-section-title::before { content: ""; width: 4px; height: 15px; border-radius: 2px; background: linear-gradient(180deg, #2563eb, #4f46e5); }
    .dash-section-title small { font-weight: 500; color: var(--ds-faint); font-size: 12.5px; }

    /* Shared card surface */
    .kpi, .widget, .fu-strip, .panel { background: #fff; border: 1px solid var(--ds-border); border-radius: 12px; box-shadow: 0 1px 2px rgba(16, 24, 40, .05), 0 1px 3px rgba(16, 24, 40, .04); }

    /* Header */
    .dash-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; }
    .dash-header-left { display: flex; align-items: center; gap: 12px; }
    .dash-header-left h4 { font-size: 24px; font-weight: 800; letter-spacing: -.3px; color: var(--ds-ink); }
    .dash-header-left .dash-sub { color: var(--ds-faint); font-size: 13px; margin-top: 2px; }
    .dash-header-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .dash-date { font-size: 13px; color: var(--ds-muted); padding-right: 10px; border-right: 1px solid var(--ds-border); margin-right: 2px; }

    /* KPI cards */
    .kpi { display: flex; align-items: flex-start; gap: 14px; height: 100%; padding: 18px; min-width: 0; max-width: 100%; overflow: hidden; }
    .kpi > div { min-width: 0; flex: 1 1 auto; }
    .kpi-icon { width: 46px; height: 46px; flex: 0 0 46px; border-radius: 11px; display: flex; align-items: center; justify-content: center; font-size: 19px; }
    .kpi-label { font-size: 12px; font-weight: 600; color: var(--ds-muted); text-transform: uppercase; letter-spacing: .4px; overflow-wrap: anywhere; }
    .kpi-value { font-size: clamp(1.5rem, 2.4vw, 2.2rem); font-weight: 800; color: var(--ds-ink); line-height: 1.15; letter-spacing: -.5px; font-variant-numeric: tabular-nums; margin: 3px 0 1px; max-width: 100%; min-width: 0; }
    .kpi-money { white-space: nowrap; font-size: clamp(1.15rem, 1.9vw, 1.95rem); }
    .kpi-sub { font-size: 12px; color: var(--ds-faint); overflow-wrap: anywhere; min-width: 0; }
    .kpi-duo { display: flex; align-items: center; gap: 12px; margin: 3px 0 1px; }
    .kpi-duo-item { display: flex; align-items: baseline; gap: 6px; }
    .kpi-duo-num { font-size: clamp(1.5rem, 2.4vw, 2.2rem); font-weight: 800; color: var(--ds-ink); line-height: 1.15; letter-spacing: -.5px; font-variant-numeric: tabular-nums; }
    .kpi-duo-cap { font-size: 12px; font-weight: 600; color: var(--ds-muted); }
    .kpi-duo-sep { width: 1px; align-self: stretch; background: var(--ds-line, #e5e9f0); }
    .kpi-blue .kpi-icon { background: #e8efff; color: #2563eb; }
    .kpi-green .kpi-icon { background: #e6f7ef; color: #0e9f6e; }
    .kpi-indigo .kpi-icon { background: #ece9fe; color: #5b5bd6; }
    .kpi-red .kpi-icon { background: #fdecec; color: #d64545; }
    .kpi-dark .kpi-icon { background: #eaf0f6; color: #334e68; }

    /* Today's activity widgets */
    .widget { display: flex; flex-direction: column; gap: 10px; height: 100%; padding: 16px; }
    .widget-top { display: flex; align-items: center; justify-content: space-between; }
    .widget-icon { width: 38px; height: 38px; flex: 0 0 38px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 15px; }
    .widget-label { font-size: 12.5px; font-weight: 600; color: var(--ds-muted); }
    .widget-value { font-size: clamp(1.4rem, 2.2vw, 1.6rem); font-weight: 800; color: var(--ds-ink); line-height: 1.2; letter-spacing: -.4px; font-variant-numeric: tabular-nums; min-width: 0; max-width: 100%; overflow-wrap: anywhere; }    .widget-foot { font-size: 11.5px; color: var(--ds-faint); margin-top: auto; }
    .wi-danger { background: #fdecec; color: #d64545; }
    .wi-warning { background: #fff5e0; color: #c98a04; }
    .wi-success { background: #e6f7ef; color: #0e9f6e; }
    .wi-info { background: #e7f4fb; color: #0e86c6; }
    .wi-teal { background: #e3f7f4; color: #0d9488; }
    .wi-purple { background: #f3ebff; color: #8b5cf6; }
    .wi-blue { background: #e8efff; color: #2563eb; }

    /* Follow-up status strip */
    .fu-strip { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; padding: 14px 18px; }
    .fu-icon { width: 42px; height: 42px; flex: 0 0 42px; border-radius: 10px; background: #f3ebff; color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 17px; }
    .fu-label { font-weight: 700; color: var(--ds-ink); font-size: 14px; }
    .fu-sub { font-size: 12px; color: var(--ds-faint); }
    .fu-stats { display: flex; gap: 26px; margin-left: auto; margin-right: 18px; }
    .fu-stat { text-align: center; }
    .fu-stat-num { font-size: 22px; font-weight: 800; line-height: 1.1; font-variant-numeric: tabular-nums; }
    .fu-stat-label { font-size: 11px; color: var(--ds-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }

    /* Panels (charts / tables) */
    .panel { overflow: hidden; }
    .panel-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 13px 18px; border-bottom: 1px solid #eef1f6; }
    .panel-title { font-size: 14px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; }
    .panel-title i { color: var(--ds-muted); }
    .panel-body { padding: 16px 18px; }
    .panel-body.p-0 { padding: 0; }
    .panel .table { margin-bottom: 0; }
    .panel thead th { font-size: 11px; text-transform: uppercase; letter-spacing: .4px; color: var(--ds-muted); border-bottom: 1px solid #eef1f6; background: #f8fafc; padding: 9px 14px; white-space: nowrap; }
    .panel tbody td { padding: 9px 14px; font-size: 13px; border-top: 1px solid #f1f4f8; vertical-align: middle; }
    .panel tbody tr:hover { background: #f8fafc; }
    .panel .table thead th { border-top: 0; }
    .row-note { font-size: 11.5px; color: var(--ds-faint); }

    /* Text helpers */
    .text-green { color: #0e9f6e !important; }
    .text-red { color: #d64545 !important; }
    .amount-due { background: #fdecec; color: #d64545; font-weight: 700; font-variant-numeric: tabular-nums; padding: 3px 10px; border-radius: 7px; }

    @media (max-width: 767.98px) {
        .fu-stats { margin-left: 0; margin-right: 0; width: 100%; justify-content: flex-start; }
        .dash-header-left h4 { font-size: 20px; }
        .kpi-value { font-size: clamp(1.3rem, 7vw, 1.8rem); }
        .kpi-money { font-size: clamp(1.1rem, 6vw, 1.5rem); }
    }
    @media (max-width: 420px) {
        .kpi { padding: 14px; gap: 10px; }
        .kpi-icon { width: 40px; height: 40px; flex-basis: 40px; font-size: 16px; }
        .kpi-money { font-size: clamp(1rem, 5.5vw, 1.4rem); }
    }
</style>
@stop

@section('content_header')
<div class="dash-header">
    <div class="dash-header-left">
        <img src="{{ asset('images/PC.png') }}" alt="Logo" class="content-header-logo">
        <div>
            <h4 class="mb-0"><b>Clinic Dashboard</b></h4>
            <div class="dash-sub">Overview of today's clinic activity</div>
        </div>
    </div>
    <div class="dash-header-right">
        <span class="dash-date">{{ now()->format('l, d M Y') }}</span>
        <a href="{{ route('billing.invoice.index') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-file-invoice"></i> New Invoice
        </a>
        <a href="{{ route('patients.newpatient') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-user-plus"></i> New Patient
        </a>
    </div>
</div>
@stop

@section('content')
<div class="dash">

    @include('partials.secure-session-toast')

    {{-- ======================= OVERVIEW ======================= --}}
    <div class="dash-section-title">Overview <small>Key clinic metrics</small></div>
    <div class="row">

        <div class="col-xl-4 col-lg-4 col-md-6 col-12 mb-3">
            <div class="kpi kpi-blue">
                <div class="kpi-icon"><i class="fas fa-users"></i></div>
                <div>
                    <div class="kpi-label">Total Patients</div>
                    <div class="kpi-value"><span data-cu data-cu-value="{{ (int)($totalPatients ?? 0) }}">{{ $totalPatients ?? 0 }}</span></div>
                    <div class="kpi-sub">Registered patients</div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-4 col-md-6 col-12 mb-3">
            <div class="kpi kpi-green">
                <div class="kpi-icon"><i class="fas fa-user-injured"></i></div>
                <div>
                    <div class="kpi-label">Today's Patients</div>
                    <div class="kpi-value"><span data-cu data-cu-value="{{ (int)($todayRegistered ?? 0) }}">{{ $todayRegistered ?? 0 }}</span></div>
                    <div class="kpi-sub">Registered today</div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-4 col-md-6 col-12 mb-3">
            <div class="kpi kpi-indigo">
                <div class="kpi-icon"><i class="fas fa-user-md"></i></div>
                <div>
                    <div class="kpi-label">Doctors</div>
                    <div class="kpi-value"><span data-cu data-cu-value="{{ (int)($totalDoctors ?? 0) }}">{{ $totalDoctors ?? 0 }}</span></div>
                    <div class="kpi-sub"><span data-cu data-cu-value="{{ (int)($activeDoctors ?? 0) }}">{{ $activeDoctors ?? 0 }}</span> active</div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-4 col-md-6 col-12 mb-3">
            <div class="kpi kpi-green">
                <div class="kpi-icon"><i class="fas fa-money-bill-wave"></i></div>
                <div>
                    <div class="kpi-label">Today Revenue</div>
                    <div class="kpi-value kpi-money">BDT <span data-cu data-cu-value="{{ (float)($todayRevenue ?? 0) }}">{{ number_format((float)($todayRevenue ?? 0), 0) }}</span></div>
                    <div class="kpi-sub">
                        @if((float)($yesterdayRevenue ?? 0) > 0)
                            Yest. BDT {{ number_format((float)$yesterdayRevenue, 0) }}
                        @else
                            Collected today
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-4 col-md-6 col-12 mb-3">
            <div class="kpi kpi-red">
                <div class="kpi-icon"><i class="fas fa-hand-holding-usd"></i></div>
                <div>
                    <div class="kpi-label">Total Due</div>
                    <div class="kpi-value kpi-money">BDT <span data-cu data-cu-value="{{ (float)($totalDue ?? 0) }}">{{ number_format((float)($totalDue ?? 0), 0) }}</span></div>
                    <div class="kpi-sub">Outstanding balance</div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-4 col-md-6 col-12 mb-3">
            <div class="kpi kpi-dark">
                <div class="kpi-icon"><i class="fas fa-calendar-alt"></i></div>
                <div>
                    <div class="kpi-label">This Month</div>
                    <div class="kpi-value kpi-money">BDT <span data-cu data-cu-value="{{ (float)($monthRevenue ?? 0) }}">{{ number_format((float)($monthRevenue ?? 0), 0) }}</span></div>
                    <div class="kpi-sub">Current month revenue</div>
                </div>
            </div>
        </div>

    </div>

    {{-- ======================= FOLLOW-UP STATUS ======================= --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="fu-strip">
                <div class="fu-icon"><i class="fas fa-bell"></i></div>
                <div class="fu-title">
                    <div class="fu-label">Follow-up Status</div>
                    <div class="fu-sub">USG follow-ups — today &amp; overdue</div>
                </div>
                <div class="fu-stats">
                    <div class="fu-stat">
                        <div class="fu-stat-num text-green"><span data-cu data-cu-value="{{ (int)($followupTodayCount ?? 0) }}">{{ $followupTodayCount ?? 0 }}</span></div>
                        <div class="fu-stat-label">Today</div>
                    </div>
                    <div class="fu-stat">
                        <div class="fu-stat-num text-red"><span data-cu data-cu-value="{{ (int)($followupOverdueCount ?? 0) }}">{{ $followupOverdueCount ?? 0 }}</span></div>
                        <div class="fu-stat-label">Overdue</div>
                    </div>
                </div>
                <a href="{{ route('usg-followups.index') }}" class="btn btn-sm btn-outline-primary">
                    View follow-ups <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- ======================= TODAY'S ACTIVITY ======================= --}}
    <div class="dash-section-title">Today's Activity <small>Operational overview</small></div>
    <div class="row">

        <div class="col-xl-2 col-lg-4 col-md-6 col-12 mb-3">
            <div class="widget">
                <div class="widget-top">
                    <span class="widget-label">Appointments</span>
                    <span class="widget-icon wi-danger"><i class="fas fa-calendar-day"></i></span>
                </div>
                <div class="widget-value"><span data-cu data-cu-value="{{ (int)($todayAppointments ?? 0) }}">{{ $todayAppointments ?? 0 }}</span></div>
                <div class="widget-foot">Appointments today</div>
            </div>
        </div>

        <div class="col-xl-2 col-lg-4 col-md-6 col-12 mb-3">
            <div class="widget">
                <div class="widget-top">
                    <span class="widget-label">Pending</span>
                    <span class="widget-icon wi-warning"><i class="fas fa-clock"></i></span>
                </div>
                <div class="widget-value"><span data-cu data-cu-value="{{ (int)($pendingAppointments ?? 0) }}">{{ $pendingAppointments ?? 0 }}</span></div>
                <div class="widget-foot">Pending appointments</div>
            </div>
        </div>

        <div class="col-xl-2 col-lg-4 col-md-6 col-12 mb-3">
            <div class="widget">
                <div class="widget-top">
                    <span class="widget-label">Prescriptions</span>
                    <span class="widget-icon wi-success"><i class="fas fa-prescription"></i></span>
                </div>
                <div class="widget-value"><span data-cu data-cu-value="{{ (int)($todayPrescription ?? 0) }}">{{ $todayPrescription ?? 0 }}</span></div>
                <div class="widget-foot">Prescriptions today</div>
            </div>
        </div>

        <div class="col-xl-2 col-lg-4 col-md-6 col-12 mb-3">
            <div class="widget">
                <div class="widget-top">
                    <span class="widget-label">Vitals</span>
                    <span class="widget-icon wi-info"><i class="fas fa-heartbeat"></i></span>
                </div>
                <div class="widget-value"><span data-cu data-cu-value="{{ (int)($todayVitals ?? 0) }}">{{ $todayVitals ?? 0 }}</span></div>
                <div class="widget-foot">Vitals taken today</div>
            </div>
        </div>

        <div class="col-xl-2 col-lg-4 col-md-6 col-12 mb-3">
            <div class="widget">
                <div class="widget-top">
                    <span class="widget-label">Follow-ups</span>
                    <span class="widget-icon wi-purple"><i class="fas fa-bell"></i></span>
                </div>
                <div class="widget-value"><span data-cu data-cu-value="{{ (int)($followupToday ?? 0) }}">{{ $followupToday ?? 0 }}</span></div>
                <div class="widget-foot">Follow-ups today</div>
            </div>
        </div>

        <div class="col-xl-2 col-lg-4 col-md-6 col-12 mb-3">
            <a href="{{ route('billing.admin.pending') }}" class="text-reset">
                <div class="widget">
                    <div class="widget-top">
                        <span class="widget-label">Approvals</span>
                        <span class="widget-icon wi-warning"><i class="fas fa-hourglass-half"></i></span>
                    </div>
                    <div class="widget-value">
                        <span data-cu data-cu-value="{{ (int)($pendingApprovals ?? 0) }}">{{ $pendingApprovals ?? 0 }}</span>
                        @if(($pendingApprovals ?? 0) > 0)
                            <span class="badge badge-danger ml-1">Action!</span>
                        @endif
                    </div>
                    <div class="widget-foot">Pending approvals</div>
                </div>
            </a>
        </div>

    </div>

    {{-- ======================= ANALYTICS ======================= --}}
    <div class="dash-section-title">Analytics <small>Patients, revenue &amp; payments</small></div>
    <div class="row">

        <div class="col-md-4 mb-3">
            <div class="panel">
                <div class="panel-head">
                    <span class="panel-title"><i class="fas fa-chart-line"></i> Patients — Last 7 Days</span>
                </div>
                <div class="panel-body"><canvas id="patientChart" height="150"></canvas></div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="panel">
                <div class="panel-head">
                    <span class="panel-title"><i class="fas fa-coins"></i> Revenue — Last 7 Days</span>
                </div>
                <div class="panel-body"><canvas id="revenueChart" height="150"></canvas></div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="panel">
                <div class="panel-head">
                    <span class="panel-title"><i class="fas fa-credit-card"></i> Payment Methods — This Month</span>
                </div>
                <div class="panel-body"><canvas id="methodChart" height="150"></canvas></div>
            </div>
        </div>

    </div>

    <div class="row">

        <div class="col-md-7 mb-3">
            <div class="panel">
                <div class="panel-head">
                    <span class="panel-title"><i class="fas fa-chart-bar"></i> Monthly Revenue — {{ now()->year }}</span>
                </div>
                <div class="panel-body"><canvas id="monthChart" height="110"></canvas></div>
            </div>
        </div>

        <div class="col-md-5 mb-3">
            <div class="panel">
                <div class="panel-head">
                    <span class="panel-title"><i class="fas fa-file-invoice"></i> Invoice Status — This Month</span>
                </div>
                <div class="panel-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                        <tr>
                            <td class="border-0 py-2">Full Paid</td>
                            <td class="border-0 text-right">
                                <span class="badge badge-success px-3 py-1">{{ $monthCounts['paid'] ?? 0 }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2">Partial Paid</td>
                            <td class="text-right">
                                <span class="badge badge-warning px-3 py-1">{{ $monthCounts['partial'] ?? 0 }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2">Unpaid / Due</td>
                            <td class="text-right">
                                <span class="badge badge-danger px-3 py-1">{{ $monthCounts['due'] ?? 0 }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 border-top">Month Revenue</td>
                            <td class="text-right border-top font-weight-bold text-success">
                                BDT {{ number_format((float)($monthRevenue ?? 0), 0) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2">Total Revenue (All time)</td>
                            <td class="text-right font-weight-bold text-primary">
                                BDT {{ number_format((float)($totalRevenue ?? 0), 0) }}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- ======================= RECENT ACTIVITY ======================= --}}
    <div class="dash-section-title">Recent Activity <small>Latest invoices &amp; admissions</small></div>
    <div class="row">

        <div class="col-md-7 mb-3">
            <div class="panel">
                <div class="panel-head">
                    <span class="panel-title"><i class="fas fa-receipt"></i> Recent Invoices</span>
                    <a href="{{ route('billing.invoice.index') }}" class="btn btn-tool"><i class="fas fa-external-link-alt"></i></a>
                </div>
                <div class="panel-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                            <tr>
                                <th>Bill</th>
                                <th>Patient</th>
                                <th class="text-right">Paid</th>
                                <th class="text-right">Due</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse(($recentPayments ?? []) as $p)
                                <tr>
                                    <td><b>{{ $p->BillNo }}</b></td>
                                    <td>
                                        {{ $p->PatientName }}
                                        <div class="row-note">{{ $p->PatientCode }}</div>
                                    </td>
                                    <td class="text-right text-green font-weight-bold">{{ number_format($p->PaidAmount, 0) }}</td>
                                    <td class="text-right text-red">{{ number_format($p->DueAmount, 0) }}</td>
                                    <td>
                                        @if(($p->approval_status ?? 'approved') === 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @elseif($p->DueAmount <= 0)
                                            <span class="badge badge-success">Paid</span>
                                        @elseif($p->PaidAmount > 0)
                                            <span class="badge badge-info">Partial</span>
                                        @else
                                            <span class="badge badge-danger">Due</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No invoices yet</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5 mb-3">
            <div class="panel">
                <div class="panel-head">
                    <span class="panel-title"><i class="fas fa-exclamation-triangle"></i> Top Due Patients</span>
                </div>
                <div class="panel-body p-0">
                    @forelse(($topDue ?? []) as $i => $d)
                        <div class="d-flex align-items-center justify-content-between px-3 py-2 {{ $i > 0 ? 'border-top' : '' }}">
                            <div>
                                <b>{{ $d->PatientName }}</b>
                                <div class="row-note">
                                    {{ $d->BillNo }} @if($d->MobileNo) · {{ $d->MobileNo }} @endif
                                </div>
                            </div>
                            <span class="amount-due">BDT {{ number_format($d->DueAmount, 0) }}</span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4"><i class="fas fa-check-circle text-green mr-1"></i>No dues outstanding</div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <div class="row">

        <div class="col-md-5 mb-3">
            <div class="panel">
                <div class="panel-head">
                    <span class="panel-title"><i class="fas fa-user-injured"></i> Today's Patients (Latest)</span>
                </div>
                <div class="panel-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Code</th>
                                <th>Mobile</th>
                                <th class="text-right">Time</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse(($todayPatientRows ?? []) as $p)
                                <tr>
                                    <td><b>{{ $p->patientname }}</b></td>
                                    <td>{{ $p->patientcode }}</td>
                                    <td>{{ $p->mobile_no }}</td>
                                    <td class="text-right text-muted">{{ \Carbon\Carbon::parse($p->created_at)->format('h:i A') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No patients visited today</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="panel">
                <div class="panel-head">
                    <span class="panel-title"><i class="fas fa-user-md"></i> Doctors</span>
                </div>
                <div class="panel-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                            <tr>
                                <th>Doctor</th>
                                <th>Speciality</th>
                                <th class="text-right">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse(($doctors ?? []) as $d)
                                <tr>
                                    <td>
                                        <b>{{ $d->doctor_name }}</b>
                                        @if(!empty($d->contact))
                                            <div class="row-note">{{ $d->contact }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $d->speciality_name ?? '-' }}</td>
                                    <td class="text-right">
                                        @if(($d->active ?? 1) == 1)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">No doctors found</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="panel">
                <div class="panel-head">
                    <span class="panel-title"><i class="fas fa-procedures"></i> Recent Admissions</span>
                </div>
                <div class="panel-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                            <tr>
                                <th>Patient</th>
                                <th class="text-right">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse(($admissions ?? []) as $a)
                                <tr>
                                    <td>
                                        <b>{{ $a->patientname ?? '-' }}</b>
                                        <div class="row-note">
                                            {{ $a->patientcode ?? '' }}
                                            @if(!empty($a->bed_no)) · Bed {{ $a->bed_no }} @endif
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        @if(($a->status ?? 0) == 2 || ($a->status ?? '') == 'admitted')
                                            <span class="badge badge-success">Admitted</span>
                                        @else
                                            <span class="badge badge-secondary">{{ ucfirst($a->status ?? 'N/A') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted py-3">No admissions yet</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ======================= USG FOLLOW-UP REMINDERS ======================= --}}
    @if(isset($followupReminders) && $followupReminders->isNotEmpty())
        <div class="dash-section-title">Follow-up Reminders <small>USG follow-ups due or overdue</small></div>
        <div class="row mb-3">
            <div class="col-12">
                <div class="panel">
                    <div class="panel-head">
                        <span class="panel-title"><i class="fas fa-bell"></i> USG Follow-up Reminders (Due / Overdue)</span>
                        <a href="{{ route('usg-followups.index') }}" class="btn btn-tool btn-sm float-right">View All</a>
                    </div>
                    <div class="panel-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                <tr>
                                    <th>Follow-up Date</th><th>Patient</th><th>Code</th>
                                    <th>Phone</th><th>Doctor</th><th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($followupReminders as $rem)
                                    <tr class="{{ $rem->followup_date < \Carbon\Carbon::today() ? 'table-danger' : '' }}">
                                        <td>{{ \Carbon\Carbon::parse($rem->followup_date)->format('d M Y') }}</td>
                                        <td>{{ $rem->patientname ?? '-' }}</td>
                                        <td>{{ $rem->patientcode ?? '-' }}</td>
                                        <td>@if($rem->mobile_no)<a href="tel:{{ $rem->mobile_no }}">{{ $rem->mobile_no }}</a>@else - @endif</td>
                                        <td>{{ $rem->doctor_name ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('usg-followups.index') }}" class="btn btn-xs btn-info"><i class="fas fa-phone"></i> Track</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
@stop

@section('js')
<script>
(function () {
    'use strict';
    var DURATION = 950;
    var STAGGER = 45;
    var STAGGER_MAX = 180;
    var reduceMotion = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;
    var isReduced = function () { return !!(reduceMotion && reduceMotion.matches); };
    var fmtCache = {};

    function fmt(dec) {
        if (!fmtCache[dec]) {
            fmtCache[dec] = new Intl.NumberFormat('en-US', { minimumFractionDigits: dec, maximumFractionDigits: dec });
        }
        return fmtCache[dec];
    }
    function decimals(el) { return Math.max(0, parseInt(el.getAttribute('data-cu-dec') || '0', 10) || 0); }
    function render(el, value) { el.textContent = fmt(decimals(el)).format(value); }
    function targetOf(el) { var v = parseFloat(el.getAttribute('data-cu-value')); return isFinite(v) ? v : 0; }
    function currentOf(el) { var v = parseFloat(String(el.textContent || '').replace(/[^0-9.\-]/g, '')); return isFinite(v) ? v : 0; }

    function animate(el, from, to) {
        if (el._cuRaf) { cancelAnimationFrame(el._cuRaf); el._cuRaf = null; }
        if (isReduced() || from === to) { render(el, to); return; }
        var t0 = performance.now();
        var step = function (now) {
            var p = Math.min((now - t0) / DURATION, 1);
            render(el, from + (to - from) * (1 - Math.pow(1 - p, 3)));
            if (p < 1) { el._cuRaf = requestAnimationFrame(step); } else { el._cuRaf = null; render(el, to); }
        };
        el._cuRaf = requestAnimationFrame(step);
    }

    function each(target, fn) {
        var els = typeof target === 'string' ? document.querySelectorAll(target)
            : (target && typeof target.length === 'number' ? target : [target]);
        Array.prototype.forEach.call(els, fn);
    }

    each('[data-cu]', function (el, i) {
        if (isReduced()) { render(el, targetOf(el)); return; }
        render(el, 0);
        setTimeout(function () { animate(el, 0, targetOf(el)); }, Math.min(i * STAGGER, STAGGER_MAX));
    });

    window.ClinicCountUp = {
        update: function (target, value) {
            var v = parseFloat(value);
            if (!isFinite(v)) { return; }
            each(target, function (el) {
                el.setAttribute('data-cu-value', String(v));
                animate(el, currentOf(el), v);
            });
        },
        refresh: function (root) {
            each(root ? root.querySelectorAll('[data-cu]') : '[data-cu]', function (el) {
                animate(el, currentOf(el), targetOf(el));
            });
        }
    };
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(function () {
        const palette = ['#007bff', '#28a745', '#ffc107', '#17a2b8', '#dc3545', '#6f42c1', '#fd7e14', '#20c997'];

        // ---- Patients last 7 days ----
        const patientEl = document.getElementById('patientChart');
        if (patientEl) {
            new Chart(patientEl, {
                type: 'line',
                data: {
                    labels: @json($chartDates),
                    datasets: [{
                        label: 'Patients',
                        data: @json($chartPatients),
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0,123,255,.12)',
                        borderWidth: 3,
                        tension: 0.35,
                        fill: true,
                        pointRadius: 4
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });
        }

        // ---- Revenue last 7 days ----
        const revenueEl = document.getElementById('revenueChart');
        if (revenueEl) {
            new Chart(revenueEl, {
                type: 'bar',
                data: {
                    labels: @json($chartDates),
                    datasets: [{
                        label: 'Revenue (BDT)',
                        data: @json($chartRevenue),
                        backgroundColor: 'rgba(40,167,69,.65)',
                        borderColor: '#28a745',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });
        }

        // ---- Payment methods doughnut ----
        const methodEl = document.getElementById('methodChart');
        @if(($methodStats ?? collect())->count() > 0)
        if (methodEl) {
            new Chart(methodEl, {
                type: 'doughnut',
                data: {
                    labels: @json($methodStats->pluck('PaymentMethod')->map(fn($m) => ucfirst(str_replace('_', ' ', $m)))),
                    datasets: [{
                        data: @json($methodStats->pluck('total')->map(fn($t) => (float) $t)),
                        backgroundColor: palette,
                        borderWidth: 2
                    }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
            });
        }
        @else
        if (methodEl) {
            methodEl.parentElement.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-chart-pie fa-2x mb-2 d-block"></i>No payments this month yet</div>';
        }
        @endif

        // ---- Monthly revenue ----
        const monthEl = document.getElementById('monthChart');
        if (monthEl) {
            new Chart(monthEl, {
                type: 'bar',
                data: {
                    labels: @json($chartMonths),
                    datasets: [{
                        label: 'Revenue (BDT)',
                        data: @json($chartMonthly),
                        backgroundColor: 'rgba(23,162,184,.6)',
                        borderColor: '#17a2b8',
                        borderWidth: 1,
                        borderRadius: 3
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }
    });
</script>
@stop
