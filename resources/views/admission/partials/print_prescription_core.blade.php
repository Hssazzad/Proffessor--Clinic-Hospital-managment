{{--
    Loads the shared prescription CSS (as window.RX_PRINT_CSS) and the JS
    builder that renders the canonical sheet into a print popup.

    Include this in any view with a "Print Prescription" button, then call
    admissionPrintPrescriptionHtml(config).
--}}
@include('partials.rx_styles_js')
<script>
@include('admission.partials._print_prescription_js')
</script>
