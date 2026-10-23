{{--
    Inlines the canonical prescription CSS as a <style> block.
    Source of truth: public/css/rx-print.css — edit that file, never this one.

    Usage (inside <head> of any prescription print view):
        @include('partials.rx_styles')
--}}
<style>
{!! file_get_contents(public_path('css/rx-print.css')) !!}
</style>
