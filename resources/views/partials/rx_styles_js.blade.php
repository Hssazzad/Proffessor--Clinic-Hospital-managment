{{--
    Exposes the canonical prescription CSS to JavaScript print builders as
    window.RX_PRINT_CSS, so a popup window written from JS uses the exact
    same stylesheet as the server-rendered prescriptions.

    Source of truth: public/css/rx-print.css — edit that file, never this one.

    Usage:
        @include('partials.rx_styles_js')
        ...
        '<style>' + window.RX_PRINT_CSS + '</style>'
--}}
<script>window.RX_PRINT_CSS = @json(file_get_contents(public_path('css/rx-print.css')));</script>
